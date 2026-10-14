<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Asynchronously transcribe one audio answer.
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_audioanswer\task;

use core\lock\lock_config;
use core\task\adhoc_task;
use mod_audioanswer\transcription_manager;
use Throwable;

/**
 * The worker never stores stale transcriptions from superseded recordings.
 */
class transcribe extends adhoc_task {
    /**
     * Call AI Bridge for a stored audio file.
     *
     * @return void
     */
    public function execute(): void {
        global $DB;

        $data = $this->get_custom_data();
        $responseid = (int)($data->responseid ?? 0);
        $filename = (string)($data->filename ?? '');
        $contenthash = (string)($data->contenthash ?? '');
        if ($responseid <= 0 || $filename === '' || $contenthash === '') {
            return;
        }

        $factory = lock_config::get_lock_factory('mod_audioanswer');
        $lock = $factory->get_lock('transcribe_' . $responseid, 1);
        if (!$lock) {
            // A duplicate worker must not repeat a potentially billable request.
            return;
        }
        try {
            $response = $DB->get_record('audioanswer_response', ['id' => $responseid]);
            if (!$response || $response->filename !== $filename || $response->transcriptionstatus !== 'pending') {
                return;
            }
            $activity = $DB->get_record('audioanswer', ['id' => $response->audioanswerid]);
            if (!$activity || empty($activity->transcriptionenabled) || !transcription_manager::available()) {
                return;
            }
            $cm = get_coursemodule_from_instance('audioanswer', $activity->id, $activity->course,
                false, IGNORE_MISSING);
            if (!$cm) {
                return;
            }
            $context = \context_module::instance($cm->id);
            $file = transcription_manager::get_file($context, $response);
            if (!$file || $file->get_contenthash() !== $contenthash) {
                return;
            }

            try {
                // Tenant, logical role, route, capabilities and credits belong to AI Bridge.
                $result = \local_ai_bridge\api::transcribe(
                    transcription_manager::PURPOSE, $file, (int)$response->userid
                );
                $status = 'ready';
                $text = $result->text;
            } catch (Throwable $exception) {
                // No retry here: a provider request might already have been billed.
                debugging('Audioanswer transcription failed (' . get_class($exception) . ').', DEBUG_DEVELOPER);
                $status = 'failed';
                $text = null;
            }

            // The student may have rerecorded while the external request was running.
            $latest = $DB->get_record('audioanswer_response', ['id' => $responseid]);
            if (!$latest || $latest->filename !== $filename || $latest->transcriptionstatus !== 'pending') {
                return;
            }
            $currentfile = transcription_manager::get_file($context, $latest);
            if (!$currentfile || $currentfile->get_contenthash() !== $contenthash) {
                return;
            }
            $DB->update_record('audioanswer_response', (object)[
                'id' => $responseid,
                'transcriptionstatus' => $status,
                'transcription' => $text,
            ]);
        } finally {
            $lock->release();
        }
    }
}
