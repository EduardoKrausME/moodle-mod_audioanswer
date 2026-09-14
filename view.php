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
 * view.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("audioanswer", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$audioanswer = $DB->get_record("audioanswer", ["id" => $cm->instance], "*", MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);

$PAGE->set_url("/mod/audioanswer/view.php", ["id" => $cm->id]);
$PAGE->set_title(format_string($audioanswer->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$event = \mod_audioanswer\event\course_module_viewed::create([
    "objectid" => $audioanswer->id,
    "context" => $context,
]);
$event->add_record_snapshot("course", $course);
$event->add_record_snapshot("course_modules", $cm);
$event->add_record_snapshot("audioanswer", $audioanswer);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$cananswer = has_capability("mod/audioanswer:submit", $context);
$canviewresponses = has_capability("mod/audioanswer:viewresponses", $context);
$response = null;
$audiourl = "";

if ($cananswer) {
    $response = \mod_audioanswer\answer_manager::get_user_response($audioanswer->id, $USER->id);
    if ($response) {
        $audiourl = \mod_audioanswer\answer_manager::get_audio_url($context, $response);
    }
}

$PAGE->requires->strings_for_js([
    "record",
    "stoprecording",
    "recording",
    "readytosave",
    "saving",
    "saved",
    "microphoneerror",
    "recordingunsupported",
    "recordingtoolong",
    "saveerror",
    "permissiondenied",
], "mod_audioanswer");

if ($cananswer) {
    $PAGE->requires->js_call_amd("mod_audioanswer/recorder", "init", [[
        "cmid" => $cm->id,
        "maxduration" => (int)$audioanswer->maxduration,
        "saveurl" => (new moodle_url("/mod/audioanswer/save.php"))->out(false),
        "sesskey" => sesskey(),
    ]]);
}

$data = [
    "name" => format_string($audioanswer->name),
    "questionhtml" => format_text($audioanswer->question, FORMAT_PLAIN, ["context" => $context]),
    "introhtml" => $audioanswer->intro ? format_module_intro("audioanswer", $audioanswer, $cm->id, false) : "",
    "cananswer" => $cananswer,
    "canviewresponses" => $canviewresponses,
    "reporturl" => (new moodle_url("/mod/audioanswer/report.php", ["id" => $cm->id]))->out(false),
    "maxduration" => (int)$audioanswer->maxduration,
    "timelimittext" => get_string("timelimit", "mod_audioanswer", (int)$audioanswer->maxduration),
    "maxtimeformatted" => sprintf("%02d:%02d", intdiv((int)$audioanswer->maxduration, 60), (int)$audioanswer->maxduration % 60),
    "hasresponse" => !empty($response),
    "audiourl" => $audiourl,
    "submittedat" => $response ? userdate($response->timemodified) : "",
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("mod_audioanswer/recorder", $data);
echo $OUTPUT->footer();
