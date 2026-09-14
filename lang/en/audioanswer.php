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
 * audioanswer.php
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['audio'] = 'Audio';
$string['audioanswer:addinstance'] = 'Add a new audio answer activity';
$string['audioanswer:submit'] = 'Submit an audio answer';
$string['audioanswer:viewresponses'] = 'View all audio answers';
$string['audioanswername'] = 'Activity name';
$string['backtoactivity'] = 'Back to activity';
$string['completiononsubmit'] = 'Student must submit an audio answer';
$string['currentresponse'] = 'Your current answer';
$string['duration'] = 'Duration';
$string['eventanswersubmitted'] = 'Audio answer submitted';
$string['eventcoursemoduleviewed'] = 'Audio answer activity viewed';
$string['invalidaudiotype'] = 'The uploaded file is not a supported audio recording.';
$string['invalidduration'] = 'The activity has an invalid recording duration setting.';
$string['invalidfilesize'] = 'The uploaded audio file has an invalid size.';
$string['lastsubmitted'] = 'Last submitted';
$string['maxduration'] = 'Maximum recording time';
$string['maxduration_help'] = 'Choose the maximum recording time allowed by the recorder: 30, 60 or 120 seconds.';
$string['microphoneerror'] = 'The microphone could not be accessed.';
$string['microphonepermission'] = 'Waiting for microphone permission…';
$string['missingaudio'] = 'No audio file was received.';
$string['modulename'] = 'Audio answer';
$string['modulenameplural'] = 'Audio answers';
$string['noinstances'] = 'There are no audio answer activities in this course.';
$string['permissiondenied'] = 'Microphone permission was denied by the browser.';
$string['pluginadministration'] = 'Audio answer administration';
$string['pluginname'] = 'Audio answer';
$string['preview'] = 'Preview';
$string['privacy:metadata:audioanswer_response'] = 'Stores each learner\'s audio response metadata.';
$string['privacy:metadata:audioanswer_response:audioanswerid'] = 'The audio answer activity the response belongs to.';
$string['privacy:metadata:audioanswer_response:duration'] = 'The declared recording duration in seconds.';
$string['privacy:metadata:audioanswer_response:filename'] = 'The stored audio filename.';
$string['privacy:metadata:audioanswer_response:mimetype'] = 'The MIME type of the stored recording.';
$string['privacy:metadata:audioanswer_response:timecreated'] = 'The time the response was first created.';
$string['privacy:metadata:audioanswer_response:timemodified'] = 'The time the response was last updated.';
$string['privacy:metadata:audioanswer_response:userid'] = 'The user who submitted the response.';
$string['privacy:metadata:core_files'] = 'Audio recordings are stored in Moodle\'s File API.';
$string['question'] = 'Question';
$string['question_help'] = 'Enter the prompt the learner should answer by recording a short audio response.';
$string['readytorecord'] = 'Ready to record.';
$string['readytosave'] = 'Recording finished. Listen to it before saving.';
$string['record'] = 'Record audio';
$string['recording'] = 'Recording…';
$string['recordingtoolong'] = 'The recording exceeds the allowed duration.';
$string['recordingunsupported'] = 'This browser does not support in-browser audio recording.';
$string['replacehint'] = 'Recording and saving again will replace this answer.';
$string['responses'] = 'Audio responses';
$string['saveanswer'] = 'Save answer';
$string['saved'] = 'Audio answer saved.';
$string['saveerror'] = 'The audio answer could not be saved. Please try again.';
$string['saving'] = 'Saving audio…';
$string['seconds'] = '{$a} seconds';
$string['stoprecording'] = 'Stop';
$string['student'] = 'Student';
$string['submitted'] = 'Submitted';
$string['timelimit'] = 'Maximum recording time: {$a} seconds';
$string['uploaderror'] = 'The audio upload failed.';
$string['viewresponses'] = 'View responses';
