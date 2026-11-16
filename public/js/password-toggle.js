(function ($) {
    'use strict';

    // ===== Setup =====

    const REVEAL_ICON = 'fa-eye-slash';

    const CONCEAL_ICON = 'fa-eye';

    // ===== Handlers =====

    $(document).on('click', '[data-thha-password-toggle]', function () {
        const button = $(this);
        const input = button.closest('.input-group').find('input').first();
        const reveal = input.attr('type') === 'password';
        const caption = reveal ? 'Hide password' : 'Show password';

        input.attr('type', reveal ? 'text' : 'password');

        button.attr('aria-pressed', reveal ? 'true' : 'false')
            .attr('title', caption)
            .attr('aria-label', caption)
            .find('i')
            .toggleClass(REVEAL_ICON, reveal)
            .toggleClass(CONCEAL_ICON, ! reveal);
    });
})(jQuery);
