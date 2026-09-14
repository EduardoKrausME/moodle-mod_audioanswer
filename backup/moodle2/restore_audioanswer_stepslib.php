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
 * restore_audioanswer_stepslib.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * restore_audioanswer_activity_structure_step
 */
class restore_audioanswer_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines restore paths.
     *
     * @return array
     */
    protected function define_structure() {
        $paths = [new restore_path_element("audioanswer", "/activity/audioanswer")];
        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element("audioanswer_response", "/activity/audioanswer/responses/response");
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores the activity record.
     *
     * @param array $data Record data.
     * @return void
     */
    protected function process_audioanswer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record("audioanswer", $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restores a user response.
     *
     * @param array $data Record data.
     * @return void
     */
    protected function process_audioanswer_response($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->audioanswerid = $this->get_new_parentid("audioanswer");
        $data->userid = $this->get_mappingid("user", $data->userid);

        if (!$data->userid) {
            return;
        }

        $newitemid = $DB->insert_record("audioanswer_response", $data);
        $this->set_mapping("audioanswer_response", $oldid, $newitemid, true);
    }

    /**
     * Restores related files after records are mapped.
     *
     * @return void
     */
    protected function after_execute() {
        $this->add_related_files("mod_audioanswer", "intro", null);
        $this->add_related_files("mod_audioanswer", "answer", "audioanswer_response");
    }
}
