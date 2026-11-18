(function ($) {
    'use strict';

    function closeSubmenus(context) {
        $(context).find('.dropdown-submenu > .dropdown-menu.show').removeClass('show');
        $(context).find('.dropdown-submenu > .dropdown-toggle[aria-expanded="true"]')
            .attr('aria-expanded', 'false');
    }

    $(document).on('click', '.thha-public-navbar .dropdown-submenu > .dropdown-toggle', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var $toggle = $(this);
        var $menu = $toggle.next('.dropdown-menu');
        var isOpen = $menu.hasClass('show');

        closeSubmenus($toggle.closest('.dropdown-menu'));

        if (! isOpen) {
            $menu.addClass('show');
            $toggle.attr('aria-expanded', 'true');
        }
    });

    $(document).on('click', '.thha-public-navbar .nav-link.dropdown-toggle', function () {
        closeSubmenus($(this).closest('.thha-public-navbar'));
    });

    $(document).on('hidden.bs.dropdown', '.thha-public-navbar .nav-item.dropdown', function () {
        closeSubmenus(this);
    });
})(jQuery);
