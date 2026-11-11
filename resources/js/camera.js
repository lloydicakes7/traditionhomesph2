/*
 * Capture a photo of the valid ID from the registration wizard.
 */
(function ($) {
    'use strict';

    var stream = null;
    var video = null;

    function stopStream() {
        if (stream !== null) {
            stream.getTracks().forEach(function (track) {
                track.stop();
            });

            stream = null;
        }

        if (video !== null) {
            video.srcObject = null;
        }
    }

    function showError() {
        $('[data-thha-camera-error]').removeClass('d-none');
    }

    function startCamera() {
        video = document.querySelector('[data-thha-camera-video]');

        $('[data-thha-camera-error]').addClass('d-none');

        if (video === null || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showError();

            return;
        }

        stopStream();

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
            audio: false
        }).then(function (mediaStream) {
            stream = mediaStream;
            video.srcObject = mediaStream;
        }).catch(function () {
            showError();
        });
    }

    function captureFrame() {
        if (video === null || video.videoWidth === 0) {
            showError();

            return;
        }

        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(function (blob) {
            if (blob === null) {
                showError();

                return;
            }

            var input = document.querySelector('[data-thha-id-input]');

            if (input === null) {
                return;
            }

            var file = new File([blob], 'valid-id.jpg', { type: 'image/jpeg' });
            var transfer = new DataTransfer();

            transfer.items.add(file);
            input.files = transfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));

            $('#thhaCameraModal').modal('hide');
        }, 'image/jpeg', 0.9);
    }

    $(document).on('click', '[data-thha-camera-open]', function () {
        $('#thhaCameraModal').one('shown.bs.modal', startCamera);
    });

    $(document).on('click', '[data-thha-camera-capture]', captureFrame);

    $(document).on('hidden.bs.modal', '#thhaCameraModal', stopStream);
})(jQuery);
