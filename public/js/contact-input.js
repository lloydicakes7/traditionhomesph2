(function ($) {
    'use strict';

    const MAX_LENGTH = 11;

    function normalise(value) {
        let digits = String(value).replace(/\D/g, '');

        if (digits === '') {
            return '';
        }

        if (digits.indexOf('0') !== 0) {
            digits = '0' + digits;
        }

        return digits.slice(0, MAX_LENGTH);
    }

    $(document).on('input', '[data-thha-contact]', function () {
        const cleaned = normalise(this.value);

        if (this.value !== cleaned) {
            this.value = cleaned;
        }
    });
})(jQuery);
