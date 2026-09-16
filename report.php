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
 * report.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once($CFG->libdir . "/tablelib.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("audioanswer", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$audioanswer = $DB->get_record("audioanswer", ["id" => $cm->instance], "*", MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability("mod/audioanswer:viewresponses", $context);

$url = new moodle_url("/mod/audioanswer/report.php", ["id" => $cm->id]);
$PAGE->set_url($url);
$PAGE->set_title(get_string("responses", "mod_audioanswer"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$groupmode = groups_get_activity_groupmode($cm, $course);
$currentgroup = $groupmode === NOGROUPS ? 0 : groups_get_activity_group($cm, true);
$groupjoin = "";
$groupwhere = "";
$params = ["audioanswerid" => $audioanswer->id];

if ($groupmode === SEPARATEGROUPS && !$currentgroup &&
        !has_capability("moodle/site:accessallgroups", $context)) {
    $groupwhere = " AND 1 = 0";
} else if ($currentgroup > 0) {
    $groupjoin = " JOIN {groups_members} gm
                        ON gm.userid = r.userid
                       AND gm.groupid = :currentgroup";
    $params["currentgroup"] = $currentgroup;
}

$total = $DB->count_records_sql(
    "SELECT COUNT(r.id)
       FROM {audioanswer_response} r
       {$groupjoin}
      WHERE r.audioanswerid = :audioanswerid{$groupwhere}",
    $params
);

$table = new flexible_table("mod-audioanswer-responses-" . $cm->id);
$table->define_columns(["student", "audio", "duration", "submitted"]);
$table->define_headers([
    get_string("student", "mod_audioanswer"),
    get_string("audio", "mod_audioanswer"),
    get_string("duration", "mod_audioanswer"),
    get_string("submitted", "mod_audioanswer"),
]);
$table->define_baseurl($url);
$table->set_attribute("class", "generaltable mod-audioanswer-report");
$table->pagesize(50, $total);
$table->setup();

$sql = "SELECT r.*, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
               u.middlename, u.alternatename
          FROM {audioanswer_response} r
          JOIN {user} u ON u.id = r.userid
          {$groupjoin}
         WHERE r.audioanswerid = :audioanswerid{$groupwhere}
      ORDER BY r.timemodified DESC";

$records = $DB->get_records_sql(
    $sql,
    $params,
    $table->get_page_start(),
    $table->get_page_size()
);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($audioanswer->name));
echo html_writer::div(format_text($audioanswer->question, FORMAT_PLAIN, ["context" => $context]), "mb-4");

if ($groupmode !== NOGROUPS) {
    groups_print_activity_menu($cm, $url);
}

echo $OUTPUT->single_button(
    new moodle_url("/mod/audioanswer/view.php", ["id" => $cm->id]),
    get_string("backtoactivity", "mod_audioanswer"),
    "get"
);

foreach ($records as $record) {
    $profileurl = new moodle_url("/user/view.php", ["id" => $record->userid, "course" => $course->id]);
    $student = html_writer::link($profileurl, fullname($record));
    $audiourl = \mod_audioanswer\answer_manager::get_audio_url($context, $record);
    $player = html_writer::tag("audio", "", [
        "controls" => "controls",
        "preload" => "none",
        "src" => $audiourl,
    ]);
    $duration = sprintf("%02d:%02d", intdiv((int)$record->duration, 60), (int)$record->duration % 60);
    $submitted = userdate((int)$record->timemodified);
    $table->add_data([$student, $player, $duration, $submitted]);
}

$table->finish_output();
echo $OUTPUT->footer();
