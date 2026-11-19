(function ($) {
    'use strict';

    // ===== Setup =====

    const PREFIX = '0';

    const COUNTRY = '63';

    const LENGTH = 11;

    const DIRECTORY_LENGTH = 11;

    // ===== Handlers =====

    function digitsOf(value) {
        return String(value).replace(/\D/g, '');
    }

    function normalise(value) {
        let digits = digitsOf(value);

        if (digits === '') {
            return '';
        }

        if (digits.indexOf(COUNTRY) === 0 && digits.length > LENGTH) {
            digits = digits.slice(COUNTRY.length);
        }

        if (digits.charAt(0) !== PREFIX) {
            digits = PREFIX + digits;
        }

        return digits.slice(0, LENGTH);
    }

    $(document).on('input', '[data-thha-contact]', function () {
        const cleaned = normalise(this.value);

        if (this.value !== cleaned) {
            this.value = cleaned;
        }
    });

    $(document).on('input', '[data-thha-directory-phone]', function () {
        const cleaned = digitsOf(this.value).slice(0, DIRECTORY_LENGTH);

        if (this.value !== cleaned) {
            this.value = cleaned;
        }
    });
})(jQuery);
