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
 * Group access helpers.
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_audioanswer;

/**
 * Handles access to responses when activity groups are enabled.
 */
class group_access {
    /**
     * Checks whether the current user may view another user's response.
     *
     * In separate groups mode, users without the access-all-groups capability
     * must share at least one group allowed by the activity/grouping with the
     * response owner. No restriction is added for no groups or visible groups.
     *
     * @param \stdClass $cm Course module record.
     * @param \stdClass $course Course record.
     * @param \context_module $context Module context.
     * @param int $userid Response owner's user id.
     * @return bool
     */
    public static function can_view_user(
        \stdClass $cm,
        \stdClass $course,
        \context_module $context,
        int $userid
    ): bool {
        if (groups_get_activity_groupmode($cm, $course) !== SEPARATEGROUPS) {
            return true;
        }

        if (has_capability('moodle/site:accessallgroups', $context)) {
            return true;
        }

        $allowedgroups = groups_get_activity_allowed_groups($cm);
        if (empty($allowedgroups)) {
            return false;
        }

        $usergroups = groups_get_all_groups(
            $course->id,
            $userid,
            (int)$cm->groupingid,
            'g.id'
        );

        if (empty($usergroups)) {
            return false;
        }

        return (bool)array_intersect(array_keys($allowedgroups), array_keys($usergroups));
    }
}
