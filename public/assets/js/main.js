/*
 * re:wear — utilitas antarmuka global
 * Dimuat lebih awal dari skrip lain tanpa modul.
 */
(function (global) {
    'use strict';

    var RW = global.Rewear = global.Rewear || {};

    /* ---------- pembantu bersama ---------- */

    if (typeof RW.ready !== 'function') {
        RW.ready = function (fn) {
            if (typeof fn !== 'function') {
                return;
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fn, { once: true });
            } else {
                fn();
            }
        };
    }

    RW.prefersReducedMotion = function () {
        return !!(global.matchMedia && global.matchMedia('(prefers-reduced-motion: reduce)').matches);
    };

    RW.csrfToken = function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? (meta.getAttribute('content') || '') : '';
    };

    RW.formatNumber = function (value) {
        var number = Number(value) || 0;
        try {
            return new Intl.NumberFormat('id-ID').format(number);
        } catch (error) {
            return String(number).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    };

    RW.formatRupiah = function (value) {
        return 'Rp ' + RW.formatNumber(value);
    };

    RW.parseRupiah = function (text) {
        if (typeof text === 'number') {
            return Math.max(0, Math.round(text));
        }
        var digits = String(text == null ? '' : text).replace(/[^\d]/g, '');
        return digits ? parseInt(digits, 10) : 0;
    };

    /* kunci scroll bersama supaya search/sidebar/filter tidak saling menimpa */
    var scrollLocks = new Set();
    var previousOverflow = '';

    RW.lockScroll = function (owner) {
        if (!owner || scrollLocks.has(owner)) {
            return;
        }
        if (!scrollLocks.size) {
            previousOverflow = document.body.style.overflow || '';
            document.body.style.overflow = 'hidden';
        }
        scrollLocks.add(owner);
        document.body.classList.add('is-scroll-locked');
    };

    RW.unlockScroll = function (owner) {
        if (!owner || !scrollLocks.has(owner)) {
            return;
        }
        scrollLocks.delete(owner);
        if (!scrollLocks.size) {
            document.body.style.overflow = previousOverflow;
            document.body.classList.remove('is-scroll-locked');
        }
    };

    RW.setCount = function (kind, value) {
        var root = document.documentElement;
        var count = Math.max(0, Number(value) || 0);
        root.querySelectorAll('[data-' + kind + '-count]').forEach(function (node) {
            node.textContent = String(count);
            node.classList.remove('is-bumped');
            // memicu animasi ulang tanpa mengubah markup
            void node.offsetWidth;
            node.classList.add('is-bumped');
            global.setTimeout(function () {
                node.classList.remove('is-bumped');
            }, 600);
        });
    };

    RW.ready(init);

    function init() {
        var root = document.documentElement;
        if (root.hasAttribute('data-rw-ui-init')) {
            return;
        }
        root.setAttribute('data-rw-ui-init', '1');

        initSearchOverlay();
        initToasts();
        initPasswordToggles();
        initDemoAccounts();
        initConfirmDialogs();
        initSidebars();
        initBackToTop();
        initNavPopovers();
        initCurrentYear();
    }

    /* ---------- pencarian ---------- */

    function initSearchOverlay() {
        var overlay = document.querySelector('[data-search-overlay]');
        var openers = Array.prototype.slice.call(document.querySelectorAll('[data-search-open]'));
        if (!overlay || (!openers.length && !document.querySelector('[data-search-close]'))) {
            return;
        }

        var input = overlay.querySelector('[data-search-input]');
        var panel = overlay.querySelector('.search-overlay__panel') || overlay;
        var closers = Array.prototype.slice.call(overlay.querySelectorAll('[data-search-close]'));
        var lastFocused = null;
        var isOpen = false;

        function focusable() {
            return Array.prototype.slice.call(
                panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])')
            ).filter(function (el) {
                return el.offsetParent !== null || el === document.activeElement;
            });
        }

        function open(event) {
            if (event && event.type === 'click') {
                event.preventDefault();
            }
            if (isOpen) {
                return;
            }
            isOpen = true;
            lastFocused = document.activeElement;
            overlay.setAttribute('aria-hidden', 'false');
            overlay.classList.add('is-open', 'is-active');
            document.body.classList.add('has-search-open');
            RW.lockScroll('search');
            global.setTimeout(function () {
                if (input) {
                    try { input.focus({ preventScroll: true }); } catch (error) { input.focus(); }
                    if (input.value) {
                        try { input.setSelectionRange(input.value.length, input.value.length); } catch (error) { /* diabaikan */ }
                    }
                }
            }, 40);
        }

        function close() {
            if (!isOpen) {
                return;
            }
            isOpen = false;
            overlay.setAttribute('aria-hidden', 'true');
            overlay.classList.remove('is-open', 'is-active');
            document.body.classList.remove('has-search-open');
            RW.unlockScroll('search');
            if (lastFocused && typeof lastFocused.focus === 'function') {
                try { lastFocused.focus({ preventScroll: true }); } catch (error) { lastFocused.focus(); }
            }
            lastFocused = null;
        }

        openers.forEach(function (button) {
            button.addEventListener('click', open);
        });
        closers.forEach(function (button) {
            button.addEventListener('click', close);
        });

        overlay.querySelectorAll('a[href]').forEach(function (link) {
            link.addEventListener('click', close);
        });

        document.addEventListener('keydown', function (event) {
            if (!isOpen) {
                return;
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                close();
                return;
            }
            if (event.key !== 'Tab') {
                return;
            }
            var items = focusable();
            if (!items.length) {
                return;
            }
            var first = items[0];
            var last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        RW.searchOverlay = { open: open, close: close };
    }

    /* ---------- notifikasi toast ---------- */

    function initToasts() {
        document.querySelectorAll('[data-toast]').forEach(function (toast, index) {
            var timer = null;

            function hide() {
                if (toast.hasAttribute('data-rw-closing')) {
                    return;
                }
                toast.setAttribute('data-rw-closing', '1');
                toast.classList.add('is-hidden');
                if (timer) {
                    global.clearTimeout(timer);
                }
                global.setTimeout(function () {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 340);
            }

            var closeButton = toast.querySelector('[data-toast-close]');
            if (closeButton) {
                closeButton.addEventListener('click', hide);
            }

            // pesan error sengaja tidak hilang dengan sendirinya
            if (!toast.classList.contains('toast--error')) {
                timer = global.setTimeout(hide, 6500 + index * 350);
            }
        });
    }

    /* ---------- kata sandi ---------- */

    function initPasswordToggles() {
        document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            var field = button.closest('.password-field') || button.parentElement;
            var input = field ? field.querySelector('input[type="password"], input[type="text"]') : null;
            if (!input) {
                return;
            }

            button.addEventListener('click', function () {
                var toText = input.getAttribute('type') === 'password';
                input.setAttribute('type', toText ? 'text' : 'password');
                button.classList.toggle('is-visible', toText);
                button.setAttribute('aria-label', toText ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                try { input.focus({ preventScroll: true }); } catch (error) { input.focus(); }
            });
        });
    }

    /* ---------- akun demo ---------- */

    function initDemoAccounts() {
        document.querySelectorAll('[data-demo-email], [data-demo-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var form = button.closest('form') || document.querySelector('.auth-form');
                if (!form) {
                    return;
                }
                var email = form.querySelector('input[name="email"]');
                var password = form.querySelector('input[name="password"]');
                if (email && button.hasAttribute('data-demo-email')) {
                    email.value = button.getAttribute('data-demo-email');
                    email.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (password && button.hasAttribute('data-demo-password')) {
                    password.value = button.getAttribute('data-demo-password');
                    password.dispatchEvent(new Event('input', { bubbles: true }));
                }
                button.classList.add('is-active');
                global.setTimeout(function () {
                    button.classList.remove('is-active');
                }, 900);
                var submit = form.querySelector('button[type="submit"]');
                if (submit && typeof submit.focus === 'function') {
                    try { submit.focus({ preventScroll: true }); } catch (error) { submit.focus(); }
                }
            });
        });
    }

    /* ---------- konfirmasi ---------- */

    function initConfirmDialogs() {
        var dialog = null;
        var pendingAction = null;
        var supportsDialog = !!(global.HTMLDialogElement && typeof global.HTMLDialogElement.prototype.showModal === 'function');

        function buildDialog() {
            if (dialog) {
                return dialog;
            }
            dialog = document.createElement(supportsDialog ? 'dialog' : 'div');
            dialog.className = 'rw-confirm-dialog' + (supportsDialog ? '' : ' rw-confirm-dialog--fallback');
            if (!supportsDialog) {
                dialog.setAttribute('role', 'dialog');
                dialog.setAttribute('aria-modal', 'true');
                dialog.hidden = true;
                dialog.style.display = 'none';
            }
            dialog.innerHTML =
                '<div class="rw-confirm-dialog__inner">' +
                    '<h2 class="rw-confirm-dialog__title">Konfirmasi tindakan</h2>' +
                    '<p class="rw-confirm-dialog__message"></p>' +
                    '<div class="rw-confirm-dialog__actions">' +
                        '<button type="button" class="button button--ghost" data-rw-confirm-cancel>Batal</button>' +
                        '<button type="button" class="button button--dark" data-rw-confirm-accept>Lanjutkan</button>' +
                    '</div>' +
                '</div>';

            if (supportsDialog) {
                dialog.style.cssText = 'border:0;padding:0;background:transparent;max-width:min(440px,calc(100% - 32px));color:#1d1c18;';
            } else {
                dialog.style.cssText = 'position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(29,28,24,.45);color:#1d1c18;';
            }
            var inner = dialog.querySelector('.rw-confirm-dialog__inner');
            inner.style.cssText = 'width:100%;max-width:440px;padding:28px;background:#f8f5ee;border:1px solid #d8d2c6;box-shadow:0 24px 60px rgba(29,28,24,.24);font-family:inherit;';
            dialog.querySelector('.rw-confirm-dialog__title').style.cssText = 'margin:0 0 12px;font-size:1.35rem;line-height:1.2;';
            dialog.querySelector('.rw-confirm-dialog__message').style.cssText = 'margin:0 0 24px;line-height:1.6;color:#5d5a52;';
            dialog.querySelector('.rw-confirm-dialog__actions').style.cssText = 'display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;';

            document.body.appendChild(dialog);

            dialog.querySelector('[data-rw-confirm-cancel]').addEventListener('click', function () {
                hideDialog();
            });
            dialog.querySelector('[data-rw-confirm-accept]').addEventListener('click', function () {
                var action = pendingAction;
                hideDialog();
                if (typeof action === 'function') {
                    action();
                }
            });
            dialog.addEventListener('cancel', function (event) {
                event.preventDefault();
                hideDialog();
            });
            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) {
                    hideDialog();
                }
            });
            return dialog;
        }

        function hideDialog() {
            pendingAction = null;
            if (!dialog) {
                return;
            }
            if (supportsDialog && dialog.open) {
                dialog.close();
            } else {
                dialog.hidden = true;
                dialog.style.display = 'none';
                dialog.classList.remove('is-open');
                RW.unlockScroll('confirm');
            }
        }

        function showDialog(message, onAccept) {
            buildDialog();
            pendingAction = onAccept;
            dialog.querySelector('.rw-confirm-dialog__message').textContent = message || 'Lanjutkan tindakan ini?';
            if (supportsDialog) {
                if (!dialog.open) {
                    dialog.showModal();
                }
            } else {
                dialog.hidden = false;
                dialog.style.display = 'flex';
                dialog.classList.add('is-open');
                RW.lockScroll('confirm');
            }
            var accept = dialog.querySelector('[data-rw-confirm-accept]');
            if (accept && typeof accept.focus === 'function') {
                accept.focus();
            }
        }

        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.getAttribute('data-rw-confirm-pending') === '1') {
                    return;
                }
                event.preventDefault();
                var message = form.getAttribute('data-confirm') || 'Lanjutkan tindakan ini?';

                function proceed() {
                    form.setAttribute('data-rw-confirm-pending', '1');
                    try {
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.submit();
                        }
                    } catch (error) {
                        form.submit();
                    }
                    global.setTimeout(function () {
                        form.removeAttribute('data-rw-confirm-pending');
                    }, 2000);
                }

                showDialog(message, proceed);
            });
        });

        RW.confirmDialog = { show: showDialog, hide: hideDialog };
    }

    /* ---------- sidebar dasbor ---------- */

    function initSidebars() {
        var sidebar = document.querySelector('[data-sidebar]');
        if (!sidebar) {
            return;
        }
        var backdrop = document.querySelector('[data-sidebar-backdrop]');
        var openers = Array.prototype.slice.call(document.querySelectorAll('[data-sidebar-open]'));
        var closers = Array.prototype.slice.call(document.querySelectorAll('[data-sidebar-close]'));
        var desktop = global.matchMedia ? global.matchMedia('(min-width: 1024px)') : { matches: false, addEventListener: null };
        var isOpen = false;

        function apply(open) {
            isOpen = !!open;
            sidebar.classList.toggle('is-open', isOpen);
            document.body.classList.toggle('has-sidebar-open', isOpen);
            if (backdrop) {
                backdrop.classList.toggle('is-open', isOpen);
                backdrop.classList.toggle('is-visible', isOpen);
            }
            openers.forEach(function (button) {
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                button.classList.toggle('is-active', isOpen);
            });
            if (desktop.matches) {
                sidebar.removeAttribute('aria-hidden');
            } else {
                sidebar.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            }
            if (isOpen) {
                RW.lockScroll('sidebar');
            } else {
                RW.unlockScroll('sidebar');
            }
        }

        function close() {
            apply(false);
        }

        openers.forEach(function (button) {
            button.addEventListener('click', function () {
                apply(!isOpen);
            });
        });
        closers.forEach(function (button) {
            button.addEventListener('click', close);
        });
        if (backdrop) {
            backdrop.addEventListener('click', close);
        }
        sidebar.querySelectorAll('a[href]').forEach(function (link) {
            link.addEventListener('click', close);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && isOpen) {
                close();
                var opener = openers[0];
                if (opener && typeof opener.focus === 'function') {
                    opener.focus();
                }
            }
        });

        function onBreakpoint(event) {
            if (event.matches) {
                close();
                sidebar.removeAttribute('aria-hidden');
            }
        }
        if (desktop.addEventListener) {
            desktop.addEventListener('change', onBreakpoint);
        } else if (desktop.addListener) {
            desktop.addListener(onBreakpoint);
        }

        apply(false);
        if (desktop.matches) {
            sidebar.removeAttribute('aria-hidden');
        }

        RW.sidebar = { open: function () { apply(true); }, close: close };
    }

    /* ---------- kembali ke atas ---------- */

    function initBackToTop() {
        var button = document.querySelector('[data-back-to-top]');
        if (!button) {
            return;
        }

        function update() {
            var visible = (global.scrollY || document.documentElement.scrollTop || 0) > 320;
            button.classList.toggle('is-visible', visible);
            button.classList.toggle('is-hidden', !visible);
        }

        button.addEventListener('click', function () {
            try {
                global.scrollTo({ top: 0, behavior: RW.prefersReducedMotion() ? 'auto' : 'smooth' });
            } catch (error) {
                global.scrollTo(0, 0);
            }
        });

        global.addEventListener('scroll', update, { passive: true });
        update();
    }

    /* ---------- popover navigasi ---------- */

    function initNavPopovers() {
        var popovers = Array.prototype.slice.call(document.querySelectorAll('.nav-popover'));
        if (popovers.length < 2) {
            return;
        }
        popovers.forEach(function (popover) {
            popover.addEventListener('toggle', function () {
                if (!popover.open) {
                    return;
                }
                popovers.forEach(function (other) {
                    if (other !== popover && other.open) {
                        other.open = false;
                    }
                });
            });
        });
    }

    /* ---------- tahun ---------- */

    function initCurrentYear() {
        var year = String(new Date().getFullYear());
        document.querySelectorAll('[data-year], [data-current-year]').forEach(function (node) {
            node.textContent = year;
        });
    }
})(window);
