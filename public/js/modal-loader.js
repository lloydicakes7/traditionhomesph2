(function () {
    'use strict';

    if (window.__modalLoaderAttached) {
        return;
    }
    window.__modalLoaderAttached = true;

    function isLazyModal(modal) {
        return !!(modal && modal.id && modal.querySelector('[data-modal-skeleton]'));
    }

    function eventStem(modal) {
        var base = modal.id.replace(/Modal$/, '');

        return base.charAt(0).toUpperCase() + base.slice(1);
    }

    function relatedRecordId($trigger) {
        return $trigger && $trigger.length ? $trigger.attr('data-id') : null;
    }

    function safeEmit(eventName, payload) {
        if (typeof window.Livewire === 'undefined' || typeof window.Livewire.emit !== 'function') {
            console.warn('[modal-loader] Livewire is not ready yet; could not emit "' + eventName + '".');

            return;
        }

        if (typeof payload !== 'undefined') {
            window.Livewire.emit(eventName, payload);
        } else {
            window.Livewire.emit(eventName);
        }
    }

    function attachListeners() {
        if (typeof window.jQuery === 'undefined') {
            console.warn('[modal-loader] jQuery is not available; Bootstrap 4 modal events cannot be captured.');

            return;
        }

        var $ = window.jQuery;
        var serverOpenedModalId = null;

        $(document).on('show.bs.modal', function (event) {
            var modal = event.target;

            if (!isLazyModal(modal)) {
                return;
            }

            // A dialog the server opened (see `modal-open` below) already
            // carries its loaded content: `load()` fetched the record and
            // rendered before asking Bootstrap for the shell. Pulling the
            // skeleton back over it would stick, because the refresh that
            // follows re-renders the very same HTML. Show what the server
            // rendered instead.
            if (serverOpenedModalId === modal.id) {
                serverOpenedModalId = null;
                return;
            }

            var $modal = $(modal);
            $modal.find('[data-modal-skeleton]').removeClass('d-none');
            $modal.find('[data-modal-content]').addClass('d-none');

            var id = relatedRecordId($(event.relatedTarget));
            var stem = eventStem(modal);

            if (id) {
                safeEmit('load' + stem + 'Modal', id);
                return;
            }

            safeEmit('open' + stem + 'Modal');
        });

        $(document).on('hidden.bs.modal', function (event) {
            var modal = event.target;

            if (!isLazyModal(modal)) {
                return;
            }

            safeEmit('close' + eventStem(modal) + 'Modal');
        });

        $(document).on('hide.bs.modal', function (event) {
            var modal = event.target;

            if (!isLazyModal(modal)) {
                return;
            }

            var $focused = $(modal).find(document.activeElement);

            if ($focused.length && document.activeElement) {
                document.activeElement.blur();
            }
        });

        window.addEventListener('modal-open', function (event) {
            var detail = event.detail || {};
            var id = typeof detail === 'string' ? detail : detail.id;

            if (!id) {
                return;
            }

            // Mark the dialog as server-opened for the synchronous show below —
            // `show.bs.modal` consumes the mark. If the dialog was already
            // shown no event fires, so clear the mark here as well: a later
            // open from a `data-toggle` trigger must still get its skeleton.
            serverOpenedModalId = id;
            $('#' + id).modal('show');
            serverOpenedModalId = null;
        });

        window.addEventListener('modal-close', function (event) {
            var detail = event.detail || {};
            var id = typeof detail === 'string' ? detail : detail.id;

            if (!id) {
                return;
            }

            $('#' + id).modal('hide');
        });
    }

    if (typeof window.Livewire !== 'undefined') {
        attachListeners();
    } else {
        document.addEventListener('livewire:load', attachListeners);
    }
})();
