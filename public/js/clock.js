(function () {
    'use strict';

    var formatter = new Intl.DateTimeFormat('en-PH', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });

    function render() {
        var stamps = document.querySelectorAll('[data-thha-clock]');

        if (!stamps.length) {
            return;
        }

        var now = formatter.format(new Date());

        stamps.forEach(function (stamp) {
            stamp.textContent = now;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        render();
        window.setInterval(render, 1000);
    });
})();
