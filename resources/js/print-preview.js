(function () {
    'use strict';

    /**
     * The form's own fields as a query string, empty values dropped.
     */
    function queryOf(form) {
        var pairs = [];

        new FormData(form).forEach(function (value, key) {
            if (value !== '' && value !== null) {
                pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
            }
        });

        return pairs.join('&');
    }

    /**
     * Append a query string to a URL that may already carry one.
     */
    function withQuery(base, query) {
        if (! query) {
            return base;
        }

        return base + (base.indexOf('?') === -1 ? '?' : '&') + query;
    }

    function init(root) {
        var form = root.querySelector('[data-print-form]');
        var frame = root.querySelector('[data-print-frame]');

        if (! form || ! frame) {
            return;
        }

        var base = frame.getAttribute('data-print-src') || frame.getAttribute('src');

        /**
         * Rebuild the preview from what the form currently says. Drawing a
         * document is a full server render, so it happens only when the officer
         * presses Update Preview — never on every keystroke.
         */
        function updatePreview() {
            var query = queryOf(form);
            var bust = '_=' + Date.now();

            frame.src = withQuery(base, query ? query + '&' + bust : bust);
        }

        /**
         * Print and Download carry the form's own values, so what is printed is
         * what the form says even if the preview was not refreshed first.
         */
        function updateLinks() {
            var query = queryOf(form);

            root.querySelectorAll('[data-print-link]').forEach(function (link) {
                link.setAttribute('href', withQuery(link.getAttribute('data-print-link'), query));
            });
        }

        function sync() {
            updatePreview();
            updateLinks();
        }

        var update = root.querySelector('[data-print-update]');

        if (update) {
            update.addEventListener('click', sync);
        }

        root.querySelectorAll('[data-print-link]').forEach(function (link) {
            link.addEventListener('click', updateLinks);
        });

        // The "All homeowners" switch hides the single-resident picker while it
        // is on: the switch replaces that picker rather than revealing it. Pure
        // client-side — it never reaches the server.
        form.querySelectorAll('[data-print-hide]').forEach(function (box) {
            var target = document.querySelector(box.getAttribute('data-print-hide'));

            if (! target) {
                return;
            }

            function apply() {
                target.classList.toggle('d-none', box.checked);
            }

            box.addEventListener('change', apply);
            apply();
        });

        // A control that reveals a field of its own for one of its values (the
        // business permit's "Other" purpose), and nothing else.
        form.querySelectorAll('[data-print-reveal]').forEach(function (control) {
            var target = document.querySelector(control.getAttribute('data-print-reveal'));
            var value = control.getAttribute('data-print-reveal-value');

            if (! target) {
                return;
            }

            function apply() {
                target.classList.toggle('d-none', control.value !== value);
            }

            control.addEventListener('change', apply);
            apply();
        });

        // Clear returns every fillable field to its empty state. The reset is
        // the browser's own; the change events tell Select2 and the show/hide
        // rules about it, and the preview is redrawn from the cleared form.
        var clear = root.querySelector('[data-print-clear]');

        if (clear) {
            clear.addEventListener('click', function () {
                form.reset();

                // Tell Select2 and the show/hide rules the form is back to its
                // defaults — reset() alone does not fire their change events.
                form.querySelectorAll('select, input[type="checkbox"]').forEach(function (control) {
                    control.dispatchEvent(new Event('change', { bubbles: true }));
                });

                sync();
            });
        }

        /**
         * Open the form with any values the page was handed — the details of a
         * homeowner's request, when the officer arrived here from the Requests
         * page. The values land in the real fields (and their change events),
         * so Select2, the reveal rules and the preview all see them.
         */
        function applyPrefill() {
            var raw = root.getAttribute('data-print-prefill');

            if (! raw) {
                return;
            }

            var values;

            try {
                values = JSON.parse(raw);
            } catch (error) {
                return;
            }

            Object.keys(values).forEach(function (name) {
                var field = form.querySelector('[name="' + name + '"]');

                if (! field || values[name] === null || typeof values[name] === 'undefined') {
                    return;
                }

                field.value = values[name];
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        // Start from the record's own values when the page was opened from one,
        // then draw the form (empty or prefilled) so the preview is never stale.
        applyPrefill();
        sync();
    }

    function boot() {
        document.querySelectorAll('[data-print-preview]').forEach(function (root) {
            if (root.dataset.printReady === '1') {
                return;
            }

            root.dataset.printReady = '1';
            init(root);
        });
    }

    document.addEventListener('DOMContentLoaded', boot);
    document.addEventListener('livewire:load', boot);
})();
