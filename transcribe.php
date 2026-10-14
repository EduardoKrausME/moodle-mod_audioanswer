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
 * Allow authorized teachers to request transcription of existing recordings.
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new moodle_exception('invalidrequest');
}

$id = required_param('id', PARAM_INT);
$responseid = required_param('responseid', PARAM_INT);
require_sesskey();

$cm = get_coursemodule_from_id('audioanswer', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$activity = $DB->get_record('audioanswer', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);
require_login($course, true, $cm);
require_capability('mod/audioanswer:viewresponses', $context);

if (empty($activity->transcriptionenabled) || !\mod_audioanswer\transcription_manager::available()) {
    throw new moodle_exception('transcriptiondisabled', 'mod_audioanswer');
}
$response = $DB->get_record('audioanswer_response', [
    'id' => $responseid,
    'audioanswerid' => $activity->id,
], '*', MUST_EXIST);
if (!\mod_audioanswer\group_access::can_view_user($cm, $course, $context, (int)$response->userid)) {
    throw new required_capability_exception($context, 'mod/audioanswer:viewresponses', 'nopermissions', '');
}
if (!in_array($response->transcriptionstatus, ['none', 'failed'], true)) {
    redirect(new moodle_url('/mod/audioanswer/report.php', ['id' => $cm->id]));
}
$file = \mod_audioanswer\transcription_manager::get_file($context, $response);
if (!$file) {
    throw new moodle_exception('missingaudio', 'mod_audioanswer');
}
\mod_audioanswer\transcription_manager::queue($activity, $response, $file);
redirect(new moodle_url('/mod/audioanswer/report.php', ['id' => $cm->id]),
    get_string('transcriptionqueued', 'mod_audioanswer'));
