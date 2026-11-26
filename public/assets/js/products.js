/*
 * Relova — pengalaman produk, katalog, favorit, dan checkout.
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
    var formatNumber = function (value) {
        if (RW.formatNumber) { return RW.formatNumber(value); }
        return String(value).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };
    var formatRupiah = function (value) {
        if (RW.formatRupiah) { return RW.formatRupiah(value); }
        return 'Rp ' + formatNumber(value);
    };
    var parseRupiah = function (value) {
        if (RW.parseRupiah) { return RW.parseRupiah(value); }
        var digits = String(value == null ? '' : value).replace(/[^\d]/g, '');
        return digits ? parseInt(digits, 10) : 0;
    };

    ready(init);

    function init() {
        var root = document.documentElement;
        if (root.hasAttribute('data-rw-products-init')) {
            return;
        }
        root.setAttribute('data-rw-products-init', '1');

        initGallery();
        initFilterPanel();
        initSorting();
        initCategorySelect();
        initProductCards();
        initWishlist();
        initImagePreviews();
        initCheckout();
        initReportDialog();
    }

    /* ---------- galeri produk ---------- */

    function initGallery() {
        document.querySelectorAll('[data-gallery]').forEach(function (gallery) {
            var main = gallery.querySelector('[data-gallery-main]');
            var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('[data-gallery-thumb]'));
            if (!main || !thumbs.length) {
                return;
            }
            var current = Math.max(0, thumbs.findIndex(function (thumb) {
                return thumb.classList.contains('is-active');
            }));

            function select(index) {
                current = (index + thumbs.length) % thumbs.length;
                var thumb = thumbs[current];
                var inner = thumb.querySelector('img');
                var src = thumb.getAttribute('data-src') || (inner ? inner.getAttribute('src') : '');
                if (!src) {
                    return;
                }

                main.classList.add('is-loading');
                var preload = new Image();
                preload.onload = preload.onerror = function () {
                    main.src = src;
                    main.classList.remove('is-loading');
                };
                preload.src = src;

                thumbs.forEach(function (item, i) {
                    var active = i === current;
                    item.classList.toggle('is-active', active);
                    item.setAttribute('aria-pressed', active ? 'true' : 'false');
                    if (active) {
                        item.setAttribute('aria-current', 'true');
                    } else {
                        item.removeAttribute('aria-current');
                    }
                });
                main.classList.add('is-switching');
                global.setTimeout(function () { main.classList.remove('is-switching'); }, 320);
            }

            thumbs.forEach(function (thumb, index) {
                thumb.setAttribute('aria-pressed', index === current ? 'true' : 'false');
                thumb.addEventListener('click', function () { select(index); });
                thumb.addEventListener('keydown', function (event) {
                    if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                        event.preventDefault();
                        var next = (index + 1) % thumbs.length;
                        thumbs[next].focus();
                        select(next);
                    } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        var previous = (index - 1 + thumbs.length) % thumbs.length;
                        thumbs[previous].focus();
                        select(previous);
                    }
                });
            });
        });
    }

    /* ---------- panel filter katalog ---------- */

    function initFilterPanel() {
        var panel = document.querySelector('[data-filter-panel]');
        if (!panel) {
            return;
        }
        var backdrop = document.querySelector('[data-filter-backdrop]');
        var openers = Array.prototype.slice.call(document.querySelectorAll('[data-filter-open]'));
        var closers = Array.prototype.slice.call(panel.querySelectorAll('[data-filter-close]'));
        var desktop = global.matchMedia ? global.matchMedia('(min-width: 960px)') : { matches: false };
        var isOpen = false;

        function apply(open) {
            isOpen = !!open;
            panel.classList.toggle('is-open', isOpen);
            panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            if (backdrop) {
                backdrop.classList.toggle('is-open', isOpen);
                backdrop.classList.toggle('is-visible', isOpen);
            }
            openers.forEach(function (button) {
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                button.classList.toggle('is-active', isOpen);
            });
            document.body.classList.toggle('has-filter-open', isOpen);
            if (isOpen && !desktop.matches) {
                RW.lockScroll('filter');
            } else {
                RW.unlockScroll('filter');
            }
        }

        openers.forEach(function (button) {
            button.addEventListener('click', function () { apply(!isOpen); });
        });
        closers.forEach(function (button) {
            button.addEventListener('click', function () { apply(false); });
        });
        if (backdrop) {
            backdrop.addEventListener('click', function () { apply(false); });
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && isOpen) {
                apply(false);
                var opener = openers[0];
                if (opener && typeof opener.focus === 'function') { opener.focus(); }
            }
        });
        function onBreakpoint(event) {
            if (event.matches) { apply(false); }
        }
        if (desktop.addEventListener) {
            desktop.addEventListener('change', onBreakpoint);
        } else if (desktop.addListener) {
            desktop.addListener(onBreakpoint);
        }

        // tautan “Atur ulang” bawaan tetap bekerja, ini hanya penanda tambahan
        document.querySelectorAll('[data-filter-clear], [data-clear-filters]').forEach(function (clear) {
            clear.addEventListener('click', function () { apply(false); });
        });
        panel.querySelectorAll('.filter-actions a[href]').forEach(function (link) {
            link.addEventListener('click', function () { apply(false); });
        });

        if (desktop.matches) {
            panel.removeAttribute('aria-hidden');
        } else {
            panel.setAttribute('aria-hidden', 'true');
        }
    }

    /* ---------- urutkan ---------- */

    function initSorting() {
        document.querySelectorAll('[data-sort-select]').forEach(function (select) {
            select.addEventListener('change', function () {
                var form = select.closest('form') || document.querySelector('.filter-form');
                var hidden = form ? form.querySelector('input[name="urutkan"]') : null;
                if (hidden && form) {
                    hidden.value = select.value;
                    try {
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.submit();
                        }
                    } catch (error) {
                        form.submit();
                    }
                    return;
                }
                try {
                    var url = new URL(global.location.href);
                    url.searchParams.set('urutkan', select.value);
                    url.searchParams.delete('halaman');
                    global.location.assign(url.toString());
                } catch (error) {
                    global.location.href = global.location.pathname + '?urutkan=' + encodeURIComponent(select.value);
                }
            });
        });
    }

    /* ---------- pilihan kategori ---------- */

    function initCategorySelect() {
        document.querySelectorAll('[data-category-select]').forEach(function (select) {
            select.addEventListener('change', function () {
                var option = select.selectedOptions && select.selectedOptions[0];
                var productForm = select.closest('[data-product-form]');
                if (productForm) {
                    var typeSelect = productForm.querySelector('[data-type-select]');
                    var type = option ? option.getAttribute('data-type') : '';
                    if (typeSelect && type) {
                        typeSelect.value = type;
                        typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    return;
                }
                var form = select.closest('form') || document.querySelector('.filter-form');
                if (!form) {
                    return;
                }
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
    }

    /* ---------- kartu produk ---------- */

    function initProductCards() {
        document.querySelectorAll('[data-product-card]').forEach(function (card) {
            card.addEventListener('mouseenter', function () { card.classList.add('is-hovered'); });
            card.addEventListener('mouseleave', function () { card.classList.remove('is-hovered'); });
            card.addEventListener('focusin', function () { card.classList.add('is-focused'); });
            card.addEventListener('focusout', function (event) {
                if (!card.contains(event.relatedTarget)) {
                    card.classList.remove('is-focused');
                }
            });
        });
    }

    /* ---------- favorit ---------- */

    function initWishlist() {
        document.querySelectorAll('[data-wishlist-form]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.hasAttribute('data-rw-busy') || form.hasAttribute('data-rw-fallback')) {
                    return;
                }
                if (typeof global.fetch !== 'function' || typeof FormData === 'undefined') {
                    return; // kirim biasa
                }

                event.preventDefault();
                form.setAttribute('data-rw-busy', '1');
                var button = form.querySelector('[data-wishlist-button]');
                if (button) { button.classList.add('is-loading'); }

                var headers = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                };
                var token = RW.csrfToken ? RW.csrfToken() : '';
                if (token) { headers['X-CSRF-TOKEN'] = token; }

                global.fetch(form.getAttribute('action') || global.location.href, {
                    method: 'POST',
                    headers: headers,
                    body: new FormData(form),
                    credentials: 'same-origin'
                }).then(function (response) {
                    var contentType = response.headers ? (response.headers.get('content-type') || '') : '';
                    if (!response.ok || contentType.indexOf('application/json') < 0) {
                        throw new Error('Respons bukan JSON');
                    }
                    return response.json();
                }).then(function (data) {
                    if (!data || !data.ok) {
                        throw new Error((data && data.message) || 'Gagal menyimpan favorit');
                    }
                    var saved = !!data.saved;
                    if (button) {
                        button.classList.toggle('is-active', saved);
                        button.setAttribute('aria-label', saved ? 'Hapus dari favorit' : 'Simpan ke favorit');
                        var label = button.querySelector('span');
                        if (label) {
                            label.textContent = saved ? 'Tersimpan di favorit' : 'Simpan ke favorit';
                        }
                    }
                    var card = form.closest('[data-product-card]');
                    if (card) {
                        card.classList.toggle('is-wishlisted', saved);
                    }
                    if (typeof data.count === 'number' && RW.setCount) {
                        RW.setCount('wishlist', data.count);
                    }
                }).catch(function () {
                    // jalur aman: biarkan form melakukan submit normal
                    form.setAttribute('data-rw-fallback', '1');
                    try {
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.submit();
                        }
                    } catch (error) {
                        form.submit();
                    }
                }).then(function () {
                    form.removeAttribute('data-rw-busy');
                    if (button) { button.classList.remove('is-loading'); }
                });
            });
        });
    }

    /* ---------- pratinjau foto produk ---------- */

    function initImagePreviews() {
        var objectUrls = [];

        document.querySelectorAll('[data-image-input]').forEach(function (input) {
            var preview = document.querySelector('[data-image-preview]');
            var zone = document.querySelector('[data-upload-zone]');
            if (!preview) {
                return;
            }

            function render(files) {
                objectUrls.forEach(function (url) {
                    try { URL.revokeObjectURL(url); } catch (error) { /* diabaikan */ }
                });
                objectUrls.length = 0;
                preview.innerHTML = '';

                var images = Array.prototype.slice.call(files || []).filter(function (file) {
                    return file && /^image\//.test(file.type);
                }).slice(0, 5);

                images.forEach(function (file, index) {
                    var url;
                    try {
                        url = URL.createObjectURL(file);
                    } catch (error) {
                        return;
                    }
                    objectUrls.push(url);
                    var figure = document.createElement('figure');
                    figure.className = 'image-preview is-loading';
                    var img = document.createElement('img');
                    img.alt = 'Pratinjau foto ' + (index + 1);
                    img.src = url;
                    img.addEventListener('load', function () { figure.classList.remove('is-loading'); });
                    img.addEventListener('error', function () { figure.classList.remove('is-loading'); });
                    var caption = document.createElement('figcaption');
                    caption.textContent = file.name;
                    figure.appendChild(img);
                    figure.appendChild(caption);
                    preview.appendChild(figure);
                });
                preview.classList.toggle('is-hidden', images.length === 0);
                preview.classList.toggle('is-visible', images.length > 0);
            }

            input.addEventListener('change', function () { render(input.files); });

            if (zone && typeof DataTransfer !== 'undefined') {
                zone.addEventListener('dragover', function (event) {
                    event.preventDefault();
                    zone.classList.add('is-dragging');
                });
                zone.addEventListener('dragleave', function () {
                    zone.classList.remove('is-dragging');
                });
                zone.addEventListener('drop', function (event) {
                    event.preventDefault();
                    zone.classList.remove('is-dragging');
                    if (!event.dataTransfer || !event.dataTransfer.files) {
                        return;
                    }
                    try {
                        input.files = event.dataTransfer.files;
                        render(input.files);
                    } catch (error) { /* beberapa browser melarang penulisan files */ }
                });
            }
        });

        global.addEventListener('pagehide', function () {
            objectUrls.forEach(function (url) {
                try { URL.revokeObjectURL(url); } catch (error) { /* diabaikan */ }
            });
            objectUrls.length = 0;
        }, { once: true });
    }

    /* ---------- ringkasan checkout ---------- */

    function initCheckout() {
        document.querySelectorAll('[data-checkout-form]').forEach(function (form) {
            var inputs = Array.prototype.slice.call(form.querySelectorAll('[data-checkout-shipping]'));
            var shippingTotal = form.querySelector('[data-checkout-shipping-total]');
            var grandTotal = form.querySelector('[data-checkout-grand-total]');
            if (!inputs.length || !shippingTotal || !grandTotal) {
                return;
            }

            function baseSubtotal() {
                if (form.hasAttribute('data-subtotal')) {
                    return parseRupiah(form.getAttribute('data-subtotal'));
                }
                var lines = Array.prototype.slice.call(form.querySelectorAll('.checkout-review__item > strong'));
                if (lines.length) {
                    return lines.reduce(function (sum, node) { return sum + parseRupiah(node.textContent); }, 0);
                }
                var summary = form.querySelector('.checkout-summary__line strong');
                return summary ? parseRupiah(summary.textContent) : 0;
            }

            function valueOf(input) {
                if (input.tagName === 'SELECT' && input.selectedOptions && input.selectedOptions[0]) {
                    var option = input.selectedOptions[0];
                    if (option.hasAttribute('data-cost')) {
                        return parseRupiah(option.getAttribute('data-cost'));
                    }
                }
                if (input.hasAttribute('data-amount')) {
                    return parseRupiah(input.getAttribute('data-amount'));
                }
                return parseRupiah(input.value);
            }

            function update() {
                var shipping = inputs.reduce(function (sum, input) {
                    return sum + Math.max(0, valueOf(input));
                }, 0);
                shippingTotal.textContent = formatRupiah(shipping);
                grandTotal.textContent = formatRupiah(baseSubtotal() + shipping);
            }

            inputs.forEach(function (input) {
                input.addEventListener('input', update);
                input.addEventListener('change', update);
            });
            update();
        });
    }

    /* ---------- dialog laporan ---------- */

    function initReportDialog() {
        var dialog = document.querySelector('[data-report-dialog]');
        var openers = Array.prototype.slice.call(document.querySelectorAll('[data-report-open]'));
        if (!dialog || !openers.length) {
            return;
        }
        var closers = Array.prototype.slice.call(dialog.querySelectorAll('[data-report-close]'));
        var native = typeof dialog.showModal === 'function';

        function open() {
            if (native) {
                if (!dialog.open) { dialog.showModal(); }
            } else {
                dialog.hidden = false;
                dialog.classList.add('is-open');
                RW.lockScroll('report');
            }
            var first = dialog.querySelector('input, textarea, button');
            if (first && typeof first.focus === 'function') {
                first.focus();
            }
        }

        function close() {
            if (native) {
                if (dialog.open) { dialog.close(); }
            } else {
                dialog.hidden = true;
                dialog.classList.remove('is-open');
                RW.unlockScroll('report');
            }
        }

        openers.forEach(function (button) {
            button.addEventListener('click', open);
        });
        closers.forEach(function (button) {
            button.addEventListener('click', close);
        });
        dialog.addEventListener('cancel', function (event) {
            event.preventDefault();
            close();
        });
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) { close(); }
        });
        if (!native) {
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && dialog.classList.contains('is-open')) {
                    close();
                }
            });
        }
    }
})(window);
