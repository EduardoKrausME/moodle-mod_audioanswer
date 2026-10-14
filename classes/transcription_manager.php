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
 * Queue and retrieve optional audio transcription.
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_audioanswer;

use core\task\manager;
use mod_audioanswer\task\transcribe;

/**
 * Only coordinates transcription; the external call belongs to local_ai_bridge.
 */
class transcription_manager {
    /** @var string Purpose identifier created by the tenant administrator. */
    public const PURPOSE = 'audioanswer-transcription';

    /**
     * Check the optional integration is installed at a compatible version.
     *
     * @return bool
     */
    public static function available(): bool {
        return class_exists(\local_ai_bridge\api::class) &&
            method_exists(\local_ai_bridge\api::class, 'transcribe');
    }

    /**
     * Queue the current response and record its audio fingerprint.
     *
     * @param \stdClass $activity Activity settings.
     * @param \stdClass $response Response record.
     * @param \stored_file $file Saved private audio.
     * @return void
     */
    public static function queue(\stdClass $activity, \stdClass $response, \stored_file $file): void {
        global $DB;

        if (empty($activity->transcriptionenabled) || !self::available()) {
            return;
        }
        $task = new transcribe();
        $task->set_userid((int)$response->userid);
        $task->set_custom_data([
            'responseid' => (int)$response->id,
            'filename' => $file->get_filename(),
            'contenthash' => $file->get_contenthash(),
        ]);
        manager::queue_adhoc_task($task);
        $DB->set_field('audioanswer_response', 'transcriptionstatus', 'pending', ['id' => $response->id]);
    }

    /**
     * Find the current recording, which must match the response filename.
     *
     * @param \context_module $context Module context.
     * @param \stdClass $response Response record.
     * @return \stored_file|false
     */
    public static function get_file(\context_module $context, \stdClass $response): \stored_file|false {
        return get_file_storage()->get_file(
            $context->id, 'mod_audioanswer', 'answer', (int)$response->id, '/', $response->filename
        );
    }
}
