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
 * save.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define("AJAX_SCRIPT", true);

require_once(__DIR__ . "/../../config.php");

header("Content-Type: application/json; charset=utf-8");

try {
    $cmid = required_param("cmid", PARAM_INT);
    $duration = required_param("duration", PARAM_INT);
    require_sesskey();

    $cm = get_coursemodule_from_id("audioanswer", $cmid, 0, false, MUST_EXIST);
    $course = get_course($cm->course);
    $audioanswer = $DB->get_record("audioanswer", ["id" => $cm->instance], "*", MUST_EXIST);
    $context = context_module::instance($cm->id);

    require_login($course, false, $cm);
    require_capability("mod/audioanswer:submit", $context);

    if (!isset($_FILES["audio"])) {
        throw new moodle_exception("missingaudio", "mod_audioanswer");
    }

    $response = \mod_audioanswer\answer_manager::save_uploaded_audio(
        $audioanswer,
        $context,
        $USER->id,
        $_FILES["audio"],
        $duration
    );

    $completion = new completion_info($course);
    if ($completion->is_enabled($cm)) {
        $completion->update_state($cm, COMPLETION_UNKNOWN, $USER->id);
    }

    echo json_encode([
        "success" => true,
        "responseid" => (int)$response->id,
    ]);
} catch (Throwable $exception) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => get_string("saveerror", "mod_audioanswer"),
    ]);
}
