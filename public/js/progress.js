(function () {
    'use strict';

    // ===== Setup =====

    window.Thha = window.Thha || {};

    var items = {};
    var sequence = 0;
    var doneLinger = 4000;
    var failedLinger = 8000;
    var deadline = 10 * 60 * 1000;

    // ===== Handlers =====

    function stack() {
        var element = document.getElementById('thha-progress-stack');

        if (element !== null) {
            return element;
        }

        element = document.createElement('div');
        element.id = 'thha-progress-stack';
        element.className = 'thha-progress-stack';
        element.setAttribute('role', 'status');
        element.setAttribute('aria-live', 'polite');
        document.body.appendChild(element);

        return element;
    }

    function make(tag, className, text) {
        var element = document.createElement(tag);

        if (className) {
            element.className = className;
        }

        if (typeof text !== 'undefined') {
            element.textContent = text;
        }

        return element;
    }

    function start(key, label, options) {
        options = options || {};

        var itemKey = key || 'progress-' + (++sequence);

        if (items[itemKey]) {
            items[itemKey].title.textContent = label;
            return itemKey;
        }

        var root = make('div', 'thha-progress-item');
        var head = make('div', 'thha-progress-head');
        var icon = make('i', 'fas fa-fw fa-circle-notch fa-spin thha-progress-icon');
        var title = make('span', 'thha-progress-title', label);
        var close = make('button', 'thha-progress-close', '\u00d7');
        var bar = make('div', 'progress thha-progress-bar');
        var fill = make('div', 'progress-bar progress-bar-striped progress-bar-animated bg-info');
        var message = make('div', 'thha-progress-message');

        close.type = 'button';
        close.setAttribute('aria-label', 'Dismiss');
        fill.setAttribute('role', 'progressbar');
        fill.setAttribute('aria-valuemin', '0');
        fill.setAttribute('aria-valuemax', '100');
        fill.setAttribute('aria-valuenow', '100');
        fill.style.width = '100%';

        bar.appendChild(fill);
        head.appendChild(icon);
        head.appendChild(title);
        head.appendChild(close);
        root.appendChild(head);
        root.appendChild(bar);
        root.appendChild(message);
        stack().appendChild(root);

        items[itemKey] = {
            key: itemKey,
            root: root,
            title: title,
            icon: icon,
            fill: fill,
            message: message,
            wait: !!options.wait,
            finished: false,
            timer: null,
            deadline: null
        };

        close.addEventListener('click', function () {
            remove(items[itemKey]);
        });

        items[itemKey].deadline = window.setTimeout(function () {
            settle('failed', itemKey, 'The operation did not report back. Check the page for the outcome.');
        }, deadline);

        return itemKey;
    }

    function settle(state, key, message) {
        var item = items[key];

        if (!item || item.finished) {
            return;
        }

        item.finished = true;
        window.clearTimeout(item.deadline);
        delete items[key];

        var classes = {
            done: 'is-done',
            warning: 'is-warning',
            failed: 'is-failed'
        };
        var icons = {
            done: 'fas fa-fw fa-check-circle',
            warning: 'fas fa-fw fa-exclamation-triangle',
            failed: 'fas fa-fw fa-exclamation-circle'
        };
        var barClasses = {
            done: 'bg-success',
            warning: 'bg-warning',
            failed: 'bg-danger'
        };

        item.root.classList.add(classes[state] || classes.done);
        item.icon.className = (icons[state] || icons.done) + ' thha-progress-icon';
        item.fill.className = 'progress-bar ' + (barClasses[state] || barClasses.done);
        item.fill.style.width = '100%';
        item.fill.setAttribute('aria-valuenow', '100');
        item.message.textContent = message || '';

        var linger = state === 'failed' ? failedLinger : doneLinger;

        item.timer = window.setTimeout(function () {
            remove(item);
        }, linger);
    }

    function complete(key, message) {
        settle('done', key, message || 'Finished.');
    }

    function fail(key, message) {
        settle('failed', key, message || 'The operation could not be completed.');
    }

    function remove(item) {
        if (!item || !item.root || !item.root.parentNode) {
            return;
        }

        window.clearTimeout(item.timer);
        window.clearTimeout(item.deadline);
        delete items[item.key];
        item.root.classList.add('is-leaving');

        window.setTimeout(function () {
            if (item.root.parentNode) {
                item.root.parentNode.removeChild(item.root);
            }
        }, 250);
    }

    function latest() {
        var keys = Object.keys(items);

        return keys.length ? items[keys[keys.length - 1]] : null;
    }

    function settleLatest(type, message) {
        var item = latest();

        if (!item) {
            return;
        }

        if (type === 'error') {
            settle('failed', item.key, message);
            return;
        }

        if (type === 'warning') {
            settle('warning', item.key, message);
            return;
        }

        settle('done', item.key, message);
    }

    function failLatest(message) {
        var item = latest();

        if (item) {
            settle('failed', item.key, message);
        }
    }

    function finishAuto() {
        Object.keys(items).forEach(function (key) {
            var item = items[key];

            if (item.wait || item.finished) {
                return;
            }

            settle('done', key, 'Finished.');
        });
    }

    function filename(header) {
        var utf8 = /filename\*=UTF-8''([^;]+)/i.exec(header || '');
        var plain = /filename="?([^";]+)"?/i.exec(header || '');

        if (utf8) {
            return decodeURIComponent(utf8[1]);
        }

        return plain ? plain[1] : 'download';
    }

    function save(blob, name) {
        var url = window.URL.createObjectURL(blob);
        var link = document.createElement('a');

        link.href = url;
        link.download = name;
        document.body.appendChild(link);
        link.click();

        document.body.removeChild(link);

        window.setTimeout(function () {
            window.URL.revokeObjectURL(url);
        }, 1000);
    }

    function fetchDownload(key, url) {
        window.fetch(url, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            var name = filename(response.headers.get('Content-Disposition'));

            return response.blob().then(function (blob) {
                save(blob, name);
            });
        }).then(function () {
            complete(key, 'Download ready.');
        }).catch(function () {
            fail(key, 'The download could not be prepared.');
        });
    }

    function startDownload(event, trigger) {
        var label = trigger.getAttribute('data-thha-progress-download') || 'Preparing download…';
        var key = start(null, label, { wait: true });

        event.preventDefault();

        window.setTimeout(function () {
            fetchDownload(key, trigger.getAttribute('href'));
        }, 0);
    }

    function onClick(event) {
        if (event.button !== 0 || typeof event.target.closest !== 'function') {
            return;
        }

        var trigger = event.target.closest('[data-thha-progress], [data-thha-progress-download]');

        if (!trigger) {
            return;
        }

        if (trigger.hasAttribute('data-thha-progress-download')) {
            startDownload(event, trigger);
            return;
        }

        var label = trigger.getAttribute('data-thha-progress') || 'Working…';
        var key = trigger.getAttribute('data-thha-progress-key') || label;

        start(key, label, { wait: trigger.hasAttribute('data-thha-progress-wait') });
    }

    function bindLivewire() {
        if (window.__thhaProgressBound || typeof window.Livewire === 'undefined') {
            return;
        }

        window.__thhaProgressBound = true;

        window.Livewire.on('notify', function (type, message) {
            settleLatest(type, message);
        });

        window.Livewire.on('csv-import-confirmed', function () {
            start('csv-import', 'Importing CSV…', { wait: true });
        });

        window.Livewire.hook('message.processed', finishAuto);
        window.Livewire.hook('message.failed', function () {
            failLatest('The request could not be completed.');
        });
    }

    // ===== Init =====

    window.Thha.progress = {
        start: start,
        complete: complete,
        fail: fail
    };

    document.addEventListener('click', onClick, true);
    document.addEventListener('livewire:load', bindLivewire);

    bindLivewire();
})();
