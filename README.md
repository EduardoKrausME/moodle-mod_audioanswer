# mod_audioanswer - Audio answer

A lightweight Moodle activity for short spoken answers.

The teacher writes one question and selects a recording limit of 30, 60, or 120 seconds. The learner records directly in the browser, previews the recording, and saves one audio response. Recording again replaces the previous response.

## Main features

- In-browser recording with the MediaRecorder API.
- 30, 60, or 120 second recording limits.
- One current response per learner, with re-recording supported.
- Teacher report with one audio player per response.
- Moodle File API storage; no external recording service.
- Capability checks and session-key validation.
- MIME-type and upload-size validation.
- Optional automatic completion when an audio answer is submitted.
- Backup and restore support.
- Moodle Privacy API support.
- English and Brazilian Portuguese language packs.

## Requirements

- Moodle 4.5 or later.
- A modern browser with MediaRecorder and getUserMedia support.
- HTTPS is normally required by browsers for microphone access, except on localhost.

## Installation

Copy the `audioanswer` directory to `mod/audioanswer`, then visit Site administration > Notifications.

## Notes about the duration limit

The browser recorder stops automatically at the configured limit. The upload endpoint also rejects a declared duration above the configured limit. Moodle core does not provide a codec-independent server-side audio duration parser, so a deliberately manipulated multipart request cannot be independently measured without an external media probe such as ffprobe.

## License

GPL v3 or later.
