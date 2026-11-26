/*
 * Relova — gerak editorial berbasis GSAP / ScrollTrigger.
 * Jika pustaka tidak ada atau pengguna memilih reduced motion, konten tetap tampil.
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
        if (root.hasAttribute('data-rw-anim-init')) {
            return;
        }
        root.setAttribute('data-rw-anim-init', '1');

        var gsap = global.gsap || null;
        var ScrollTrigger = global.ScrollTrigger || null;
        var reduced = !!(RW.prefersReducedMotion && RW.prefersReducedMotion());
        var motion = !!(gsap && ScrollTrigger && !reduced);
        var tweens = [];
        var triggers = [];
        var cleanups = [];

        if (gsap && ScrollTrigger) {
            try { gsap.registerPlugin(ScrollTrigger); } catch (error) { /* diabaikan */ }
        }

        try {
            initHeroReveal(gsap, ScrollTrigger, motion, tweens, triggers);
            initSectionReveals(gsap, ScrollTrigger, motion, tweens, triggers, cleanups);
            initParallax(gsap, ScrollTrigger, motion, tweens, triggers);
            initCounters(gsap, ScrollTrigger, motion, tweens, triggers);
            initScrollCue(cleanups);

            if (motion && ScrollTrigger.refresh) {
                global.setTimeout(function () {
                    try { ScrollTrigger.refresh(); } catch (error) { /* diabaikan */ }
                }, 120);
            }
        } catch (error) {
            // kegagalan gerak tidak boleh menyembunyikan konten
            showEverything();
        }

        global.addEventListener('pagehide', function () {
            cleanups.forEach(function (fn) {
                try { fn(); } catch (error) { /* diabaikan */ }
            });
            triggers.forEach(function (trigger) {
                try { if (trigger && trigger.kill) { trigger.kill(); } } catch (error) { /* diabaikan */ }
            });
            tweens.forEach(function (tween) {
                try { if (tween && tween.kill) { tween.kill(); } } catch (error) { /* diabaikan */ }
            });
            try { if (ScrollTrigger && ScrollTrigger.getAll) { ScrollTrigger.getAll().forEach(function (t) { t.kill(); }); } } catch (error) { /* diabaikan */ }
        }, { once: true });

        function showEverything() {
            document.querySelectorAll('[data-reveal], [data-reveal-card], [data-hero-reveal], [data-hero-title]').forEach(function (el) {
                el.classList.add('is-visible', 'is-revealed');
                try { el.style.removeProperty('opacity'); } catch (error) { /* diabaikan */ }
                try { el.style.removeProperty('visibility'); } catch (error) { /* diabaikan */ }
                try { el.style.removeProperty('transform'); } catch (error) { /* diabaikan */ }
                try { el.style.removeProperty('clip-path'); } catch (error) { /* diabaikan */ }
            });
            document.querySelectorAll('[data-count]').forEach(function (el) {
                var target = parseInt(el.getAttribute('data-count'), 10);
                if (isFinite(target)) {
                    el.textContent = RW.formatNumber ? RW.formatNumber(target) : String(target);
                }
            });
        }
    }

    /* ---------- judul hero ---------- */

    function initHeroReveal(gsap, ScrollTrigger, motion, tweens, triggers) {
        var hero = document.querySelector('[data-hero]');
        var title = document.querySelector('[data-hero-title]');
        var reveals = Array.prototype.slice.call(document.querySelectorAll('[data-hero-reveal]'));
        var all = [];

        if (title) { all.push(title); }
        reveals.forEach(function (el) { all.push(el); });

        // penanda ini dipakai hero.js agar tidak menganimasikan dua kali
        all.forEach(function (el) { el.setAttribute('data-rw-hero-animated', '1'); });

        if (!all.length) {
            return;
        }
        if (!motion) {
            all.forEach(function (el) { el.classList.add('is-visible', 'is-revealed'); });
            return;
        }

        var titleLines = title ? Array.prototype.slice.call(title.children).filter(function (element) {
            return element.tagName === 'SPAN' && element.textContent.trim().length > 0;
        }) : [];
        var introTargets = titleLines.length ? titleLines : (title ? [title] : []);
        var targets = introTargets.concat(reveals);

        try {
            gsap.set(targets, { autoAlpha: 0, y: 26, clipPath: 'inset(0 0 12% 0)' });
            var timeline = gsap.timeline({ defaults: { ease: 'power4.out' } });
            timeline.to(introTargets, {
                autoAlpha: 1,
                y: 0,
                clipPath: 'inset(0 0 0% 0)',
                duration: 1.15,
                stagger: 0.12
            }, 0);
            if (reveals.length) {
                timeline.to(reveals, {
                    autoAlpha: 1,
                    y: 0,
                    clipPath: 'inset(0 0 0% 0)',
                    duration: 1,
                    stagger: 0.1
                }, 0.24);
            }
            timeline.eventCallback('onComplete', function () {
                all.forEach(function (el) { el.classList.add('is-visible', 'is-revealed'); });
            });
            tweens.push(timeline);
        } catch (error) {
            all.forEach(function (el) { el.classList.add('is-visible', 'is-revealed'); });
        }
    }

    /* ---------- reveal bagian ---------- */

    function initSectionReveals(gsap, ScrollTrigger, motion, tweens, triggers, cleanups) {
        var nodes = Array.prototype.slice.call(
            document.querySelectorAll('[data-reveal], [data-reveal-card]')
        ).filter(function (el) {
            return !el.closest('[data-hero]') && !el.hasAttribute('data-rw-reveal');
        });

        nodes.forEach(function (el) { el.setAttribute('data-rw-reveal', '1'); });

        if (!nodes.length) {
            return;
        }
        if (!motion) {
            nodes.forEach(function (el) { el.classList.add('is-visible', 'is-revealed'); });
            return;
        }

        var groups = new Map();
        nodes.forEach(function (el) {
            var section = el.closest('[data-reveal-section]') || el.closest('section') || document.body;
            if (!groups.has(section)) {
                groups.set(section, []);
            }
            groups.get(section).push(el);
        });

        groups.forEach(function (items) {
            var cards = items.filter(function (el) { return el.hasAttribute('data-reveal-card'); });
            var plain = items.filter(function (el) { return !el.hasAttribute('data-reveal-card'); });

            if (cards.length) {
                setupGroup(cards, true);
            }
            if (plain.length) {
                setupGroup(plain, false);
            }
        });

        function setupGroup(items, isCard) {
            var from = isCard
                ? { y: 58, autoAlpha: 0, clipPath: 'inset(0 0 16% 0 round 4px)' }
                : { y: 24, autoAlpha: 0, clipPath: 'inset(0 0 8% 0)' };
            var to = {
                y: 0,
                autoAlpha: 1,
                clipPath: 'inset(0 0 0% 0 round 4px)',
                duration: isCard ? 1.05 : 0.9,
                ease: 'power3.out',
                stagger: { each: isCard ? 0.085 : 0.065, from: 'start' },
                overwrite: 'auto',
                onComplete: function () {
                    items.forEach(function (el) { el.classList.add('is-visible', 'is-revealed'); });
                }
            };

            try {
                gsap.set(items, from);
                var batch = ScrollTrigger.batch(items, {
                    start: 'top 88%',
                    once: true,
                    onEnter: function (elements) {
                        gsap.to(elements, to);
                    }
                });
                if (Array.isArray(batch)) {
                    batch.forEach(function (trigger) { triggers.push(trigger); });
                } else if (batch) {
                    triggers.push(batch);
                }
                cleanups.push(function () {
                    items.forEach(function (el) {
                        try {
                            el.style.removeProperty('opacity');
                            el.style.removeProperty('visibility');
                            el.style.removeProperty('transform');
                            el.style.removeProperty('clip-path');
                        } catch (error) { /* diabaikan */ }
                    });
                });
            } catch (error) {
                items.forEach(function (el) { el.classList.add('is-visible', 'is-revealed'); });
            }
        }
    }

    /* ---------- parallax ---------- */

    function initParallax(gsap, ScrollTrigger, motion, tweens, triggers) {
        if (!motion) {
            return;
        }
        var selectors = [
            '.hero__arch',
            '.hero__visual-tag',
            '.editorial-section__media img',
            '.about-hero__media img',
            '.about-process__media img',
            '.sell-cta__visual img',
            '.studio-stage__floor'
        ];

        selectors.forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (el) {
                if (el.hasAttribute('data-rw-parallax')) {
                    return;
                }
                el.setAttribute('data-rw-parallax', '1');
                var amount = selector.indexOf('hero__') === 0 ? 12 : -8;
                try {
                    var tween = gsap.to(el, {
                        yPercent: amount,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: el.parentElement || el,
                            start: 'top bottom',
                            end: 'bottom top',
                            scrub: 0.6
                        }
                    });
                    tweens.push(tween);
                    if (tween.scrollTrigger) {
                        triggers.push(tween.scrollTrigger);
                    }
                } catch (error) { /* diabaikan */ }
            });
        });
    }

    /* ---------- angka ---------- */

    function initCounters(gsap, ScrollTrigger, motion, tweens, triggers) {
        document.querySelectorAll('[data-count]').forEach(function (el) {
            var target = parseInt(el.getAttribute('data-count'), 10);
            if (!isFinite(target)) {
                return;
            }
            var format = function (value) {
                return RW.formatNumber ? RW.formatNumber(value) : String(value);
            };
            if (!motion) {
                el.textContent = format(target);
                return;
            }
            var state = { value: 0 };
            try {
                var tween = gsap.to(state, {
                    value: target,
                    duration: 1.5,
                    ease: 'power2.out',
                    scrollTrigger: { trigger: el, start: 'top 92%', once: true },
                    onUpdate: function () {
                        el.textContent = format(Math.min(target, Math.round(state.value)));
                    },
                    onComplete: function () {
                        el.textContent = format(target);
                    }
                });
                tweens.push(tween);
                if (tween.scrollTrigger) {
                    triggers.push(tween.scrollTrigger);
                }
            } catch (error) {
                el.textContent = format(target);
            }
        });
    }

    /* ---------- penanda gulir ---------- */

    function initScrollCue(cleanups) {
        var cue = document.querySelector('[data-scroll-cue]');
        if (!cue) {
            return;
        }
        function onClick(event) {
            var href = cue.getAttribute('href') || '';
            if (href.charAt(0) !== '#') {
                return;
            }
            var target = document.querySelector(href);
            if (!target) {
                return;
            }
            event.preventDefault();
            try {
                target.scrollIntoView({ behavior: RW.prefersReducedMotion && RW.prefersReducedMotion() ? 'auto' : 'smooth', block: 'start' });
            } catch (error) {
                target.scrollIntoView();
            }
        }
        cue.addEventListener('click', onClick);
        cleanups.push(function () { cue.removeEventListener('click', onClick); });
    }
})(window);
