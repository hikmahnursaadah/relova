/*
 * Relova — navigasi “zipper”
 * Bekerja dengan atau tanpa GSAP. Kelas CSS menjadi jalur cadangan.
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
        document.querySelectorAll('[data-zipper]').forEach(function (zipper) {
            var navbar = zipper.closest('[data-navbar]') || document.querySelector('[data-navbar]') || document;
            var slider = zipper.querySelector('[data-zipper-slider]');
            var drawer = navbar.querySelector ? navbar.querySelector('[data-nav-drawer]') : document.querySelector('[data-nav-drawer]');
            if (!slider || !drawer) {
                return;
            }
            new ZipperNav(zipper, slider, drawer, navbar);
        });

        initClothingTags();
    }

    function ZipperNav(zipper, slider, drawer, navbar) {
        var self = this;
        var gsap = global.gsap || null;
        var links = Array.prototype.slice.call(drawer.querySelectorAll('[data-nav-links] a, .nav-link'));
        var tape = zipper.querySelector('.zipper__tape');
        var teethTop = zipper.querySelector('.zipper__teeth--top');
        var teethBottom = zipper.querySelector('.zipper__teeth--bottom');
        var hint = zipper.querySelector('[data-zipper-hint]');
        var openHint = zipper.querySelector('[data-zipper-open-hint]');
        var desktop = global.matchMedia ? global.matchMedia('(min-width: 1024px)') : { matches: false };
        var finePointer = !global.matchMedia || global.matchMedia('(hover: hover) and (pointer: fine)').matches;

        var open = drawer.getAttribute('aria-hidden') === 'false';
        var progress = open ? 1 : 0;
        var dragging = false;
        var moved = false;
        var startY = 0;
        var startProgress = 0;
        var suppressClick = false;
        var timeline = null;
        var resizeTimer = 0;
        var disposed = false;

        slider.style.touchAction = 'none';
        zipper.classList.add('has-hit-area');
        zipper.style.setProperty('--zipper-hit-area', '44px');

        this.setOpen = setOpen;
        this.close = function () { setOpen(false); };
        this.open = function () { setOpen(true); };

        buildTimeline();
        render(progress, true);
        applyState();

        /* --- geometri --- */

        function trackHeight() {
            var rect = zipper.getBoundingClientRect();
            return Math.max(140, Math.round(rect.height || 220));
        }

        function travel() {
            return Math.min(96, Math.max(48, trackHeight() * 0.34));
        }

        /* --- timeline GSAP --- */

        function buildTimeline() {
            if (!gsap) {
                return;
            }
            try {
                if (timeline) {
                    timeline.kill();
                    timeline = null;
                }
                gsap.set(drawer, { autoAlpha: 0, y: 12 });
                if (links.length) {
                    gsap.set(links, { autoAlpha: 0, y: 18 });
                }
                gsap.set(slider, { clearProps: 'transform' });
                if (tape) {
                    gsap.set(tape, { transformOrigin: 'top center', scaleY: 1 });
                }

                timeline = gsap.timeline({ paused: true, defaults: { ease: 'power3.out' } });
                timeline.to(slider, { y: travel(), rotation: 6, duration: 1 }, 0);
                if (tape) {
                    timeline.to(tape, { scaleY: 0.24, duration: 1 }, 0);
                }
                if (teethTop) {
                    timeline.to(teethTop, { opacity: 0.22, duration: 1 }, 0);
                }
                if (teethBottom) {
                    timeline.to(teethBottom, { opacity: 1, duration: 1 }, 0);
                }
                timeline.to(drawer, { autoAlpha: 1, y: 0, duration: 1 }, 0);
                if (links.length) {
                    timeline.to(links, {
                        autoAlpha: 1,
                        y: 0,
                        duration: 0.85,
                        stagger: 0.055
                    }, 0.08);
                }
                timeline.progress(progress).pause();
            } catch (error) {
                timeline = null;
            }
        }

        /* --- penyajian --- */

        function render(value, immediate) {
            progress = Math.max(0, Math.min(1, Number(value) || 0));
            zipper.style.setProperty('--zipper-progress', progress.toFixed(3));
            if (!timeline) {
                return;
            }
            try {
                if (immediate && gsap) {
                    gsap.set(slider, { y: progress * travel(), rotation: progress * 6 });
                    if (tape) {
                        gsap.set(tape, { scaleY: 1 - progress * 0.76 });
                    }
                    gsap.set(drawer, { autoAlpha: progress, y: (1 - progress) * 12 });
                    if (links.length) {
                        gsap.set(links, { autoAlpha: progress, y: (1 - progress) * 18 });
                    }
                } else {
                    timeline.progress(progress).pause();
                }
            } catch (error) {
                timeline = null;
            }
        }

        function applyState() {
            zipper.classList.toggle('is-open', open);
            zipper.classList.toggle('is-closed', !open);
            slider.classList.toggle('is-open', open);
            slider.classList.toggle('is-expanded', open);
            slider.setAttribute('aria-expanded', open ? 'true' : 'false');
            drawer.classList.toggle('is-open', open);
            drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
            if ('inert' in drawer) {
                drawer.inert = !open;
            }
            if (navbar.classList && typeof navbar.classList.toggle === 'function') {
                navbar.classList.toggle('has-nav-open', open);
            }
            if (hint) {
                hint.classList.toggle('is-hidden', open);
            }
            if (openHint) {
                openHint.classList.toggle('is-visible', open);
            }
        }

        function setOpen(next, options) {
            var target = !!next;
            if (target === open && !(options && options.force)) {
                return;
            }
            open = target;
            applyState();
            if (timeline && gsap && !(options && options.noAnimate)) {
                try {
                    timeline.tweenTo(open ? timeline.duration() : 0, {
                        duration: RW.prefersReducedMotion && RW.prefersReducedMotion() ? 0.01 : 0.7,
                        ease: 'power3.out',
                        onUpdate: function () {
                            progress = timeline.progress();
                            zipper.style.setProperty('--zipper-progress', progress.toFixed(3));
                        },
                        onComplete: function () {
                            progress = open ? 1 : 0;
                            render(progress, true);
                        }
                    });
                } catch (error) {
                    render(open ? 1 : 0, true);
                }
            } else {
                render(open ? 1 : 0, true);
            }
        }

        /* --- seret / sentuh --- */

        function onPointerDown(event) {
            if (event.pointerType === 'mouse' && typeof event.button === 'number' && event.button !== 0) {
                return;
            }
            dragging = true;
            moved = false;
            startY = event.clientY;
            startProgress = progress;
            suppressClick = false;
            slider.classList.add('is-dragging', 'is-grabbing');
            zipper.classList.add('is-dragging');
            try { slider.setPointerCapture(event.pointerId); } catch (error) { /* diabaikan */ }
        }

        function onPointerMove(event) {
            if (!dragging) {
                return;
            }
            var delta = event.clientY - startY;
            if (Math.abs(delta) > 4) {
                moved = true;
            }
            render(startProgress + delta / Math.max(120, trackHeight() * 0.55), false);
        }

        function onPointerUp(event) {
            if (!dragging) {
                return;
            }
            dragging = false;
            slider.classList.remove('is-dragging', 'is-grabbing');
            zipper.classList.remove('is-dragging');
            try { slider.releasePointerCapture(event.pointerId); } catch (error) { /* diabaikan */ }
            if (moved) {
                suppressClick = true;
                setOpen(progress >= 0.5);
            }
        }

        slider.addEventListener('pointerdown', onPointerDown);
        slider.addEventListener('pointermove', onPointerMove);
        slider.addEventListener('pointerup', onPointerUp);
        slider.addEventListener('pointercancel', onPointerUp);

        slider.addEventListener('click', function (event) {
            event.stopPropagation();
            if (suppressClick) {
                suppressClick = false;
                return;
            }
            setOpen(!open);
        });

        // seluruh lintasan dapat diketuk sebagai jalur cadangan
        zipper.addEventListener('click', function (event) {
            if (slider.contains(event.target)) {
                return;
            }
            setOpen(!open);
        });

        // gerakan pointer memberi isyarat visual halus tanpa mengubah status
        zipper.addEventListener('pointermove', function (event) {
            if (dragging) {
                return;
            }
            var rect = zipper.getBoundingClientRect();
            if (!rect.height) {
                return;
            }
            var ratio = Math.max(0, Math.min(1, (event.clientY - rect.top) / rect.height));
            zipper.style.setProperty('--zipper-pointer', ratio.toFixed(3));
            zipper.classList.add('is-pointing');
        });
        zipper.addEventListener('pointerleave', function () {
            zipper.classList.remove('is-pointing');
        });

        slider.addEventListener('keydown', function (event) {
            var key = event.key;
            if (key === 'Enter' || key === ' ' || key === 'Spacebar') {
                event.preventDefault();
                setOpen(!open);
            } else if (key === 'ArrowDown' || key === 'ArrowRight' || key === 'End') {
                event.preventDefault();
                setOpen(true);
            } else if (key === 'ArrowUp' || key === 'ArrowLeft' || key === 'Home') {
                event.preventDefault();
                setOpen(false);
            }
        });

        drawer.querySelectorAll('a[href]').forEach(function (link) {
            link.addEventListener('click', function () {
                setOpen(false);
            });
        });

        document.addEventListener('pointerdown', function (event) {
            if (!open) {
                return;
            }
            if (navbar.contains && navbar.contains(event.target)) {
                return;
            }
            setOpen(false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && open) {
                setOpen(false);
                try { slider.focus({ preventScroll: true }); } catch (error) { slider.focus(); }
            }
        });

        function onResize() {
            global.clearTimeout(resizeTimer);
            resizeTimer = global.setTimeout(function () {
                if (disposed) {
                    return;
                }
                buildTimeline();
                render(open ? 1 : 0, true);
            }, 140);
        }

        function onBreakpoint(event) {
            if (event.matches) {
                setOpen(false);
            }
        }

        global.addEventListener('resize', onResize, { passive: true });
        if (desktop.addEventListener) {
            desktop.addEventListener('change', onBreakpoint);
        } else if (desktop.addListener) {
            desktop.addListener(onBreakpoint);
        }

        global.addEventListener('pagehide', function () {
            disposed = true;
            global.clearTimeout(resizeTimer);
            if (timeline) {
                try { timeline.kill(); } catch (error) { /* diabaikan */ }
                timeline = null;
            }
        }, { once: true });

        if (finePointer) {
            slider.setAttribute('title', 'Tarik atau tekan untuk membuka menu');
        }

        self.destroy = function () {
            disposed = true;
            if (timeline) {
                timeline.kill();
            }
        };
    }

    /* ---------- label pakaian pada tautan “Jelajahi” ---------- */

    function initClothingTags() {
        var scopes = Array.prototype.slice.call(document.querySelectorAll('[data-nav-drawer]'));
        scopes.forEach(function (scope) {
            var trigger = scope.querySelector('[data-tag-trigger]');
            var tag = scope.querySelector('[data-clothing-tag]');
            if (!trigger || !tag) {
                return;
            }
            var coarse = !global.matchMedia || global.matchMedia('(pointer: coarse)').matches;
            var visible = false;

            function show(next) {
                visible = !!next;
                tag.classList.toggle('is-visible', visible);
                tag.classList.toggle('is-open', visible);
                trigger.classList.toggle('is-tag-active', visible);
                tag.setAttribute('aria-hidden', visible ? 'false' : 'true');
            }

            trigger.addEventListener('mouseenter', function () {
                if (!coarse) { show(true); }
            });
            trigger.addEventListener('mouseleave', function () {
                if (!coarse) { show(false); }
            });
            trigger.addEventListener('focus', function () {
                show(true);
            });
            trigger.addEventListener('blur', function () {
                show(false);
            });

            trigger.addEventListener('click', function (event) {
                if (coarse) {
                    event.preventDefault();
                    show(!visible);
                } else {
                    show(false);
                }
            });

            document.addEventListener('pointerdown', function (event) {
                if (visible && !trigger.contains(event.target) && !tag.contains(event.target)) {
                    show(false);
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && visible) {
                    show(false);
                }
            });
        });
    }
})(window);
