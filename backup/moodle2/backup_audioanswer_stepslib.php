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
 * backup_audioanswer_stepslib.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class backup_audioanswer_activity_structure_step
 */
class backup_audioanswer_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the activity backup structure.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value("userinfo");

        $audioanswer = new backup_nested_element("audioanswer", ["id"], [
            "name",
            "intro",
            "introformat",
            "question",
            "maxduration",
            "completiononsubmit",
            "timecreated",
            "timemodified",
        ]);
        $responses = new backup_nested_element("responses");
        $response = new backup_nested_element("response", ["id"], [
            "userid",
            "duration",
            "mimetype",
            "filename",
            "timecreated",
            "timemodified",
        ]);

        $audioanswer->add_child($responses);
        $responses->add_child($response);

        $audioanswer->set_source_table("audioanswer", ["id" => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $response->set_source_table("audioanswer_response", ["audioanswerid" => backup::VAR_PARENTID]);
        }

        $response->annotate_ids("user", "userid");
        $audioanswer->annotate_files("mod_audioanswer", "intro", null);
        $response->annotate_files("mod_audioanswer", "answer", "id");

        return $this->prepare_activity_structure($audioanswer);
    }
}
