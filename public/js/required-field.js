(function () {
    'use strict';

    // ===== Setup =====

    var MARKER = 'thha-required';

    var FIELD_SELECTOR = 'input, select, textarea';

    var SKIP_TYPES = ['hidden', 'submit', 'button', 'reset', 'image'];

    var componentFields = {};

    var livewireBooted = false;

    var scheduled = false;

    // ===== Helpers =====

    function normalizeName(name) {
        return String(name || '')
            .replace(/\[\]/g, '')
            .replace(/\[/g, '.')
            .replace(/\]/g, '');
    }

    function matchesRule(rule, name) {
        var pattern = rule.split('.').map(function (part) {
            return part === '*' ? '[^.]+' : part.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }).join('\\.');

        return new RegExp('^' + pattern + '$').test(name);
    }

    function namesFor(control) {
        var root = control.closest('[wire\\:id]');

        if (! root) {
            return [];
        }

        return componentFields[root.getAttribute('wire:id')] || [];
    }

    function fieldNames(control) {
        var names = [];

        if (control.name) {
            names.push(normalizeName(control.name));
        }

        Array.prototype.forEach.call(control.attributes, function (attribute) {
            if (attribute.name.indexOf('wire:model') === 0) {
                names.push(normalizeName(attribute.value));
            }
        });

        return names.filter(function (name, index) {
            return name !== '' && names.indexOf(name) === index;
        });
    }

    function livewireComponent(id) {
        try {
            return window.Livewire && typeof window.Livewire.find === 'function'
                ? window.Livewire.find(id)
                : null;
        } catch (error) {
            return null;
        }
    }

    function syncComponent(root) {
        var id = root.getAttribute('wire:id');

        if (! id) {
            return;
        }

        var component = livewireComponent(id);

        if (component && component.serverMemo && component.serverMemo.data) {
            var live = component.serverMemo.data.requiredFields;

            componentFields[id] = Array.isArray(live) ? live : [];

            return;
        }

        var raw = root.getAttribute('wire:initial-data');

        if (! raw) {
            return;
        }

        try {
            var data = JSON.parse(raw).data || {};

            componentFields[id] = Array.isArray(data.requiredFields) ? data.requiredFields : [];
        } catch (error) {
            componentFields[id] = [];
        }
    }

    function syncComponents() {
        Array.prototype.forEach.call(document.querySelectorAll('[wire\\:id]'), syncComponent);
    }

    function controlFromLabel(label) {
        if (label.htmlFor) {
            return document.getElementById(label.htmlFor);
        }

        return label.querySelector(FIELD_SELECTOR);
    }

    function labelFor(control) {
        var label = null;

        if (control.id) {
            label = document.querySelector('label[for="' + control.id.replace(/"/g, '\\"') + '"]');
        }

        if (! label) {
            var group = control.closest('.form-group') || control.closest('.input-group');

            if (group) {
                label = group.querySelector('label');
            }
        }

        return label;
    }

    function stripTrailingAsterisk(label) {
        var nodes = Array.prototype.slice.call(label.childNodes).reverse();

        for (var index = 0; index < nodes.length; index += 1) {
            var node = nodes[index];

            if (node.nodeType === 1 && node.classList.contains(MARKER)) {
                continue;
            }

            var text = node.textContent.replace(/\s*\*+\s*$/, '');

            if (text === node.textContent) {
                continue;
            }

            if (node.nodeType === 3) {
                node.nodeValue = text;
            } else {
                node.textContent = text;
            }

            return;
        }
    }

    function adoptLegacyMarkers() {
        Array.prototype.forEach.call(document.querySelectorAll('label'), function (label) {
            if (label.querySelector('.' + MARKER) || ! /\*\s*$/.test(label.textContent)) {
                return;
            }

            var control = controlFromLabel(label);

            if (! control) {
                return;
            }

            control.setAttribute('data-thha-required', '1');
            stripTrailingAsterisk(label);
        });
    }

    function ensureMarker(label) {
        var marker = label.querySelector('.' + MARKER);

        if (marker) {
            return marker;
        }

        marker = document.createElement('span');
        marker.className = MARKER;
        marker.setAttribute('aria-hidden', 'true');
        marker.textContent = '*';
        label.appendChild(marker);

        return marker;
    }

    function groupControls(control) {
        if ((control.type !== 'checkbox' && control.type !== 'radio') || ! control.name) {
            return [];
        }

        var name = control.name.replace(/"/g, '\\"');

        return Array.prototype.slice.call(document.querySelectorAll('[name="' + name + '"]')).filter(function (other) {
            return other.type === control.type && other.form === control.form;
        });
    }

    function groupLabel(control, controls) {
        var group = control.closest('.form-group');

        if (! group) {
            return null;
        }

        var ids = controls.map(function (other) {
            return other.id;
        }).filter(Boolean);

        var labels = Array.prototype.slice.call(group.querySelectorAll('label'));

        for (var index = 0; index < labels.length; index += 1) {
            if (ids.indexOf(labels[index].htmlFor) === -1) {
                return labels[index];
            }
        }

        return null;
    }

    function isRequired(control, fields) {
        if (control.disabled || SKIP_TYPES.indexOf(control.type) !== -1) {
            return false;
        }

        if (control.matches('[required], [aria-required="true"], [data-thha-required]')) {
            return true;
        }

        return fieldNames(control).some(function (name) {
            return fields.some(function (rule) {
                return matchesRule(rule, name);
            });
        });
    }

    function isEmpty(control) {
        if (control.type === 'checkbox' || control.type === 'radio') {
            var controls = groupControls(control);

            if (controls.length > 1) {
                return ! controls.some(function (other) {
                    return other.checked;
                });
            }

            return ! control.checked;
        }

        if (control.type === 'file') {
            return ! control.files || control.files.length === 0;
        }

        if (control.tagName === 'SELECT' && control.multiple) {
            return control.selectedOptions.length === 0;
        }

        return String(control.value || '').trim() === '';
    }

    function isInvalid(control) {
        if (control.classList.contains('is-invalid') || control.getAttribute('aria-invalid') === 'true') {
            return true;
        }

        return control.willValidate && ! control.checkValidity();
    }

    function markerLabel(control) {
        var controls = groupControls(control);

        if (controls.length > 1) {
            return groupLabel(control, controls);
        }

        return labelFor(control);
    }

    function updateMarker(control) {
        var label = markerLabel(control);

        if (! label) {
            return;
        }

        var marker = label.querySelector('.' + MARKER);

        if (marker) {
            marker.classList.toggle('d-none', ! (isEmpty(control) || isInvalid(control)));
        }
    }

    function decorate(control) {
        if (! isRequired(control, namesFor(control))) {
            return;
        }

        var label = markerLabel(control);

        if (! label) {
            return;
        }

        stripTrailingAsterisk(label);
        ensureMarker(label);
        updateMarker(control);
    }

    function refresh() {
        syncComponents();

        try {
            adoptLegacyMarkers();
        } catch (error) {
            
        }

        Array.prototype.forEach.call(document.querySelectorAll('.' + MARKER), function (marker) {
            marker.classList.add('d-none');
        });

        Array.prototype.forEach.call(document.querySelectorAll(FIELD_SELECTOR), function (control) {
            try {
                decorate(control);
            } catch (error) {
            
            }
        });
    }

    function scheduleRefresh() {
        if (scheduled) {
            return;
        }

        scheduled = true;

        window.setTimeout(function () {
            scheduled = false;
            refresh();
        }, 0);
    }

    function bootLivewire() {
        if (livewireBooted || ! window.Livewire || typeof window.Livewire.hook !== 'function') {
            return;
        }

        livewireBooted = true;

        window.Livewire.hook('message.processed', function () {
            syncComponents();
            scheduleRefresh();
        });
    }

    function observe() {
        if (! window.MutationObserver || ! document.body) {
            return;
        }

        new MutationObserver(scheduleRefresh).observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['required', 'aria-required', 'data-thha-required'],
        });
    }

    function init() {
        refresh();
        bootLivewire();
        observe();
    }

    document.addEventListener('input', function (event) {
        if (event.target.matches && event.target.matches(FIELD_SELECTOR)) {
            updateMarker(event.target);
        }
    });

    document.addEventListener('change', scheduleRefresh);

    document.addEventListener('livewire:load', function () {
        bootLivewire();
        scheduleRefresh();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
