(function () {
    'use strict';

    var options = {
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Choose an account…',
        allowClear: false
    };

    function syncAccountSelect($select) {
        var hasContainer = $select.next('.select2-container').length > 0;
        var hasInstance = !! $select.data('select2');

        if (!hasContainer || !hasInstance) {
            if ($select.hasClass('select2-hidden-accessible')) {
                try {
                    $select.select2('destroy');
                } catch (error) {
                    $select.removeClass('select2-hidden-accessible');
                }
            }

            $select.select2(options);
            $select.attr('data-select2-value', $select.val() || '');

            $select.off('.journalAccount').on(
                'select2:select.journalAccount select2:unselect.journalAccount select2:clear.journalAccount',
                function (event) {
                    event.target.dispatchEvent(new Event('change', { bubbles: true }));
                }
            );

            return;
        }

        var value = $select.val() || '';

        if (($select.attr('data-select2-value') || '') !== value) {
            $select.val(value).trigger('change.select2');
            $select.attr('data-select2-value', value);
        }
    }

    function initAccountSelects() {
        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) {
            return;
        }

        window.jQuery('select[data-thha-select2]').each(function () {
            syncAccountSelect(window.jQuery(this));
        });
    }

    function boot() {
        initAccountSelects();

        if (window.Livewire && typeof window.Livewire.hook === 'function') {
            window.Livewire.hook('message.processed', initAccountSelects);
        }
    }

    if (window.Livewire) {
        boot();
    } else {
        document.addEventListener('livewire:load', boot);
    }
})();
