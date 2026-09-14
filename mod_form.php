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
 * mod_form.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("{$CFG->dirroot}/course/moodleform_mod.php");

/**
 * Class mod_audioanswer_mod_form.
 */
class mod_audioanswer_mod_form extends moodleform_mod {
    /**
     * Defines the activity settings form.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("audioanswername", "mod_audioanswer"), ["size" => 64]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");

        $mform->addElement("textarea", "question", get_string("question", "mod_audioanswer"), ["rows" => 5]);
        $mform->setType("question", PARAM_TEXT);
        $mform->addRule("question", null, "required", null, "client");
        $mform->addHelpButton("question", "question", "mod_audioanswer");

        $durations = [
            30 => get_string("seconds", "mod_audioanswer", 30),
            60 => get_string("seconds", "mod_audioanswer", 60),
            120 => get_string("seconds", "mod_audioanswer", 120),
        ];
        $mform->addElement("select", "maxduration", get_string("maxduration", "mod_audioanswer"), $durations);
        $mform->setDefault("maxduration", 60);
        $mform->addHelpButton("maxduration", "maxduration", "mod_audioanswer");

        $this->standard_intro_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Adds the custom completion rule.
     *
     * @return array
     */
    public function add_completion_rules() {
        $mform = $this->_form;
        $mform->addElement(
            "checkbox",
            "completiononsubmit",
            "",
            get_string("completiononsubmit", "mod_audioanswer")
        );
        $mform->setDefault("completiononsubmit", 1);

        return ["completiononsubmit"];
    }

    /**
     * Checks whether the custom completion rule is enabled.
     *
     * @param array $data Form data.
     * @return bool
     */
    public function completion_rule_enabled($data) {
        return !empty($data["completiononsubmit"]);
    }
    /**
     * Validates submitted activity settings.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (trim((string)($data["question"] ?? "")) === "") {
            $errors["question"] = get_string("required");
        }
        if (!in_array((int)($data["maxduration"] ?? 0), [30, 60, 120], true)) {
            $errors["maxduration"] = get_string("invalidduration", "mod_audioanswer");
        }

        return $errors;
    }

}
