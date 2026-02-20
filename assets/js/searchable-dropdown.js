/**
 * Searchable Dropdown Component
 * Replaces native <select> elements with a searchable dropdown UI.
 * Adds an optional "OTHER" option that reveals a free-text input.
 *
 * Usage:
 *   initSearchableDropdowns('#record-form select');
 *   refreshSearchableDropdown('target');
 */
(function () {
    'use strict';

    var instances = [];

    /* ------------------------------------------------------------------ */
    /*  Constructor                                                       */
    /* ------------------------------------------------------------------ */
    function SearchableDropdown(selectEl, opts) {
        if (selectEl.dataset.sdInit) return;
        selectEl.dataset.sdInit = '1';

        this.select = selectEl;
        this.cfg = Object.assign({
            addOther: true,
            otherLabel: '— OTHER (specify below) —',
            searchPlaceholder: 'Type to search\u2026'
        }, opts || {});

        /* honour data-attribute overrides */
        if (selectEl.dataset.sdNoOther !== undefined) this.cfg.addOther = false;

        this.isOpen = false;
        this.items = [];
        this.customOptionEl = null;

        this._extractItems();
        this._buildDOM();
        this._bindEvents();

        /* Sync initial disabled state to custom trigger */
        if (this.select.disabled) {
            this.trigger.disabled = true;
            this.trigger.classList.add('sd-disabled');
        }

        instances.push(this);
    }

    /* ------------------------------------------------------------------ */
    /*  Extract options from the native <select>                          */
    /* ------------------------------------------------------------------ */
    SearchableDropdown.prototype._extractItems = function () {
        this.items = [];
        var self = this;

        Array.from(this.select.children).forEach(function (child) {
            if (child.tagName === 'OPTGROUP') {
                self.items.push({ type: 'group', label: child.label });
                Array.from(child.children).forEach(function (opt) {
                    if (opt.value !== '') {
                        self.items.push({ type: 'option', value: opt.value, label: opt.textContent.trim() });
                    }
                });
            } else if (child.tagName === 'OPTION' && child.value !== '') {
                self.items.push({ type: 'option', value: child.value, label: child.textContent.trim() });
            }
        });

        if (this.cfg.addOther) {
            this.items.push({ type: 'option', value: '__OTHER__', label: this.cfg.otherLabel, isOther: true });
        }
    };

    /* ------------------------------------------------------------------ */
    /*  Build the custom DOM                                              */
    /* ------------------------------------------------------------------ */
    SearchableDropdown.prototype._buildDOM = function () {
        /* hide native select */
        this.select.style.display = 'none';
        this.select.setAttribute('tabindex', '-1');
        this.select.setAttribute('aria-hidden', 'true');

        /* wrapper */
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'sd-wrap';
        this.select.parentNode.insertBefore(this.wrapper, this.select.nextSibling);

        /* trigger button */
        this.trigger = document.createElement('button');
        this.trigger.type = 'button';
        this.trigger.className = 'sd-trigger';

        var placeholder = (this.select.options[0] && this.select.options[0].value === '')
            ? this.select.options[0].textContent
            : 'Select\u2026';

        this.triggerText = document.createElement('span');
        this.triggerText.className = 'sd-trigger-text';
        this.triggerText.textContent = placeholder;

        this.triggerArrow = document.createElement('span');
        this.triggerArrow.className = 'sd-trigger-arrow';
        this.triggerArrow.innerHTML =
            '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';

        this.trigger.appendChild(this.triggerText);
        this.trigger.appendChild(this.triggerArrow);
        this.wrapper.appendChild(this.trigger);

        /* dropdown panel */
        this.panel = document.createElement('div');
        this.panel.className = 'sd-panel';
        this.panel.style.display = 'none';

        /* search bar */
        this.searchWrap = document.createElement('div');
        this.searchWrap.className = 'sd-search-wrap';

        this.searchInput = document.createElement('input');
        this.searchInput.type = 'text';
        this.searchInput.className = 'sd-search';
        this.searchInput.placeholder = this.cfg.searchPlaceholder;
        this.searchInput.setAttribute('autocomplete', 'off');

        this.searchWrap.appendChild(this.searchInput);
        this.panel.appendChild(this.searchWrap);

        /* options list */
        this.list = document.createElement('div');
        this.list.className = 'sd-list';
        this.list.setAttribute('role', 'listbox');
        this._renderItems('');
        this.panel.appendChild(this.list);

        this.wrapper.appendChild(this.panel);

        /* OTHER free-text input (below trigger, shown only when OTHER selected) */
        if (this.cfg.addOther) {
            this.otherWrap = document.createElement('div');
            this.otherWrap.className = 'sd-other-wrap';
            this.otherWrap.style.display = 'none';

            this.otherInput = document.createElement('input');
            this.otherInput.type = 'text';
            this.otherInput.className = 'sd-other-input';
            this.otherInput.placeholder = 'Please specify\u2026';

            this.otherWrap.appendChild(this.otherInput);
            this.wrapper.appendChild(this.otherWrap);
        }

        /* reflect current select value */
        this._updateDisplay(this.select.value);
    };

    /* ------------------------------------------------------------------ */
    /*  Render filtered options                                           */
    /* ------------------------------------------------------------------ */
    SearchableDropdown.prototype._renderItems = function (filter) {
        var self = this;
        this.list.innerHTML = '';
        var q = (filter || '').toLowerCase();
        var hasVisible = false;
        var groupEl = null;
        var groupHasVisible = false;

        this.items.forEach(function (item) {
            if (item.type === 'group') {
                /* flush previous group header visibility */
                if (groupEl && !groupHasVisible) groupEl.style.display = 'none';

                groupEl = document.createElement('div');
                groupEl.className = 'sd-group-label';
                groupEl.textContent = item.label;
                self.list.appendChild(groupEl);
                groupHasVisible = false;
                return;
            }

            /* filtering (OTHER always visible) */
            if (q && !item.isOther && item.label.toLowerCase().indexOf(q) === -1) return;

            var el = document.createElement('div');
            el.className = 'sd-option';
            if (item.isOther) el.classList.add('sd-option-other');
            if (item.value === self.select.value) el.classList.add('sd-option-selected');
            el.setAttribute('role', 'option');
            el.setAttribute('data-value', item.value);
            el.textContent = item.label;

            el.addEventListener('click', function (e) {
                e.stopPropagation();
                self._selectItem(item);
            });

            self.list.appendChild(el);
            hasVisible = true;
            groupHasVisible = true;
        });

        /* hide last orphan group header */
        if (groupEl && !groupHasVisible) groupEl.style.display = 'none';

        if (!hasVisible) {
            var empty = document.createElement('div');
            empty.className = 'sd-empty';
            empty.textContent = 'No matches found';
            this.list.appendChild(empty);
        }
    };

    /* ------------------------------------------------------------------ */
    /*  Select an item                                                    */
    /* ------------------------------------------------------------------ */
    SearchableDropdown.prototype._selectItem = function (item) {
        if (item.isOther) {
            this.triggerText.textContent = 'OTHER';
            this.triggerText.classList.add('sd-has-value');
            if (this.otherWrap) this.otherWrap.style.display = 'block';
            this._syncToNative('');
            this.close();
            this.otherInput.focus();
        } else {
            this.triggerText.textContent = item.label;
            this.triggerText.classList.add('sd-has-value');
            if (this.otherWrap) this.otherWrap.style.display = 'none';
            if (this.otherInput) this.otherInput.value = '';
            this._syncToNative(item.value);
            this.close();
        }
    };

    /* ------------------------------------------------------------------ */
    /*  Sync value back to native <select>                                */
    /* ------------------------------------------------------------------ */
    SearchableDropdown.prototype._syncToNative = function (value) {
        /* does the value already exist as an <option>? */
        var exists = false;
        for (var i = 0; i < this.select.options.length; i++) {
            if (this.select.options[i].value === value) { exists = true; break; }
        }

        /* remove previously-injected custom option */
        if (this.customOptionEl && this.customOptionEl.parentNode) {
            this.customOptionEl.parentNode.removeChild(this.customOptionEl);
            this.customOptionEl = null;
        }

        /* inject a dynamic option when needed */
        if (!exists && value !== '') {
            this.customOptionEl = document.createElement('option');
            this.customOptionEl.value = value;
            this.customOptionEl.textContent = value;
            this.select.appendChild(this.customOptionEl);
        }

        this.select.value = value;
        this.select.dispatchEvent(new Event('change', { bubbles: true }));
    };

    /* ------------------------------------------------------------------ */
    /*  Update display text to match current native value                 */
    /* ------------------------------------------------------------------ */
    SearchableDropdown.prototype._updateDisplay = function (value) {
        if (!value || value === '') {
            var ph = (this.select.options[0] && this.select.options[0].value === '')
                ? this.select.options[0].textContent
                : 'Select\u2026';
            this.triggerText.textContent = ph;
            this.triggerText.classList.remove('sd-has-value');
            if (this.otherWrap) this.otherWrap.style.display = 'none';
            if (this.otherInput) this.otherInput.value = '';
            return;
        }

        for (var i = 0; i < this.items.length; i++) {
            if (this.items[i].type === 'option' && this.items[i].value === value) {
                this.triggerText.textContent = this.items[i].label;
                this.triggerText.classList.add('sd-has-value');
                return;
            }
        }
        /* value not in predefined list – show as-is */
        this.triggerText.textContent = value;
        this.triggerText.classList.add('sd-has-value');
    };

    /* ------------------------------------------------------------------ */
    /*  Open / Close / Toggle                                             */
    /* ------------------------------------------------------------------ */
    SearchableDropdown.prototype.open = function () {
        if (this.isOpen || this.select.disabled) return;
        /* close every other instance first */
        instances.forEach(function (inst) { if (inst.isOpen) inst.close(); });

        this.isOpen = true;
        this.panel.style.display = 'flex';
        this.wrapper.classList.add('sd-open');
        this.searchInput.value = '';
        this._renderItems('');

        var self = this;
        requestAnimationFrame(function () {
            self.searchInput.focus();
            var sel = self.list.querySelector('.sd-option-selected');
            if (sel) sel.scrollIntoView({ block: 'nearest' });
        });
    };

    SearchableDropdown.prototype.close = function () {
        if (!this.isOpen) return;
        this.isOpen = false;
        this.panel.style.display = 'none';
        this.wrapper.classList.remove('sd-open');
    };

    SearchableDropdown.prototype.toggle = function () {
        this.isOpen ? this.close() : this.open();
    };

    /* ------------------------------------------------------------------ */
    /*  Event binding                                                     */
    /* ------------------------------------------------------------------ */
    SearchableDropdown.prototype._bindEvents = function () {
        var self = this;

        /* trigger click */
        this.trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            self.toggle();
        });

        /* search typing */
        this.searchInput.addEventListener('input', function () {
            self._renderItems(this.value);
        });

        /* keyboard: Escape to close, Enter to select first visible */
        this.searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                self.close();
                self.trigger.focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                var first = self.list.querySelector('.sd-option');
                if (first) first.click();
            }
        });

        /* OTHER text input → sync */
        if (this.otherInput) {
            this.otherInput.addEventListener('input', function () {
                self._syncToNative(this.value.trim());
            });
        }

        /* close on outside click */
        document.addEventListener('click', function (e) {
            if (self.isOpen && !self.wrapper.contains(e.target)) self.close();
        });

        /* form reset */
        var form = this.select.closest('form');
        if (form) {
            form.addEventListener('reset', function () {
                setTimeout(function () {
                    self._updateDisplay('');
                    if (self.otherInput) self.otherInput.value = '';
                    if (self.customOptionEl && self.customOptionEl.parentNode) {
                        self.customOptionEl.parentNode.removeChild(self.customOptionEl);
                        self.customOptionEl = null;
                    }
                    self._renderItems('');
                }, 10);
            });
        }
    };

    /* ------------------------------------------------------------------ */
    /*  Refresh (re-read options from the native <select>)                */
    /* ------------------------------------------------------------------ */
    SearchableDropdown.prototype.refresh = function () {
        this._extractItems();
        this._renderItems('');
        this._updateDisplay(this.select.value);
        /* Sync disabled state to custom trigger */
        if (this.select.disabled) {
            this.trigger.disabled = true;
            this.trigger.classList.add('sd-disabled');
        } else {
            this.trigger.disabled = false;
            this.trigger.classList.remove('sd-disabled');
        }
    };

    /* ------------------------------------------------------------------ */
    /*  Public API                                                        */
    /* ------------------------------------------------------------------ */
    window.SearchableDropdown = SearchableDropdown;

    /**
     * Initialise searchable dropdowns for all <select> elements matching
     * the given CSS selector (default: '#record-form select').
     */
    window.initSearchableDropdowns = function (selector, opts) {
        var selects = document.querySelectorAll(selector || '#record-form select');
        selects.forEach(function (sel) {
            if (!sel.dataset.sdInit) new SearchableDropdown(sel, opts);
        });
    };

    /**
     * Refresh a specific instance by the <select> element's id.
     * Useful after dynamically populating options (e.g. Target list).
     */
    window.refreshSearchableDropdown = function (selectId) {
        for (var i = 0; i < instances.length; i++) {
            if (instances[i].select.id === selectId) {
                instances[i].refresh();
                return;
            }
        }
    };

    /**
     * Refresh a specific instance by its <select> DOM element reference.
     * Useful for repeater rows where selects have no unique id.
     */
    window.refreshSearchableDropdownEl = function (selectEl) {
        for (var i = 0; i < instances.length; i++) {
            if (instances[i].select === selectEl) {
                instances[i].refresh();
                return;
            }
        }
    };

    /**
     * Destroy the SD instance for a <select>, remove its wrapper, then
     * create a fresh instance.  Use when the <select>'s options have been
     * completely rebuilt (e.g. cascading dropdown).
     */
    window.reinitSearchableDropdown = function (selectEl, opts) {
        /* 1. Remove old instance from array */
        for (var i = instances.length - 1; i >= 0; i--) {
            if (instances[i].select === selectEl) {
                if (instances[i].isOpen) instances[i].close();
                if (instances[i].wrapper && instances[i].wrapper.parentNode) {
                    instances[i].wrapper.parentNode.removeChild(instances[i].wrapper);
                }
                instances.splice(i, 1);
                break;
            }
        }
        /* 2. Reset init flags so the constructor accepts this element */
        delete selectEl.dataset.sdInit;
        selectEl.style.display = '';
        selectEl.removeAttribute('tabindex');
        selectEl.removeAttribute('aria-hidden');
        /* 3. Remove any orphaned .sd-wrap that the loop might have missed */
        var next = selectEl.nextElementSibling;
        if (next && next.classList.contains('sd-wrap')) next.remove();
        /* 4. Create fresh instance */
        new SearchableDropdown(selectEl, opts);
    };
})();
