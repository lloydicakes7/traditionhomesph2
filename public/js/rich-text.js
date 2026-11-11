(function () {
    'use strict';

    function editorFor(id) {
        return document.querySelector('[data-rich-text="' + id + '"]');
    }

    window.addEventListener('thha:rich-text', function (event) {
        var detail = event.detail || {};
        var $ = window.jQuery;

        if (! detail.id || ! $ || ! $.fn.summernote) {
            return;
        }

        var editor = editorFor(detail.id);

        if (! editor || ! $(editor).data('summernote')) {
            return;
        }

        $(editor).summernote('code', detail.value || '');
    });
})();
