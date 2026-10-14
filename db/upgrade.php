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
 * Upgrade file.
 *
 * @package    mod_audioanswer
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps for audioanswer.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_audioanswer_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026101401) {
        $dbman = $DB->get_manager();
        $activitytable = new xmldb_table('audioanswer');
        $enabled = new xmldb_field('transcriptionenabled', XMLDB_TYPE_INTEGER, '1',
            null, XMLDB_NOTNULL, null, '0', 'completiononsubmit');
        if (!$dbman->field_exists($activitytable, $enabled)) {
            $dbman->add_field($activitytable, $enabled);
        }

        $responsetable = new xmldb_table('audioanswer_response');
        $status = new xmldb_field('transcriptionstatus', XMLDB_TYPE_CHAR, '20',
            null, XMLDB_NOTNULL, null, 'none', 'filename');
        if (!$dbman->field_exists($responsetable, $status)) {
            $dbman->add_field($responsetable, $status);
        }
        $transcription = new xmldb_field('transcription', XMLDB_TYPE_TEXT, null,
            null, null, null, null, 'transcriptionstatus');
        if (!$dbman->field_exists($responsetable, $transcription)) {
            $dbman->add_field($responsetable, $transcription);
        }

        upgrade_mod_savepoint(true, 2026101401, 'audioanswer');
    }

    return true;
}
