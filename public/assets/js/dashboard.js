/*
 * re:wear — penyempurna interaksi dasbor pembeli, penjual, dan administrator.
 * Skrip ini sengaja ringan: pekerjaan utama sudah ditangani main.js.
 */
(function (global) {
    'use strict';

    var RW = global.Rewear = global.Rewear || {};
    var ready = typeof RW.ready === 'function'
        ? RW.ready
        : function (fn) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fn, { once: true });
            } else {
                fn();
            }
        };

    ready(init);

    function init() {
        var root = document.documentElement;
        if (root.hasAttribute('data-rw-dashboard-init')) {
            return;
        }
        root.setAttribute('data-rw-dashboard-init', '1');

        initAutoSubmitFilters();
        initTableScrollHints();
    }

    /* Pilihan filter langsung diterapkan tanpa menekan tombol. */
    function initAutoSubmitFilters() {
        var forms = document.querySelectorAll('.admin-filter-bar, form[data-filter-auto]');

        forms.forEach(function (form) {
            form.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', function () {
                    try {
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.submit();
                        }
                    } catch (error) {
                        form.submit();
                    }
                });
            });
        });
    }

    /* Beri petunjuk gulir pada tabel lebar tanpa mengubah markup server. */
    function initTableScrollHints() {
        document.querySelectorAll('.table-wrap').forEach(function (wrap) {
            if (wrap.scrollWidth <= wrap.clientWidth + 1) {
                return;
            }
            wrap.setAttribute('tabindex', '0');
            wrap.setAttribute('aria-label', 'Tabel dapat digulir secara mendatar');
            wrap.classList.add('is-scrollable');
        });
    }
})(window);
