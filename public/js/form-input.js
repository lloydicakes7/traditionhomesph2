(function ($) {
    'use strict';

    // ===== Setup =====

    const PHILSYS_GROUP = 4;

    const PHILSYS_DIGITS = 16;

    const DASH = '-';

    // ===== Handlers =====

    function capitalize(value) {
        return value.replace(/(^|\s)(\S)/g, function (match, space, letter) {
            return space + letter.toUpperCase();
        });
    }

    function philsys(value) {
        const digits = String(value).replace(/\D/g, '').slice(0, PHILSYS_DIGITS);

        return digits.replace(new RegExp('(.{' + PHILSYS_GROUP + '})(?=.)', 'g'), '$1' + DASH);
    }

    $(document).on('input', '[data-thha-capitalize]', function () {
        const capitalized = capitalize(this.value);

        if (this.value !== capitalized) {
            this.value = capitalized;
        }
    });

    $(document).on('input', '[data-thha-uppercase]', function () {
        const uppercased = this.value.toUpperCase();

        if (this.value !== uppercased) {
            this.value = uppercased;
        }
    });

    $(document).on('input', '[data-thha-philsys]', function () {
        const formatted = philsys(this.value);

        if (this.value !== formatted) {
            this.value = formatted;
        }
    });
})(jQuery);
