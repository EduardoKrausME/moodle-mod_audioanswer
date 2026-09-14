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
 * provider.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_audioanswer\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Describes stored personal data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            "audioanswer_response",
            [
                "audioanswerid" => "privacy:metadata:audioanswer_response:audioanswerid",
                "userid" => "privacy:metadata:audioanswer_response:userid",
                "duration" => "privacy:metadata:audioanswer_response:duration",
                "mimetype" => "privacy:metadata:audioanswer_response:mimetype",
                "filename" => "privacy:metadata:audioanswer_response:filename",
                "timecreated" => "privacy:metadata:audioanswer_response:timecreated",
                "timemodified" => "privacy:metadata:audioanswer_response:timemodified",
            ],
            "privacy:metadata:audioanswer_response"
        );
        $collection->add_subsystem_link("core_files", [], "privacy:metadata:core_files");

        return $collection;
    }

    /**
     * Gets contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {audioanswer_response} r
                  JOIN {audioanswer} a ON a.id = r.audioanswerid
                  JOIN {modules} m ON m.name = :modname
                  JOIN {course_modules} cm ON cm.instance = a.id AND cm.module = m.id
                  JOIN {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
                 WHERE r.userid = :userid";
        $contextlist->add_from_sql($sql, [
            "modname" => "audioanswer",
            "contextlevel" => CONTEXT_MODULE,
            "userid" => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Exports user data from approved contexts.
     *
     * @param approved_contextlist $contextlist Approved context list.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id("audioanswer", $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }

            $response = $DB->get_record("audioanswer_response", [
                "audioanswerid" => $cm->instance,
                "userid" => $userid,
            ]);
            if (!$response) {
                continue;
            }

            $data = (object)[
                "duration" => $response->duration,
                "filename" => $response->filename,
                "timecreated" => transform::datetime($response->timecreated),
                "timemodified" => transform::datetime($response->timemodified),
            ];

            writer::with_context($context)->export_data([], $data);
            writer::with_context($context)->export_area_files(
                [],
                "mod_audioanswer",
                "answer",
                $response->id
            );
        }
    }

    /**
     * Deletes all user data in a module context.
     *
     * @param \context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id("audioanswer", $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return;
        }

        get_file_storage()->delete_area_files($context->id, "mod_audioanswer", "answer");
        $DB->delete_records("audioanswer_response", ["audioanswerid" => $cm->instance]);
    }

    /**
     * Deletes data for one user in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved context list.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id("audioanswer", $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }

            $responses = $DB->get_records("audioanswer_response", [
                "audioanswerid" => $cm->instance,
                "userid" => $userid,
            ]);
            foreach ($responses as $response) {
                \mod_audioanswer\answer_manager::delete_response($context, $response);
            }
        }
    }
}
