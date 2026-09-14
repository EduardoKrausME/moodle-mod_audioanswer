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
 * lib.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_audioanswer\instance_manager;

/**
 * Declares the Moodle features supported by this activity.
 *
 * @param string $feature Feature constant.
 * @return bool|string|null
 */
function audioanswer_supports($feature) {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_COMPLETION_TRACKS_VIEWS => true,
        FEATURE_COMPLETION_HAS_RULES => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_ASSESSMENT,
        default => null,
    };
}

/**
 * Adds an activity instance.
 *
 * @param stdClass $data Submitted form data.
 * @param mod_audioanswer_mod_form|null $mform Form instance.
 * @return int
 */
function audioanswer_add_instance($data, $mform = null) {
    return instance_manager::add($data);
}

/**
 * Updates an activity instance.
 *
 * @param stdClass $data Submitted form data.
 * @param mod_audioanswer_mod_form|null $mform Form instance.
 * @return bool
 */
function audioanswer_update_instance($data, $mform = null) {
    return instance_manager::update($data);
}

/**
 * Deletes an activity instance.
 *
 * @param int $id Activity instance id.
 * @return bool
 */
function audioanswer_delete_instance($id) {
    return instance_manager::delete($id);
}

/**
 * Serves response audio files.
 *
 * @param stdClass $course Course record.
 * @param stdClass $cm Course module record.
 * @param context $context Module context.
 * @param string $filearea File area.
 * @param array $args Remaining path arguments.
 * @param bool $forcedownload Whether download is forced.
 * @param array $options File serving options.
 * @return bool
 */
function audioanswer_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB, $USER;

    if ($context->contextlevel !== CONTEXT_MODULE || $filearea !== "answer") {
        return false;
    }

    require_login($course, true, $cm);

    $responseid = (int)array_shift($args);
    if (!$responseid) {
        return false;
    }

    $response = $DB->get_record("audioanswer_response", ["id" => $responseid, "audioanswerid" => $cm->instance]);
    if (!$response) {
        return false;
    }

    if ((int)$response->userid !== (int)$USER->id && !has_capability("mod/audioanswer:viewresponses", $context)) {
        return false;
    }

    $filename = array_pop($args);
    $filepath = "/" . implode("/", $args) . "/";
    if ($filepath === "//") {
        $filepath = "/";
    }

    $fs = get_file_storage();
    $file = $fs->get_file(
        $context->id,
        "mod_audioanswer",
        "answer",
        $responseid,
        $filepath,
        $filename
    );

    if (!$file || $file->is_directory()) {
        return false;
    }

    if (!$forcedownload) {
        header("X-Content-Type-Options: nosniff");
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}
