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
 * custom_completion.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_audioanswer\completion;

/**
 * Class custom_completion.
 */
class custom_completion extends \core_completion\activity_custom_completion {
    /**
     * Returns the state of a custom completion rule.
     *
     * @param string $rule Rule name.
     * @return int
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        return $DB->record_exists("audioanswer_response", [
            "audioanswerid" => $this->cm->instance,
            "userid" => $this->userid,
        ]) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Lists custom completion rules.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return ["completiononsubmit"];
    }

    /**
     * Describes custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        return [
            "completiononsubmit" => get_string("completiononsubmit", "mod_audioanswer"),
        ];
    }

    /**
     * Provides the completion rule sort order.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return ["completiononsubmit"];
    }
}
