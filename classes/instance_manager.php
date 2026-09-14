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
 * instance_manager.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_audioanswer;

/**
 * Class instance_manager.
 */
class instance_manager {
    /**
     * Creates an activity instance.
     *
     * @param \stdClass $data Activity data.
     * @return int
     */
    public static function add(\stdClass $data): int {
        global $DB;

        self::validate_duration((int)$data->maxduration);

        $now = time();
        $data->timecreated = $now;
        $data->timemodified = $now;
        $data->completiononsubmit = empty($data->completiononsubmit) ? 0 : 1;

        return (int)$DB->insert_record("audioanswer", $data);
    }

    /**
     * Updates an activity instance.
     *
     * @param \stdClass $data Activity data.
     * @return bool
     */
    public static function update(\stdClass $data): bool {
        global $DB;

        self::validate_duration((int)$data->maxduration);

        $data->id = $data->instance;
        $data->timemodified = time();
        $data->completiononsubmit = empty($data->completiononsubmit) ? 0 : 1;

        return $DB->update_record("audioanswer", $data);
    }

    /**
     * Deletes an activity instance and all response files.
     *
     * @param int $id Activity id.
     * @return bool
     */
    public static function delete(int $id): bool {
        global $DB;

        $activity = $DB->get_record("audioanswer", ["id" => $id]);
        if (!$activity) {
            return false;
        }

        $cm = get_coursemodule_from_instance("audioanswer", $id, $activity->course, false, IGNORE_MISSING);
        if ($cm) {
            $context = \context_module::instance($cm->id);
            get_file_storage()->delete_area_files($context->id, "mod_audioanswer", "answer");
        }

        $DB->delete_records("audioanswer_response", ["audioanswerid" => $id]);
        $DB->delete_records("audioanswer", ["id" => $id]);

        return true;
    }
    /**
     * Validates the configured recording limit.
     *
     * @param int $duration Recording limit in seconds.
     * @return void
     */
    private static function validate_duration(int $duration): void {
        if (!in_array($duration, [30, 60, 120], true)) {
            throw new \moodle_exception("invalidduration", "mod_audioanswer");
        }
    }

}
