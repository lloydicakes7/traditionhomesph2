(function () {
    'use strict';

    var defaults = {
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Choose an option',
        allowClear: false
    };

    function optionsFor($select) {
        return {
            theme: defaults.theme,
            width: defaults.width,
            placeholder: $select.attr('data-placeholder') || defaults.placeholder,
            allowClear: $select.attr('data-allow-clear') === 'true'
        };
    }

    function syncSelect($select) {
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

            $select.select2(optionsFor($select));
            $select.attr('data-select2-value', $select.val() || '');

            $select.off('.thhaSelect2').on(
                'select2:select.thhaSelect2 select2:unselect.thhaSelect2 select2:clear.thhaSelect2',
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

    function initSelects() {
        if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) {
            return;
        }

        window.jQuery('select[data-thha-select2]').each(function () {
            syncSelect(window.jQuery(this));
        });
    }

    function boot() {
        initSelects();

        if (window.Livewire && typeof window.Livewire.hook === 'function') {
            window.Livewire.hook('message.processed', initSelects);
        }
    }

    if (window.Livewire) {
        boot();
    } else {
        document.addEventListener('livewire:load', boot);
    }
})();
