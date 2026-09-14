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
 * recorder.js
 *
 * @package   mod_audioanswer
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function($) {
    var getString = function(key) {
        return M.util.get_string(key, "mod_audioanswer");
    };

    var formatTime = function(seconds) {
        var minutes = Math.floor(seconds / 60);
        var remaining = seconds % 60;
        return String(minutes).padStart(2, "0") + ":" + String(remaining).padStart(2, "0");
    };

    var chooseMimeType = function() {
        var candidates = [
            "audio/webm;codecs=opus",
            "audio/ogg;codecs=opus",
            "audio/mp4",
            "audio/webm"
        ];
        if (typeof MediaRecorder === "undefined" || typeof MediaRecorder.isTypeSupported !== "function") {
            return "";
        }
        for (var i = 0; i < candidates.length; i++) {
            if (MediaRecorder.isTypeSupported(candidates[i])) {
                return candidates[i];
            }
        }
        return "";
    };

    var extensionForType = function(type) {
        if (type.indexOf("ogg") !== -1) {
            return "ogg";
        }
        if (type.indexOf("mp4") !== -1) {
            return "m4a";
        }
        if (type.indexOf("mpeg") !== -1) {
            return "mp3";
        }
        if (type.indexOf("wav") !== -1) {
            return "wav";
        }
        return "webm";
    };

    var init = function(config) {
        var root = $("[data-region='audioanswer-recorder']");
        if (!root.length) {
            return;
        }

        var recordButton = root.find("[data-action='record']");
        var stopButton = root.find("[data-action='stop']");
        var saveButton = root.find("[data-action='save']");
        var preview = root.find("[data-region='preview']");
        var previewWrapper = root.find("[data-region='preview-wrapper']");
        var status = root.find("[data-region='status']");
        var timer = root.find("[data-region='timer']");
        var recorder = null;
        var stream = null;
        var chunks = [];
        var timerHandle = null;
        var startedAt = 0;
        var recordedBlob = null;
        var recordedDuration = 0;
        var previewUrl = null;

        var setStatus = function(text, type) {
            status.removeClass("alert-light alert-info alert-success alert-danger");
            status.addClass(type || "alert-light");
            status.text(text);
        };

        var resetPreview = function() {
            recordedBlob = null;
            recordedDuration = 0;
            saveButton.prop("disabled", true);
            previewWrapper.addClass("d-none");
            preview.removeAttr("src");
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }
        };

        var stopTracks = function() {
            if (stream) {
                stream.getTracks().forEach(function(track) {
                    track.stop();
                });
                stream = null;
            }
        };

        var updateTimer = function() {
            var elapsed = Math.min(config.maxduration, Math.floor((Date.now() - startedAt) / 1000));
            timer.text(formatTime(elapsed) + " / " + formatTime(config.maxduration));
            if (elapsed >= config.maxduration && recorder && recorder.state === "recording") {
                recorder.stop();
            }
        };

        var finishRecording = function() {
            if (timerHandle) {
                clearInterval(timerHandle);
                timerHandle = null;
            }

            recordedDuration = Math.max(1, Math.min(
                config.maxduration,
                Math.ceil((Date.now() - startedAt) / 1000)
            ));
            timer.text(formatTime(recordedDuration) + " / " + formatTime(config.maxduration));
            stopTracks();

            var type = recorder && recorder.mimeType ? recorder.mimeType : "audio/webm";
            recordedBlob = new Blob(chunks, {type: type});
            if (!recordedBlob.size) {
                setStatus(getString("saveerror"), "alert-danger");
                return;
            }

            previewUrl = URL.createObjectURL(recordedBlob);
            preview.attr("src", previewUrl);
            previewWrapper.removeClass("d-none");
            saveButton.prop("disabled", false);
            recordButton.prop("disabled", false);
            stopButton.prop("disabled", true);
            setStatus(getString("readytosave"), "alert-success");
        };

        recordButton.on("click", function() {
            if (typeof MediaRecorder === "undefined" || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setStatus(getString("recordingunsupported"), "alert-danger");
                return;
            }

            resetPreview();
            setStatus(getString("microphonepermission"), "alert-info");

            navigator.mediaDevices.getUserMedia({audio: true}).then(function(mediaStream) {
                stream = mediaStream;
                chunks = [];
                var mimeType = chooseMimeType();
                recorder = mimeType ? new MediaRecorder(stream, {mimeType: mimeType}) : new MediaRecorder(stream);

                recorder.addEventListener("dataavailable", function(event) {
                    if (event.data && event.data.size > 0) {
                        chunks.push(event.data);
                    }
                });
                recorder.addEventListener("stop", finishRecording, {once: true});
                recorder.addEventListener("error", function() {
                    stopTracks();
                    setStatus(getString("microphoneerror"), "alert-danger");
                    recordButton.prop("disabled", false);
                    stopButton.prop("disabled", true);
                });

                startedAt = Date.now();
                recorder.start(250);
                recordButton.prop("disabled", true);
                stopButton.prop("disabled", false);
                setStatus(getString("recording"), "alert-info");
                timer.text("00:00 / " + formatTime(config.maxduration));
                timerHandle = setInterval(updateTimer, 250);
            }).catch(function(error) {
                var message = error && error.name === "NotAllowedError" ?
                    getString("permissiondenied") : getString("microphoneerror");
                setStatus(message, "alert-danger");
                stopTracks();
            });
        });

        stopButton.on("click", function() {
            if (recorder && recorder.state === "recording") {
                recorder.stop();
            }
        });

        saveButton.on("click", function() {
            if (!recordedBlob || !recordedDuration) {
                return;
            }

            saveButton.prop("disabled", true);
            recordButton.prop("disabled", true);
            setStatus(getString("saving"), "alert-info");

            var type = recordedBlob.type || "audio/webm";
            var formData = new FormData();
            formData.append("cmid", config.cmid);
            formData.append("duration", recordedDuration);
            formData.append("sesskey", config.sesskey);
            formData.append("audio", recordedBlob, "answer." + extensionForType(type));

            fetch(config.saveurl, {
                method: "POST",
                credentials: "same-origin",
                body: formData
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error("HTTP " + response.status);
                }
                return response.json();
            }).then(function(data) {
                if (!data.success) {
                    throw new Error(data.error || getString("saveerror"));
                }
                setStatus(getString("saved"), "alert-success");
                window.location.reload();
            }).catch(function() {
                setStatus(getString("saveerror"), "alert-danger");
                saveButton.prop("disabled", false);
                recordButton.prop("disabled", false);
            });
        });

        window.addEventListener("beforeunload", function() {
            stopTracks();
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
        });
    };

    return {
        init: init
    };
});
