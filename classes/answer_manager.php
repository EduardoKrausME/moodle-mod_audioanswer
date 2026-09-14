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
 * answer_manager.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_audioanswer;

/**
 * Class answer_manager.
 */
class answer_manager {
    /**
     * Returns one student's response.
     *
     * @param int $audioanswerid Activity id.
     * @param int $userid User id.
     * @return \stdClass|null
     */
    public static function get_user_response(int $audioanswerid, int $userid): ?\stdClass {
        global $DB;

        $response = $DB->get_record("audioanswer_response", [
            "audioanswerid" => $audioanswerid,
            "userid" => $userid,
        ]);

        return $response ?: null;
    }

    /**
     * Saves a multipart audio upload as the user's response.
     *
     * @param \stdClass $audioanswer Activity record.
     * @param \context_module $context Module context.
     * @param int $userid User id.
     * @param array $upload PHP upload entry.
     * @param int $duration Declared recording duration in seconds.
     * @return \stdClass
     */
    public static function save_uploaded_audio(
        \stdClass $audioanswer,
        \context_module $context,
        int $userid,
        array $upload,
        int $duration
    ): \stdClass {
        global $DB;

        if (!in_array((int)$audioanswer->maxduration, [30, 60, 120], true)) {
            throw new \moodle_exception("invalidduration", "mod_audioanswer");
        }

        if ($duration < 1 || $duration > ((int)$audioanswer->maxduration + 2)) {
            throw new \moodle_exception("recordingtoolong", "mod_audioanswer");
        }

        if (!isset($upload["error"], $upload["tmp_name"], $upload["size"]) || (int)$upload["error"] !== UPLOAD_ERR_OK) {
            throw new \moodle_exception("uploaderror", "mod_audioanswer");
        }

        if ((int)$upload["size"] < 1 || (int)$upload["size"] > 20971520) {
            throw new \moodle_exception("invalidfilesize", "mod_audioanswer");
        }

        if (!is_uploaded_file($upload["tmp_name"])) {
            throw new \moodle_exception("uploaderror", "mod_audioanswer");
        }

        [$mimetype, $extension] = self::detect_audio_type($upload);
        $now = time();
        $response = self::get_user_response((int)$audioanswer->id, $userid);
        $isnew = !$response;

        if ($isnew) {
            $response = (object)[
                "audioanswerid" => (int)$audioanswer->id,
                "userid" => $userid,
                "duration" => $duration,
                "mimetype" => $mimetype,
                "filename" => "",
                "timecreated" => $now,
                "timemodified" => $now,
            ];
            $response->id = $DB->insert_record("audioanswer_response", $response);
        }

        $filename = "answer-" . $response->id . "-" . $now . "." . $extension;
        $fileinfo = [
            "contextid" => $context->id,
            "component" => "mod_audioanswer",
            "filearea" => "answer",
            "itemid" => $response->id,
            "filepath" => "/",
            "filename" => $filename,
            "userid" => $userid,
            "mimetype" => $mimetype,
        ];

        $fs = get_file_storage();
        try {
            $newfile = $fs->create_file_from_pathname($fileinfo, $upload["tmp_name"]);
            if (!$newfile) {
                throw new \moodle_exception("uploaderror", "mod_audioanswer");
            }

            $files = $fs->get_area_files(
                $context->id,
                "mod_audioanswer",
                "answer",
                $response->id,
                "id ASC",
                false
            );
            foreach ($files as $file) {
                if ($file->get_id() !== $newfile->get_id()) {
                    $file->delete();
                }
            }

            $response->duration = $duration;
            $response->mimetype = $mimetype;
            $response->filename = $filename;
            $response->timemodified = $now;
            $DB->update_record("audioanswer_response", $response);
        } catch (\Throwable $exception) {
            if ($isnew && !empty($response->id)) {
                $DB->delete_records("audioanswer_response", ["id" => $response->id]);
            }
            throw $exception;
        }

        \mod_audioanswer\event\answer_submitted::create([
            "objectid" => $response->id,
            "context" => $context,
            "relateduserid" => $userid,
            "other" => ["replaced" => $isnew ? 0 : 1],
        ])->trigger();

        return $response;
    }

    /**
     * Returns the pluginfile URL for a response.
     *
     * @param \context_module $context Module context.
     * @param \stdClass $response Response record.
     * @return string
     */
    public static function get_audio_url(\context_module $context, \stdClass $response): string {
        $fs = get_file_storage();
        $file = $fs->get_file(
            $context->id,
            "mod_audioanswer",
            "answer",
            $response->id,
            "/",
            $response->filename
        );

        if (!$file) {
            return "";
        }

        return \moodle_url::make_pluginfile_url(
            $context->id,
            "mod_audioanswer",
            "answer",
            $response->id,
            "/",
            $response->filename,
            false
        )->out(false);
    }

    /**
     * Deletes one response and its files.
     *
     * @param \context_module $context Module context.
     * @param \stdClass $response Response record.
     * @return void
     */
    public static function delete_response(\context_module $context, \stdClass $response): void {
        global $DB;

        get_file_storage()->delete_area_files(
            $context->id,
            "mod_audioanswer",
            "answer",
            $response->id
        );
        $DB->delete_records("audioanswer_response", ["id" => $response->id]);
    }

    /**
     * Detects and normalises an audio container type.
     *
     * @param array $upload PHP upload entry.
     * @return array Array containing MIME type and extension.
     */
    private static function detect_audio_type(array $upload): array {
        $clienttype = strtolower(trim((string)($upload["type"] ?? "")));
        $detectedtype = "";

        if (class_exists("finfo")) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedtype = strtolower((string)$finfo->file($upload["tmp_name"]));
        }

        $allowed = [
            "audio/webm" => ["audio/webm", "webm"],
            "video/webm" => ["audio/webm", "webm"],
            "audio/ogg" => ["audio/ogg", "ogg"],
            "application/ogg" => ["audio/ogg", "ogg"],
            "audio/mp4" => ["audio/mp4", "m4a"],
            "video/mp4" => ["audio/mp4", "m4a"],
            "audio/mpeg" => ["audio/mpeg", "mp3"],
            "audio/wav" => ["audio/wav", "wav"],
            "audio/x-wav" => ["audio/wav", "wav"],
        ];

        if (isset($allowed[$detectedtype])) {
            return $allowed[$detectedtype];
        }

        if (($detectedtype === "" || $detectedtype === "application/octet-stream") && isset($allowed[$clienttype])) {
            return $allowed[$clienttype];
        }

        throw new \moodle_exception("invalidaudiotype", "mod_audioanswer");
    }
}
