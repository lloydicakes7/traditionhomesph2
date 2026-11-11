// ===== Setup =====

window.Thha = window.Thha || {};

// ===== Handlers =====

Thha.confirmClick = function (event) {
    if (typeof event.target.closest !== 'function') {
        return;
    }

    var trigger = event.target.closest('[data-thha-confirm]');

    if (trigger === null) {
        return;
    }

    if (!window.confirm(trigger.getAttribute('data-thha-confirm'))) {
        event.preventDefault();
        event.stopImmediatePropagation();
    }
};

// ===== Init =====

document.addEventListener('click', Thha.confirmClick, true);
