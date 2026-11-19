(function ($) {
    'use strict';

    // ===== Setup =====

    const WHOLE_DIGITS = 13;

    const DECIMALS = 2;

    // ===== Handlers =====

    function digitsOf(value) {
        return String(value).replace(/[^\d.]/g, '');
    }

    function sanitize(value) {
        let text = digitsOf(value);
        let dot = text.indexOf('.');

        if (dot !== -1) {
            text = text.slice(0, dot + 1) + text.slice(dot + 1).replace(/\./g, '');
        }

        let parts = text.split('.');

        parts[0] = parts[0].slice(0, WHOLE_DIGITS);

        if (parts.length === 2) {
            parts[1] = parts[1].slice(0, DECIMALS);

            return parts[0] + '.' + parts[1];
        }

        return parts[0];
    }

    function plain(value) {
        return String(value).replace(/,/g, '');
    }

    function format(value) {
        let clean = sanitize(plain(value));

        if (clean === '' || clean === '.') {
            return '';
        }

        let parts = clean.split('.');
        let cents = (parts[1] || '');

        return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '.' + (cents + '00').slice(0, DECIMALS);
    }

    function showFocused(input) {
        let value = plain(input.value);

        if (input.value !== value) {
            input.value = value;
        }
    }

    function showFormatted(input) {
        let value = format(input.value);

        if (input.value !== value) {
            input.value = value;
        }
    }

    function formatAll() {
        $('[data-thha-money]').each(function () {
            if (document.activeElement !== this) {
                showFormatted(this);
            }
        });
    }

    $(document).on('input', '[data-thha-money]', function () {
        let value = sanitize(this.value);

        if (this.value !== value) {
            this.value = value;
        }
    });

    $(document).on('focusin', '[data-thha-money]', function () {
        showFocused(this);
    });

    $(document).on('focusout', '[data-thha-money]', function () {
        showFormatted(this);
    });

    $(formatAll);

    document.addEventListener('livewire:load', formatAll);

    if (window.Livewire && window.Livewire.hook) {
        window.Livewire.hook('message.processed', formatAll);
    }
})(jQuery);
