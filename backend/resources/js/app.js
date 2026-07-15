import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import htmx from 'htmx.org';
import 'select2';
import $ from 'jquery';
import flatpickr from 'flatpickr';

Alpine.plugin(collapse);


document.addEventListener('alpine:init', function () {
    Alpine.data('commandPalette', function () {
        return {
            open: false,
            query: '',
            results: [],
            loading: false,
            selectedIndex: 0,
            searchTimer: null,
            init() {
                var self = this;
                this.$watch('open', val => {
                    if (val) this.$nextTick(() => { var inp = self.$refs.searchInput; if (inp) inp.focus(); });
                });
                // Direct window listeners as fallback (some events may not reach teleported x-show elements)
                window.addEventListener('open-palette', function () { self.onPaletteOpen(); });
                window.addEventListener('keydown', function (e) { self.onKeydown(e); });
            },
            onKeydown(event) {
                if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey) {
                    var tag = event.target?.tagName || '';
                    var isInput = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || event.target?.isContentEditable;
                    if (!isInput) {
                        event.preventDefault();
                        this.open = true; this.query = ''; this.results = []; this.selectedIndex = 0;
                        this.$nextTick(() => { var inp = this.$refs.searchInput; if (inp) inp.focus(); });
                        return;
                    }
                }
                if (event.key === 'Escape' && this.open) { this.close(); return; }
                if (!this.open) return;
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                } else if (event.key === 'Enter' && this.results[this.selectedIndex]) {
                    event.preventDefault();
                    window.location.href = this.results[this.selectedIndex].url;
                    this.close();
                }
            },
            search() {
                if (this.searchTimer) clearTimeout(this.searchTimer);
                this.searchTimer = setTimeout(() => {
                    if (this.query.length < 1) { this.results = []; return; }
                    this.loading = true;
                    var url = '/search?q=' + encodeURIComponent(this.query);
                    fetch(url).then(function(r) { return r.json(); })
                        .then(function(data) { this.results = data.results; this.selectedIndex = 0; }.bind(this))
                        .catch(function() { this.results = []; }.bind(this))
                        .finally(function() { this.loading = false; }.bind(this));
                }, 200);
            },
            close() { this.open = false; },
            onPaletteOpen() {
                this.open = true; this.query = ''; this.results = []; this.selectedIndex = 0;
                this.$nextTick(() => { var inp = this.$refs.searchInput; if (inp) inp.focus(); });
            },
        };
    });
    Alpine.data('fabMenu', function (role) {
        var base = [];
        if (role === 'instructor') {
            base = [
                { label: 'Create Student', url: '/students/create', icon: 'bi-person-plus' },
                { label: 'Add Company', url: '/companies/create', icon: 'bi-building-plus' },
                { label: 'New Evaluation', url: '/evaluations/create', icon: 'bi-star' },
                { label: 'Send Message', url: '/messages/create', icon: 'bi-chat-dots' },
            ];
        } else if (role === 'chairperson' || role === 'dean') {
            base = [
                { label: 'New Announcement', url: '/announcements/create', icon: 'bi-megaphone' },
                { label: 'Send Message', url: '/messages/create', icon: 'bi-chat-dots' },
            ];
        } else if (role === 'student') {
            base = [
                { label: 'Upload Document', url: '/student/documents', icon: 'bi-upload' },
                { label: 'Clock In / Out', url: '/attendance/check-in', icon: 'bi-clock' },
            ];
        }
        return {
            open: false,
            visible: true,
            actions: base,
            toggle() { this.open = !this.open; },
        };
    });
    Alpine.data('archiveModal', function (route, type) {
        return {
            open: false,
            recordId: null,
            recordName: '',
            archiveUrl: '',
            onOpen(detail) {
                this.recordId = detail.id;
                this.recordName = detail.name || type + ' #' + detail.id;
                this.archiveUrl = route.replace(':id', detail.id);
                this.open = true;
            }
        };
    });
    Alpine.data('readMore', function (options) {
        var opts = options || {};
        var max = opts.max || 200;
        return {
            expanded: false,
            get truncated() {
                var content = this.$el.textContent.trim();
                return !this.expanded && content.length > max ? content.substring(0, max) + '...' : content;
            },
            get needsTruncation() {
                return this.$el.textContent.trim().length > max;
            },
            toggle() {
                this.expanded = !this.expanded;
            },
        };
    });
    Alpine.data('passwordStrength', function () {
        return {
            show: false,
            showConfirm: false,
            password: '',
            confirmPassword: '',
            strength: 0,
            requirements: {
                minLength: false,
                hasUpper: false,
                hasLower: false,
                hasNumber: false,
                hasSpecial: false,
            },
            get match() {
                return this.confirmPassword.length > 0 && this.password === this.confirmPassword;
            },
            get score() {
                this.checkStrength();
                return this.strength;
            },
            get label() {
                var s = this.strength;
                if (s === 0 && this.password.length === 0) return { text: 'Choose a strong password', color: 'bg-gray-200' };
                if (this.password.length < 8) return { text: 'Too short', color: 'bg-red-500' };
                if (s <= 30) return { text: 'Weak', color: 'bg-red-500' };
                if (s <= 60) return { text: 'Medium', color: 'bg-orange-500' };
                if (s <= 80) return { text: 'Strong', color: 'bg-emerald-500' };
                return { text: 'Very strong', color: 'bg-emerald-600' };
            },
            checkStrength: function () {
                var p = this.password;
                var score = 0;
                this.requirements.minLength = p.length >= 8;
                this.requirements.hasUpper = /[A-Z]/.test(p);
                this.requirements.hasLower = /[a-z]/.test(p);
                this.requirements.hasNumber = /[0-9]/.test(p);
                this.requirements.hasSpecial = /[^A-Za-z0-9]/.test(p);
                if (this.requirements.minLength) score += 15;
                if (p.length >= 12) score += 10;
                if (this.requirements.hasLower) score += 20;
                if (this.requirements.hasUpper) score += 20;
                if (this.requirements.hasNumber) score += 20;
                if (this.requirements.hasSpecial) score += 15;
                this.strength = Math.min(100, score);
            },
        };
    });
});

window.htmx = htmx;
window.Alpine = Alpine;
window.$ = $;
window.jQuery = $;


function initDatePickers(container) {
    var root = container || document;
    root.querySelectorAll('[data-flatpickr]').forEach(function (el) {
        if (el._flatpickr) return;
        var config = {
            dateFormat: 'Y-m-d',
            altFormat: 'M j, Y',
            altInput: true,
            allowInput: true,
        };
        if (el.dataset.flatpickr === 'datetime') {
            config.enableTime = true;
            config.dateFormat = 'Y-m-d H:i';
            config.altFormat = 'M j, Y h:i K';
            config.time_24hr = false;
        }
        if (el.dataset.flatpickr === 'time') {
            config.enableTime = true;
            config.noCalendar = true;
            config.dateFormat = 'H:i';
            config.altFormat = 'h:i K';
            config.time_24hr = false;
        }
        try {
            flatpickr(el, config);
        } catch (e) {
            // flatpickr may fail on dynamically swapped inputs; ignore
        }
    });
}

function initClockpickers(container) {
    import('clockpicker/dist/jquery-clockpicker.min.js').then(function () {
        var root = container || document;
        $(root).find('[data-clockpicker]').each(function () {
            if ($(this).data('clockpicker')) return;
            $(this).clockpicker({
                autoclose: true,
                donetext: 'Done',
                format: 'HH:mm',
            });
        });
    }).catch(function () {
        // clockpicker failed to load, time inputs will use native browser picker
    });
}

function initSelect2(container) {
    $(container || document).find('select').not('.no-select2').each(function () {
        var $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) return;

        var optsCount = $el.find('option').length;
        var minimalSearch = optsCount >= 8 ? 0 : 999;
        var placeholder = $el.data('placeholder') || $el.find('option:first').text() || 'Select...';
        if (placeholder === '' || $el.find('option[value=""]').length) {
            placeholder = $el.data('placeholder') || 'Select...';
        }

        $el.select2({
            width: '100%',
            placeholder: placeholder,
            allowClear: $el.data('allow-clear') !== false,
            minimumResultsForSearch: minimalSearch,
            dropdownAutoWidth: true,
            language: {
                noResults: function () { return 'No results found'; }
            },
        });
    });
}

function showToast(message, type, duration, undoKey) {
    type = type || 'success';
    duration = duration || 5000;

    var container = document.getElementById('toast-container');
    if (!container) return;

    var colors = {
        success: 'bg-emerald-600',
        error: 'bg-red-600',
        warning: 'bg-amber-600',
        info: 'bg-blue-600',
    };
    var toast = document.createElement('div');
    toast.className = 'px-4 py-3 rounded-lg shadow-lg text-white min-w-[280px] max-w-md animate-slide-in pointer-events-auto ' + (colors[type] || colors.success);
    var undoBtn = undoKey
        ? '<button onclick="fetch(\'/undo/' + undoKey + '\', {method: \'POST\', headers: {\'X-CSRF-TOKEN\': document.querySelector(\'meta[name=\\\'csrf-token\\\']\').getAttribute(\'content\')}}).then(function(r){ if(r.redirected) window.location.href=r.url; else window.location.reload(); });this.closest(\'.animate-slide-in\').remove()" class="ml-3 px-2 py-1 rounded bg-white/20 text-white text-xs font-semibold hover:bg-white/30 transition-colors">Undo</button>'
        : '';
    toast.innerHTML = '<div class="flex items-start gap-3"><div class="flex-1 text-sm font-medium">' + message + undoBtn + '</div><button onclick="this.closest(\'.animate-slide-in\').remove()" class="text-white/70 hover:text-white transition-opacity"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>';
    container.appendChild(toast);
    setTimeout(function () {
        if (toast.parentNode) toast.remove();
    }, duration);
}

window.showToast = showToast;

function hideLoadingOverlay() {
    var overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    Alpine.start();
    initDatePickers();
    initClockpickers();
    try { initSelect2(); } catch (e) { /* Select2 may fail on dynamic selects; ignore */ }

    var overlay = document.getElementById('loadingOverlay');

    function isHtmxForm(form) {
        return form.hasAttribute('hx-post')
            || form.hasAttribute('hx-put')
            || form.hasAttribute('hx-patch')
            || form.hasAttribute('hx-delete')
            || form.hasAttribute('hx-get');
    }

    function showOverlayWithSafety() {
        if (!overlay) return;
        overlay.style.display = 'flex';
        // Safety timeout: auto-hide after 10s to prevent permanent stuck state
        setTimeout(hideLoadingOverlay, 10000);
    }

    document.querySelectorAll('form').forEach(function (form) {
        if (form.getAttribute('method') === 'GET') return;
        if (form.closest('[x-data]')) return;
        form.addEventListener('submit', function () {
            if (isHtmxForm(form)) return;
            showOverlayWithSafety();
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hideLoadingOverlay();
    });

    hideLoadingOverlay();
});

window.addEventListener('pageshow', hideLoadingOverlay);
window.addEventListener('load', hideLoadingOverlay);

// Hide overlay on HTMX events so it never stays stuck
document.body.addEventListener('htmx:sendError', hideLoadingOverlay);
document.body.addEventListener('htmx:beforeSwap', hideLoadingOverlay);
window.addEventListener('popstate', hideLoadingOverlay);

document.body.addEventListener('htmx:afterSwap', function (evt) {
    initDatePickers(evt.detail.target);
    initClockpickers(evt.detail.target);
    initSelect2(evt.detail.target);
    if (window.Alpine && evt.detail && evt.detail.target) {
        Alpine.initTree(evt.detail.target);
    }

    var countEl = document.getElementById('unread-count-val');
    if (countEl) {
        var count = parseInt(countEl.textContent || '0');
        document.querySelectorAll('.unread-badge').forEach(function (el) {
            if (count > 0) {
                el.style.display = '';
                el.textContent = count > 99 ? '99+' : count;
            } else {
                el.style.display = 'none';
            }
        });
    }

    if (evt.detail.target && evt.detail.target.id === 'message-conversation-panel') {
        setTimeout(function () {
            var viewport = document.getElementById('threadMessageViewport');
            if (viewport) viewport.scrollTop = viewport.scrollHeight;
        }, 50);
    }
});

document.body.addEventListener('htmx:afterRequest', function (evt) {
    hideLoadingOverlay();
    var el = evt.detail.elt;
    if (el && el.hasAttribute && el.hasAttribute('hx-post') && el.getAttribute('hx-target') === '#review-panel-content') {
        window.dispatchEvent(new CustomEvent('review-panel-loaded'));
    }
    if (evt.detail.successful) {
        var xhr = evt.detail.xhr;
        var toastMsg = xhr.getResponseHeader('X-Toast-Message');
        if (toastMsg) {
            var toastType = xhr.getResponseHeader('X-Toast-Type') || 'success';
            showToast(toastMsg, toastType);
        }
    }
});
document.body.addEventListener('htmx:sendError', function () {
    hideLoadingOverlay();
    if (typeof window.showToast === 'function') {
        window.showToast('Network error. Please check your connection and try again.', 'error', 5000);
    }
});
document.body.addEventListener('htmx:beforeRequest', function (evt) {
    hideLoadingOverlay();
    var el = evt.detail.elt;
    if (el && el.hasAttribute && el.hasAttribute('hx-post') && el.getAttribute('hx-target') === '#review-panel-content') {
        window.dispatchEvent(new CustomEvent('open-review-panel'));
    }
    var form = el && el.closest('form');
    if (form && form.hasAttribute && form.hasAttribute('hx-boosted')) {
        // HTMX boosted forms handling
    }
});

document.addEventListener('focusout', function (e) {
    var el = e.target;
    if (el.tagName !== 'INPUT' && el.tagName !== 'SELECT' && el.tagName !== 'TEXTAREA') return;
    if (!el.willValidate || el.validity.valid) return;
    if (el.validity.valueMissing) return;
    el.reportValidity();
});

// ── Input masks ──
window.inputMasks = {
    phone: function (value) {
        var digits = value.replace(/[^0-9]/g, '');
        if (digits.startsWith('63')) digits = digits.substring(2);
        digits = digits.substring(0, 10);
        var parts = [];
        if (digits.length > 0) parts.push(digits.substring(0, 3));
        if (digits.length > 3) parts.push(digits.substring(3, 6));
        if (digits.length > 6) parts.push(digits.substring(6, 10));
        return '+63 ' + parts.join(' ');
    },
    studentNumber: function (value) {
        return value.replace(/[^0-9]/g, '').substring(0, 8);
    },
};

document.addEventListener('input', function (e) {
    var el = e.target;
    var mask = el.getAttribute('x-inputmask');
    if (!mask || !window.inputMasks[mask]) return;
    var start = el.selectionStart;
    var end = el.selectionEnd;
    var prevLen = el.value.length;
    el.value = window.inputMasks[mask](el.value);
    var newLen = el.value.length;
    if (start < prevLen) {
        el.setSelectionRange(start + (newLen - prevLen), end + (newLen - prevLen));
    }
});

// ── Scroll to first validation error ──
function scrollToFirstError() {
    var err = document.querySelector('.is-invalid, [aria-invalid="true"], .peer-invalid');
    if (!err) {
        err = document.querySelector('.text-red-600, .text-rose-600');
        if (err) err = err.closest('.form-group, [class*="mb-"], .space-y, div.float-input') || err;
    }
    if (err) setTimeout(function () { err.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 150);
}
document.addEventListener('DOMContentLoaded', scrollToFirstError);
document.body.addEventListener('htmx:afterSwap', function () { setTimeout(scrollToFirstError, 200); });
