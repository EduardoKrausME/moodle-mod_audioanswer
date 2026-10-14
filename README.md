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

## Optional AI transcription with local_ai_bridge

Install [local_ai_bridge](https://github.com/EduardoKrausME/moodle-local_ai_bridge) and
configure a tenant purpose with ID \`audioanswer-transcription\`. Add a route for the users'
AI role to an OpenAI connection using a speech-to-text model (for example
\`gpt-4o-mini-transcribe\`). Configure access and credits in AI Bridge, and keep Moodle
cron running to process ad hoc transcription tasks.

Teachers can enable **Transcribe audio answers with AI Bridge** on each activity. Each
new recording is saved normally and queued for transcription without holding the HTTP
upload open. Students see their own transcript and progress; teachers with response
permissions see transcripts and a retry button for failed/older recordings in the
responses report. Re-recording clears old text, and a worker never attaches an outdated
transcript to a newer answer. Audio remains playable when the bridge is missing, a route
fails, or credits are exhausted.

Important: When enabled, recordings are transmitted to the configured third-party AI
provider. Review data protection requirements before enabling this feature. The plugin
stores only the final text and status alongside the existing private audio; AI Bridge
logs usage metadata without storing the recording or transcript. Deleting a response
removes its transcript; backups and Moodle Privacy API export include transcript data.
Transcription is an assistive aid and can make mistakes; it does not grade answers.

## License

GPL v3 or later.
