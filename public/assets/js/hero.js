/*
 * re:wear — manekin 3D prosedural
 * Skrip klasik ini memuat modul Three.js lokal secara dinamis. Jika WebGL tidak
 * tersedia, ilustrasi SVG bawaan tetap ditampilkan tanpa teks pengganti Inggris.
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

    var OUTFIT_NAMES = ['Kasual', 'Vintage', 'Streetwear', 'Minimalis', 'Outerwear'];
    var ICON_PAUSE = '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h3.2v14H7zm6.8 0H17v14h-3.2z" fill="currentColor"/></svg>';
    var ICON_PLAY = '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l11-6.5z" fill="currentColor"/></svg>';

    var threePromise = null;
    var mounts = [];

    function init() {
        var hosts = Array.prototype.slice.call(document.querySelectorAll('[data-mannequin]'));
        if (!hosts.length) {
            return;
        }

        hosts.forEach(function (host) {
            try {
                mounts.push(mount(host));
            } catch (error) {
                showFallback(host, controllerFor(host), 'Ilustrasi ditampilkan karena tampilan 3D tidak tersedia.');
            }
        });

        global.addEventListener('pagehide', function () {
            mounts.forEach(function (mounted) {
                try { mounted.destroy(); } catch (error) { /* diabaikan */ }
            });
            mounts.length = 0;
        }, { once: true });
    }

    function mount(host) {
        var controller = new OutfitController(host);
        var renderer = new MannequinRenderer(host, controller);
        controller.setRenderer(renderer);

        return {
            destroy: function () {
                try { renderer.destroy(); } catch (error) { /* diabaikan */ }
                try { controller.destroy(); } catch (error) { /* diabaikan */ }
            }
        };
    }

    function controllerFor(host) {
        return {
            disable: function () {
                var button = host.parentElement
                    ? host.parentElement.querySelector('[data-outfit-play], [data-studio-autoplay]')
                    : null;
                if (button) {
                    button.disabled = true;
                }
            }
        };
    }

    function reducedMotion() {
        return !!(RW.prefersReducedMotion && RW.prefersReducedMotion());
    }

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function now() {
        return global.performance && typeof global.performance.now === 'function'
            ? global.performance.now()
            : Date.now();
    }

    /* ---------- pengatur tampilan outfit ---------- */

    function OutfitController(host) {
        this.host = host;
        this.mode = host.getAttribute('data-mode') === 'studio' ? 'studio' : 'hero';
        this.index = 0;
        this.playing = !reducedMotion();
        this.ready = false;
        this.visible = true;
        this.elapsed = 0;
        this.lastTick = 0;
        this.renderer = null;
        this.destroyed = false;
        this.listeners = [];
        this.buttons = [];
        this.playButton = null;
        this.icon = null;
        this.label = null;
        this.counter = null;
        this.name = null;
        this.progress = null;
        this.interval = this.mode === 'studio' ? 5000 : 4200;

        this.collect();
        this.bind();
        this.render();
    }

    OutfitController.prototype.on = function (target, type, handler, options) {
        if (!target || typeof target.addEventListener !== 'function') {
            return;
        }
        target.addEventListener(type, handler, options);
        this.listeners.push([target, type, handler, options]);
    };

    OutfitController.prototype.collect = function () {
        var stage = this.host.parentElement || document;
        if (this.mode === 'studio') {
            this.buttons = Array.prototype.slice.call(document.querySelectorAll('[data-studio-outfit]'));
            this.playButton = document.querySelector('[data-studio-autoplay]');
            if (this.playButton) {
                this.icon = this.playButton.querySelector('.icon') || this.playButton.firstElementChild;
                this.label = this.playButton.querySelector('span');
            }
            return;
        }

        var panel = stage.querySelector('[data-outfit-panel]') || document.querySelector('[data-outfit-panel]');
        this.buttons = panel
            ? Array.prototype.slice.call(panel.querySelectorAll('[data-outfit-index]'))
            : Array.prototype.slice.call(document.querySelectorAll('[data-outfit-index]'));
        this.counter = panel ? panel.querySelector('[data-outfit-counter]') : null;
        this.name = panel ? panel.querySelector('[data-outfit-name]') : null;
        this.playButton = panel ? panel.querySelector('[data-outfit-play]') : null;
        this.icon = panel ? panel.querySelector('[data-outfit-play-icon]') : null;
        this.progress = panel ? panel.querySelector('[data-outfit-progress]') : null;
    };

    OutfitController.prototype.bind = function () {
        var self = this;

        this.buttons.forEach(function (button, index) {
            self.on(button, 'click', function () {
                self.select(index, true);
            });
            self.on(button, 'keydown', function (event) {
                var step = 0;
                if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                    step = 1;
                } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                    step = -1;
                }
                if (!step) {
                    return;
                }
                event.preventDefault();
                var next = (index + step + self.buttons.length) % self.buttons.length;
                self.select(next, true);
                if (self.buttons[next] && typeof self.buttons[next].focus === 'function') {
                    self.buttons[next].focus();
                }
            });
        });

        if (this.playButton) {
            this.on(this.playButton, 'click', function () {
                self.toggle();
            });
        }
    };

    OutfitController.prototype.render = function () {
        var self = this;
        var active = this.index;

        this.buttons.forEach(function (button, index) {
            var isActive = index === active;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            if (isActive) {
                button.setAttribute('aria-current', 'true');
            } else {
                button.removeAttribute('aria-current');
            }
        });

        if (this.counter) {
            this.counter.textContent = pad(active + 1) + ' / ' + pad(OUTFIT_NAMES.length);
        }
        if (this.name) {
            this.name.textContent = OUTFIT_NAMES[active] || OUTFIT_NAMES[0];
        }

        if (this.playButton && !this.playButton.disabled) {
            if (this.mode === 'studio') {
                this.playButton.setAttribute('aria-pressed', this.playing ? 'true' : 'false');
                this.setStudioIcon(this.playing ? ICON_PAUSE : ICON_PLAY);
                if (this.label) {
                    this.label.textContent = this.playing ? 'Hentikan otomatis' : 'Putar otomatis';
                }
            } else {
                this.playButton.setAttribute(
                    'aria-label',
                    this.playing ? 'Hentikan pergantian outfit otomatis' : 'Putar pergantian outfit otomatis'
                );
                if (this.icon) {
                    this.icon.innerHTML = this.playing ? ICON_PAUSE : ICON_PLAY;
                }
            }
        }

        if (this.progress) {
            var percent = this.playing && this.ready
                ? clamp((this.elapsed / this.interval) * 100, 0, 100)
                : 0;
            this.progress.style.width = percent.toFixed(1) + '%';
        }
    };

    OutfitController.prototype.setStudioIcon = function (markup) {
        if (!this.playButton) {
            return;
        }
        if (!this.icon || !this.icon.parentNode) {
            this.icon = this.playButton.querySelector('.icon') || this.playButton.firstElementChild;
        }
        if (!this.icon || !this.icon.parentNode || typeof document.createRange !== 'function') {
            return;
        }
        var fragment = document.createRange().createContextualFragment(markup);
        var replacement = fragment.firstElementChild;
        if (replacement) {
            this.icon.parentNode.replaceChild(replacement, this.icon);
            this.icon = replacement;
        }
    };

    OutfitController.prototype.tick = function (time) {
        if (!this.playing || !this.ready || !this.visible || document.hidden) {
            return;
        }
        if (!this.lastTick) {
            this.lastTick = time;
            return;
        }
        var delta = Math.min(80, Math.max(0, time - this.lastTick));
        this.lastTick = time;
        this.elapsed += delta;

        if (this.progress) {
            this.progress.style.width = clamp((this.elapsed / this.interval) * 100, 0, 100).toFixed(1) + '%';
        }
        if (this.elapsed >= this.interval) {
            this.elapsed = 0;
            this.select((this.index + 1) % OUTFIT_NAMES.length, false);
        }
    };

    OutfitController.prototype.resetClock = function () {
        this.lastTick = 0;
    };

    OutfitController.prototype.select = function (index, manual) {
        if (!isFinite(index)) {
            return;
        }
        var length = OUTFIT_NAMES.length;
        this.index = ((index % length) + length) % length;
        this.elapsed = 0;
        this.lastTick = 0;
        this.render();
        if (this.renderer && typeof this.renderer.setOutfit === 'function') {
            this.renderer.setOutfit(this.index, !!manual);
        }
    };

    OutfitController.prototype.toggle = function () {
        if (!this.ready) {
            return;
        }
        this.playing = !this.playing;
        this.elapsed = 0;
        this.lastTick = 0;
        this.render();
    };

    OutfitController.prototype.setRenderer = function (renderer) {
        this.renderer = renderer;
    };

    OutfitController.prototype.setReady = function () {
        this.ready = true;
        if (reducedMotion()) {
            this.playing = false;
        }
        if (this.playButton) {
            this.playButton.disabled = false;
            this.playButton.removeAttribute('aria-disabled');
        }
        this.render();
        if (this.renderer && typeof this.renderer.setOutfit === 'function') {
            this.renderer.setOutfit(this.index, true);
        }
    };

    OutfitController.prototype.disable = function () {
        this.ready = false;
        this.playing = false;
        if (this.playButton) {
            this.playButton.disabled = true;
            this.playButton.setAttribute('aria-disabled', 'true');
        }
        this.render();
    };

    OutfitController.prototype.destroy = function () {
        if (this.destroyed) {
            return;
        }
        this.destroyed = true;
        this.listeners.forEach(function (entry) {
            try { entry[0].removeEventListener(entry[1], entry[2], entry[3]); } catch (error) { /* diabaikan */ }
        });
        this.listeners.length = 0;
        this.buttons.length = 0;
        this.renderer = null;
    };

    /* ---------- perender Three.js ---------- */

    function MannequinRenderer(host, controller) {
        this.host = host;
        this.controller = controller;
        this.fallback = host.parentElement ? host.parentElement.querySelector('[data-mannequin-fallback]') : null;
        this.destroyed = false;
        this.contextLost = false;
        this.THREE = null;
        this.canvas = null;
        this.renderer = null;
        this.scene = null;
        this.camera = null;
        this.rig = null;
        this.torso = null;
        this.outfits = [];
        this.currentOutfit = null;
        this.transition = null;
        this.resources = { geometries: [], materials: [] };
        this.listeners = [];
        this.raf = 0;
        this.lastTime = 0;
        this.inView = true;
        this.resizeObserver = null;
        this.intersectionObserver = null;
        this.yaw = 0;
        this.targetYaw = 0;
        this.hoverYaw = 0;
        this.pitch = 0;
        this.targetPitch = 0;
        this.drag = null;
        this.dirty = true;

        var self = this;
        this._frame = function (time) { self.frame(time); };
        this._resize = function () { self.resize(); };

        this.boot();
    }

    MannequinRenderer.prototype.on = function (target, type, handler, options) {
        if (!target || typeof target.addEventListener !== 'function') {
            return;
        }
        target.addEventListener(type, handler, options);
        this.listeners.push([target, type, handler, options]);
    };

    MannequinRenderer.prototype.boot = function () {
        var self = this;
        if (this.destroyed) {
            return;
        }
        if (!supportsWebGL()) {
            this.fail('Ilustrasi ditampilkan karena perangkat ini tidak mendukung 3D.');
            return;
        }

        this.host.classList.add('is-loading');
        this.host.setAttribute('aria-busy', 'true');

        loadThree().then(function (THREE) {
            if (self.destroyed) {
                return;
            }
            self.THREE = THREE;
            self.build();
        }).catch(function () {
            if (!self.destroyed) {
                self.fail('Ilustrasi ditampilkan karena tampilan 3D tidak dapat dimuat.');
            }
        });
    };

    MannequinRenderer.prototype.build = function () {
        var THREE = this.THREE;
        if (!THREE || this.destroyed) {
            return;
        }

        try {
            this.canvas = document.createElement('canvas');
            this.canvas.setAttribute('aria-hidden', 'true');
            this.renderer = new THREE.WebGLRenderer({
                canvas: this.canvas,
                alpha: true,
                antialias: true,
                powerPreference: 'high-performance'
            });
            this.renderer.setClearColor(0x000000, 0);
            this.renderer.setPixelRatio(Math.min(global.devicePixelRatio || 1, 2));
            if (THREE.SRGBColorSpace) {
                this.renderer.outputColorSpace = THREE.SRGBColorSpace;
            }
            if (THREE.ACESFilmicToneMapping) {
                this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
                this.renderer.toneMappingExposure = 1.12;
            }
            this.host.appendChild(this.canvas);

            this.buildSceneGraph();
            this.bindEvents();
            this.observeSize();
            this.resize();
            this.markReady();
            this.controller.setReady();
            this.syncLoop();
        } catch (error) {
            this.fail('Ilustrasi ditampilkan karena tampilan 3D tidak dapat disiapkan.');
        }
    };

    MannequinRenderer.prototype.buildSceneGraph = function () {
        var THREE = this.THREE;
        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(31, 1, 0.1, 100);
        camera.position.set(0, 2.15, 8.4);
        camera.lookAt(0, 1.95, 0);
        this.scene = scene;
        this.camera = camera;

        var hemi = new THREE.HemisphereLight(0xfff8e9, 0x3d3a34, 2.05);
        scene.add(hemi);

        var key = new THREE.DirectionalLight(0xfff4e0, 2.35);
        key.position.set(3.4, 6.2, 4.8);
        scene.add(key);

        var fill = new THREE.DirectionalLight(0xe7ecff, 0.85);
        fill.position.set(-4.5, 3.2, 2.4);
        scene.add(fill);

        var rim = new THREE.DirectionalLight(0xffd9b0, 1.05);
        rim.position.set(0.5, 4.2, -5);
        scene.add(rim);

        var shadowGeometry = trackGeometry(this.resources, new THREE.CircleGeometry(1.2, 48));
        var shadowMaterial = trackMaterial(this.resources, new THREE.MeshBasicMaterial({
            color: 0x1d1c18,
            transparent: true,
            opacity: 0.13,
            depthWrite: false,
            side: THREE.DoubleSide
        }));
        var shadow = new THREE.Mesh(shadowGeometry, shadowMaterial);
        shadow.rotation.x = -Math.PI / 2;
        shadow.scale.set(1.15, 0.5, 1);
        shadow.position.y = 0.012;
        scene.add(shadow);

        var rig = new THREE.Group();
        scene.add(rig);
        this.rig = rig;

        var body = buildBody(THREE, this.resources);
        rig.add(body);
        this.torso = body.userData.torso || null;

        var self = this;
        this.outfits = OUTFIT_BUILDERS.map(function (builder, index) {
            var group = builder(THREE, self.resources);
            group.visible = index === 0;
            rig.add(group);
            return group;
        });
        this.currentOutfit = this.outfits[0] || null;
    };

    MannequinRenderer.prototype.bindEvents = function () {
        var self = this;

        this.on(this.host, 'pointerdown', function (event) { self.onPointerDown(event); });
        this.on(this.host, 'pointermove', function (event) { self.onPointerMove(event); });
        this.on(this.host, 'pointerup', function (event) { self.onPointerUp(event); });
        this.on(this.host, 'pointercancel', function (event) { self.onPointerUp(event); });
        this.on(this.host, 'pointerleave', function () {
            self.hoverYaw = 0;
            self.dirty = true;
        });
        this.on(document, 'visibilitychange', function () { self.syncLoop(); });

        if (this.canvas) {
            this.on(this.canvas, 'webglcontextlost', function (event) {
                event.preventDefault();
                self.handleContextLoss();
            }, false);
            this.on(this.canvas, 'webglcontextrestored', function () {
                self.handleContextRestore();
            }, false);
        }
    };

    MannequinRenderer.prototype.handleContextLoss = function () {
        if (this.destroyed || this.contextLost) {
            return;
        }
        this.contextLost = true;
        this.stopLoop();
        if (this.canvas) {
            this.canvas.style.visibility = 'hidden';
            this.canvas.style.pointerEvents = 'none';
        }
        this.controller.disable();
        showFallback(this.host, null, 'Kontek 3D terputus. Ilustrasi ditampilkan sementara.');
    };

    MannequinRenderer.prototype.handleContextRestore = function () {
        if (this.destroyed || !this.contextLost || !this.renderer) {
            return;
        }
        this.contextLost = false;
        if (this.canvas) {
            this.canvas.style.visibility = '';
            this.canvas.style.pointerEvents = '';
        }
        try {
            this.resize();
            this.markReady();
            this.controller.setReady();
            this.dirty = true;
            this.syncLoop();
        } catch (error) {
            this.fail('Ilustrasi ditampilkan karena tampilan 3D tidak dapat dipulihkan.');
        }
    };

    MannequinRenderer.prototype.observeSize = function () {
        var self = this;
        if (global.ResizeObserver && typeof global.ResizeObserver === 'function') {
            this.resizeObserver = new global.ResizeObserver(function () { self.resize(); });
            this.resizeObserver.observe(this.host);
        } else {
            this.on(global, 'resize', this._resize, { passive: true });
        }

        if (global.IntersectionObserver && typeof global.IntersectionObserver === 'function') {
            this.intersectionObserver = new global.IntersectionObserver(function (entries) {
                var entry = entries && entries[0];
                self.inView = !entry || entry.isIntersecting;
                self.syncLoop();
            }, { rootMargin: '120px 0px' });
            this.intersectionObserver.observe(this.host);
        }
    };

    MannequinRenderer.prototype.resize = function () {
        if (!this.renderer || !this.camera || this.destroyed || this.contextLost) {
            return;
        }
        var rect = this.host.getBoundingClientRect();
        var width = Math.max(1, Math.round(rect.width || this.host.clientWidth || 320));
        var height = Math.max(1, Math.round(rect.height || this.host.clientHeight || 480));
        this.renderer.setSize(width, height, false);
        this.camera.aspect = width / height;
        this.camera.position.z = this.camera.aspect < 0.72 ? 9.8 : (this.camera.aspect < 1 ? 8.9 : 8.4);
        this.camera.lookAt(0, 1.95, 0);
        this.camera.updateProjectionMatrix();
        this.dirty = true;
    };

    MannequinRenderer.prototype.syncLoop = function () {
        if (this.destroyed) {
            return;
        }
        var shouldRun = !this.contextLost && !document.hidden && this.inView;
        if (shouldRun) {
            if (!this.raf) {
                this.lastTime = 0;
                this.controller.resetClock();
                this.raf = global.requestAnimationFrame(this._frame);
            }
        } else if (this.raf) {
            global.cancelAnimationFrame(this.raf);
            this.raf = 0;
            this.controller.resetClock();
        }
    };

    MannequinRenderer.prototype.frame = function (time) {
        if (this.destroyed) {
            return;
        }
        this.raf = global.requestAnimationFrame(this._frame);
        if (this.contextLost || document.hidden || !this.inView) {
            this.lastTime = 0;
            return;
        }

        var timestamp = typeof time === 'number' ? time : now();
        var delta = this.lastTime ? Math.min(64, Math.max(0, timestamp - this.lastTime)) : 16;
        this.lastTime = timestamp;

        var moved = this.updateMotion(timestamp, delta);
        this.controller.tick(timestamp);

        if (moved || this.dirty || !reducedMotion()) {
            try {
                this.renderer.render(this.scene, this.camera);
                this.dirty = false;
            } catch (error) {
                this.handleContextLoss();
            }
        }
    };

    MannequinRenderer.prototype.updateMotion = function (timestamp, delta) {
        if (!this.rig) {
            return false;
        }
        var changed = false;
        var still = reducedMotion();
        var idle = still ? 0 : Math.sin(timestamp * 0.00022) * 0.2;
        var targetYaw = this.targetYaw + this.hoverYaw + idle;
        var nextYaw = still
            ? targetYaw
            : this.yaw + (targetYaw - this.yaw) * Math.min(1, delta * 0.009);
        if (Math.abs(nextYaw - this.yaw) > 0.0004) {
            this.yaw = nextYaw;
            changed = true;
        }

        var nextPitch = still
            ? this.targetPitch
            : this.pitch + (this.targetPitch - this.pitch) * Math.min(1, delta * 0.009);
        if (Math.abs(nextPitch - this.pitch) > 0.0004) {
            this.pitch = nextPitch;
            changed = true;
        }

        this.rig.rotation.y = this.yaw;
        this.rig.rotation.x = this.pitch;

        if (!still) {
            var breath = Math.sin(timestamp * 0.0015) * 0.006;
            if (this.torso) {
                this.torso.scale.x = 1 + breath;
                this.torso.scale.z = 0.72 + breath * 0.5;
            }
            this.rig.position.y = Math.sin(timestamp * 0.0011) * 0.012;
            changed = true;
        } else if (this.torso) {
            this.torso.scale.x = 1;
            this.torso.scale.z = 0.72;
            this.rig.position.y = 0;
        }

        if (this.transition) {
            this.updateTransition(timestamp);
            changed = true;
        }
        return changed;
    };

    MannequinRenderer.prototype.updateTransition = function (timestamp) {
        var transition = this.transition;
        if (!transition) {
            return;
        }
        var ratio = transition.duration <= 0
            ? 1
            : clamp((timestamp - transition.start) / transition.duration, 0, 1);
        var eased = ratio * ratio * (3 - 2 * ratio);
        setGroupOpacity(transition.from, 1 - eased);
        setGroupOpacity(transition.to, eased);

        if (ratio >= 1) {
            transition.from.visible = false;
            setGroupOpacity(transition.from, 1);
            setGroupOpacity(transition.to, 1);
            this.transition = null;
        }
        this.dirty = true;
    };

    MannequinRenderer.prototype.setOutfit = function (index, immediate) {
        if (!this.outfits.length) {
            return;
        }
        var next = this.outfits[index];
        if (!next) {
            return;
        }
        if (next === this.currentOutfit && !immediate && !this.transition) {
            return;
        }

        if (this.transition) {
            this.transition.from.visible = false;
            setGroupOpacity(this.transition.from, 1);
            this.transition = null;
        }

        var previous = this.currentOutfit;
        this.currentOutfit = next;

        if (immediate || reducedMotion()) {
            this.outfits.forEach(function (group, i) {
                group.visible = i === index;
                setGroupOpacity(group, 1);
            });
        } else {
            next.visible = true;
            setGroupOpacity(next, 0);
            if (previous && previous !== next) {
                setGroupOpacity(previous, 1);
                this.transition = {
                    from: previous,
                    to: next,
                    start: now(),
                    duration: 520
                };
            } else {
                setGroupOpacity(next, 1);
            }
        }
        this.dirty = true;
    };

    MannequinRenderer.prototype.onPointerDown = function (event) {
        if (event.pointerType === 'mouse' && typeof event.button === 'number' && event.button !== 0) {
            return;
        }
        this.drag = {
            id: event.pointerId,
            x: event.clientX,
            y: event.clientY,
            moved: false
        };
        this.host.style.touchAction = 'pan-y';
        this.host.classList.add('is-dragging');
        this.dirty = true;
        try { this.host.setPointerCapture(event.pointerId); } catch (error) { /* diabaikan */ }
    };

    MannequinRenderer.prototype.onPointerMove = function (event) {
        if (this.drag && event.pointerId === this.drag.id) {
            var dx = event.clientX - this.drag.x;
            var dy = event.clientY - this.drag.y;
            if (Math.abs(dx) + Math.abs(dy) > 3) {
                this.drag.moved = true;
            }
            this.targetYaw += dx * 0.009;
            this.targetPitch = clamp(this.targetPitch + dy * 0.004, -0.16, 0.16);
            this.drag.x = event.clientX;
            this.drag.y = event.clientY;
            this.dirty = true;
            return;
        }

        if (event.pointerType === 'mouse') {
            var rect = this.host.getBoundingClientRect();
            if (rect.width) {
                this.hoverYaw = ((event.clientX - rect.left) / rect.width - 0.5) * 0.45;
                this.dirty = true;
            }
        }
    };

    MannequinRenderer.prototype.onPointerUp = function (event) {
        if (!this.drag || event.pointerId !== this.drag.id) {
            return;
        }
        this.drag = null;
        this.host.classList.remove('is-dragging');
        this.dirty = true;
        try { this.host.releasePointerCapture(event.pointerId); } catch (error) { /* diabaikan */ }
    };

    MannequinRenderer.prototype.markReady = function () {
        this.host.classList.remove('is-loading', 'is-fallback');
        this.host.classList.add('is-ready');
        this.host.setAttribute('aria-busy', 'false');
        if (this.fallback) {
            this.fallback.hidden = true;
            this.fallback.classList.add('is-hidden');
            this.fallback.classList.remove('is-fallback', 'is-visible');
            this.fallback.setAttribute('aria-hidden', 'true');
        }
    };

    MannequinRenderer.prototype.fail = function (message) {
        this.stopLoop();
        this.teardownScene();
        this.controller.disable();
        showFallback(this.host, null, message);
    };

    MannequinRenderer.prototype.stopLoop = function () {
        if (this.raf) {
            global.cancelAnimationFrame(this.raf);
            this.raf = 0;
        }
        this.controller.resetClock();
    };

    MannequinRenderer.prototype.teardownScene = function () {
        this.stopLoop();

        if (this.resizeObserver) {
            try { this.resizeObserver.disconnect(); } catch (error) { /* diabaikan */ }
            this.resizeObserver = null;
        }
        if (this.intersectionObserver) {
            try { this.intersectionObserver.disconnect(); } catch (error) { /* diabaikan */ }
            this.intersectionObserver = null;
        }

        this.listeners.forEach(function (entry) {
            try { entry[0].removeEventListener(entry[1], entry[2], entry[3]); } catch (error) { /* diabaikan */ }
        });
        this.listeners.length = 0;

        this.resources.geometries.forEach(function (geometry) {
            try { if (geometry && geometry.dispose) { geometry.dispose(); } } catch (error) { /* diabaikan */ }
        });
        this.resources.materials.forEach(function (material) {
            try { if (material && material.dispose) { material.dispose(); } } catch (error) { /* diabaikan */ }
        });
        this.resources.geometries.length = 0;
        this.resources.materials.length = 0;

        if (this.canvas && this.canvas.parentNode) {
            try { this.canvas.parentNode.removeChild(this.canvas); } catch (error) { /* diabaikan */ }
        }

        if (this.renderer) {
            try { this.renderer.dispose(); } catch (error) { /* diabaikan */ }
        }

        this.canvas = null;
        this.renderer = null;
        this.scene = null;
        this.camera = null;
        this.rig = null;
        this.torso = null;
        this.outfits.length = 0;
        this.currentOutfit = null;
        this.transition = null;
        this.drag = null;
        this.host.classList.remove('is-dragging');
    };

    MannequinRenderer.prototype.destroy = function () {
        if (this.destroyed) {
            return;
        }
        this.destroyed = true;
        this.teardownScene();
    };

    function showFallback(host, controller, message) {
        if (!host) {
            return;
        }
        host.classList.remove('is-ready', 'is-loading');
        host.classList.add('is-fallback');
        host.setAttribute('aria-busy', 'false');

        var fallback = host.parentElement ? host.parentElement.querySelector('[data-mannequin-fallback]') : null;
        if (fallback) {
            fallback.hidden = false;
            fallback.classList.remove('is-hidden');
            fallback.classList.add('is-fallback', 'is-visible');
            fallback.setAttribute('aria-hidden', 'false');
            var caption = fallback.querySelector('.mannequin-fallback__caption');
            if (caption) {
                caption.textContent = message || 'Ilustrasi ditampilkan karena tampilan 3D tidak tersedia.';
            }
        }
        if (controller && typeof controller.disable === 'function') {
            controller.disable();
        }
    }

    /* ---------- pemuatan modul dan dukungan WebGL ---------- */

    function vendorUrl() {
        var script = document.currentScript;
        if (!script || !script.src) {
            var scripts = document.querySelectorAll('script[src]');
            for (var i = scripts.length - 1; i >= 0; i -= 1) {
                if (/hero\.js(?:[?#].*)?$/.test(scripts[i].getAttribute('src') || '')) {
                    script = scripts[i];
                    break;
                }
            }
        }
        var base = script && script.src ? script.src.replace(/[^\\/]*$/, '') : global.location.href;
        try {
            return new URL('vendor/three.module.js', base).href;
        } catch (error) {
            return 'assets/js/vendor/three.module.js';
        }
    }

    function loadThree() {
        if (threePromise) {
            return threePromise;
        }
        var url = vendorUrl();
        try {
            threePromise = import(url).then(function (module) {
                return module && module.WebGLRenderer ? module : (module && module.default) || module;
            });
        } catch (error) {
            threePromise = Promise.reject(error);
        }
        return threePromise;
    }

    function supportsWebGL() {
        try {
            var canvas = document.createElement('canvas');
            var gl = canvas.getContext('webgl2')
                || canvas.getContext('webgl')
                || canvas.getContext('experimental-webgl');
            if (!gl) {
                return false;
            }
            var lose = gl.getExtension('WEBGL_lose_context');
            if (lose && typeof lose.loseContext === 'function') {
                lose.loseContext();
            }
            return true;
        } catch (error) {
            return false;
        }
    }

    /* ---------- pembangun geometri prosedural ---------- */

    function trackGeometry(resources, geometry) {
        resources.geometries.push(geometry);
        return geometry;
    }

    function trackMaterial(resources, material) {
        resources.materials.push(material);
        return material;
    }

    function makeMaterial(THREE, resources, color, roughness, extra) {
        var options = {
            color: color,
            roughness: roughness == null ? 0.78 : roughness,
            metalness: 0.015,
            transparent: true,
            opacity: 1,
            side: THREE.DoubleSide
        };
        if (extra) {
            Object.keys(extra).forEach(function (key) { options[key] = extra[key]; });
        }
        return trackMaterial(resources, new THREE.MeshStandardMaterial(options));
    }

    function addMesh(THREE, resources, group, geometry, material, position, rotation, scale) {
        var mesh = new THREE.Mesh(trackGeometry(resources, geometry), material);
        position = position || [0, 0, 0];
        rotation = rotation || [0, 0, 0];
        mesh.position.set(position[0] || 0, position[1] || 0, position[2] || 0);
        mesh.rotation.set(rotation[0] || 0, rotation[1] || 0, rotation[2] || 0);
        if (scale) {
            mesh.scale.set(
                scale[0] == null ? 1 : scale[0],
                scale[1] == null ? 1 : scale[1],
                scale[2] == null ? 1 : scale[2]
            );
        }
        group.add(mesh);
        return mesh;
    }

    function setGroupOpacity(group, value) {
        if (!group || typeof group.traverse !== 'function') {
            return;
        }
        group.traverse(function (object) {
            if (!object.isMesh || !object.material) {
                return;
            }
            var materials = Array.isArray(object.material) ? object.material : [object.material];
            materials.forEach(function (material) {
                if (!material) {
                    return;
                }
                material.transparent = true;
                material.opacity = value;
                material.depthWrite = value > 0.96;
            });
        });
    }

    function buildBody(THREE, resources) {
        var group = new THREE.Group();
        var skin = makeMaterial(THREE, resources, 0xe9e0d0, 0.88);
        var skinSoft = makeMaterial(THREE, resources, 0xdcd2c0, 0.92);
        var torso = null;

        addMesh(THREE, resources, group, new THREE.SphereGeometry(0.34, 28, 20), skin,
            [0, 3.58, 0], [0, 0, 0], [1, 1.1, 0.94]);
        addMesh(THREE, resources, group, new THREE.CylinderGeometry(0.135, 0.16, 0.26, 18), skinSoft,
            [0, 3.22, 0]);

        torso = addMesh(THREE, resources, group, new THREE.CylinderGeometry(0.41, 0.31, 1.18, 26, 1), skin,
            [0, 2.58, 0], [0, 0, 0], [1, 1, 0.72]);
        addMesh(THREE, resources, group, new THREE.SphereGeometry(0.36, 22, 16), skin,
            [0, 1.96, 0], [0, 0, 0], [1.12, 0.76, 0.74]);

        [-1, 1].forEach(function (side) {
            addMesh(THREE, resources, group, new THREE.SphereGeometry(0.16, 18, 12), skin,
                [side * 0.43, 2.96, 0]);
            addMesh(THREE, resources, group, new THREE.CapsuleGeometry(0.098, 0.62, 6, 14), skin,
                [side * 0.54, 2.57, 0], [0, 0, side * -0.13]);
            addMesh(THREE, resources, group, new THREE.SphereGeometry(0.105, 16, 12), skinSoft,
                [side * 0.58, 2.2, 0]);
            addMesh(THREE, resources, group, new THREE.CapsuleGeometry(0.088, 0.58, 6, 14), skin,
                [side * 0.6, 1.86, 0], [0, 0, side * -0.06]);
            addMesh(THREE, resources, group, new THREE.SphereGeometry(0.115, 16, 12), skinSoft,
                [side * 0.61, 1.5, 0], [0, 0, 0], [0.85, 1.15, 0.7]);

            addMesh(THREE, resources, group, new THREE.CapsuleGeometry(0.145, 0.7, 6, 16), skin,
                [side * 0.19, 1.42, 0]);
            addMesh(THREE, resources, group, new THREE.SphereGeometry(0.145, 16, 12), skinSoft,
                [side * 0.19, 1.0, 0]);
            addMesh(THREE, resources, group, new THREE.CapsuleGeometry(0.115, 0.64, 6, 14), skin,
                [side * 0.19, 0.61, 0]);
            addMesh(THREE, resources, group, new THREE.SphereGeometry(0.115, 16, 12), skinSoft,
                [side * 0.19, 0.26, 0]);
            addMesh(THREE, resources, group, new THREE.CapsuleGeometry(0.115, 0.3, 6, 14), skin,
                [side * 0.19, 0.13, 0.16], [Math.PI / 2, 0, 0], [1, 1, 0.8]);
        });

        group.userData.torso = torso;
        return group;
    }

    function outfitShell(THREE, resources, group, color, options) {
        var settings = options || {};
        var fabric = makeMaterial(THREE, resources, color, settings.roughness == null ? 0.82 : settings.roughness);
        var top = addMesh(THREE, resources, group,
            new THREE.CylinderGeometry(settings.topRadius || 0.46, settings.bottomRadius || 0.38, settings.topHeight || 0.94, 26, 1),
            fabric, [0, settings.topY || 2.62, 0], [0, 0, 0],
            [settings.topScaleX || 1, 1, settings.topScaleZ || 0.76]);
        return { fabric: fabric, top: top };
    }

    function addSleeves(THREE, resources, group, material, options) {
        var settings = options || {};
        [-1, 1].forEach(function (side) {
            addMesh(THREE, resources, group,
                new THREE.CapsuleGeometry(settings.radius || 0.13, settings.length || 0.62, 6, 14),
                material,
                [side * (settings.x || 0.55), settings.y || 2.56, 0],
                [0, 0, side * (settings.tilt == null ? -0.14 : settings.tilt)]);
        });
    }

    function addLegwear(THREE, resources, group, color, options) {
        var settings = options || {};
        var fabric = makeMaterial(THREE, resources, color, settings.roughness == null ? 0.88 : settings.roughness);
        [-1, 1].forEach(function (side) {
            var x = side * (settings.x || 0.19);
            addMesh(THREE, resources, group,
                new THREE.CylinderGeometry(settings.thighTop || 0.19, settings.thighBottom || 0.17,
                    settings.thighHeight || 0.96, 18, 1),
                fabric, [x, 1.42, 0], [0, 0, 0], [1, 1, settings.scaleZ || 0.94]);
            addMesh(THREE, resources, group,
                new THREE.CylinderGeometry(settings.calfTop || 0.16, settings.calfBottom || 0.14,
                    settings.calfHeight || 0.76, 18, 1),
                fabric, [x, 0.58, 0], [0, 0, 0], [1, 1, settings.scaleZ || 0.94]);
        });
        if (settings.belt !== false) {
            addMesh(THREE, resources, group, new THREE.TorusGeometry(0.35, 0.022, 8, 30),
                settings.beltMaterial || makeMaterial(THREE, resources, 0x4a463f, 0.7),
                [0, 1.93, 0], [Math.PI / 2, 0, 0], [1, 0.74, 1]);
        }
        return fabric;
    }

    function addShoes(THREE, resources, group, color, options) {
        var settings = options || {};
        var material = makeMaterial(THREE, resources, color, settings.roughness == null ? 0.7 : settings.roughness);
        [-1, 1].forEach(function (side) {
            addMesh(THREE, resources, group,
                new THREE.BoxGeometry(settings.width || 0.3, settings.height || 0.16, settings.depth || 0.48),
                material, [side * 0.19, settings.y || 0.1, 0.16]);
        });
        return material;
    }

    var OUTFIT_BUILDERS = [
        /* 0 — Kasual */
        function (THREE, resources) {
            var group = new THREE.Group();
            var top = outfitShell(THREE, resources, group, 0x73795f, {
                topRadius: 0.47, bottomRadius: 0.4, topHeight: 0.94, topY: 2.64, topScaleZ: 0.78
            });
            addSleeves(THREE, resources, group, top.fabric, { radius: 0.135, length: 0.6, x: 0.55 });
            addMesh(THREE, resources, group, new THREE.TorusGeometry(0.19, 0.035, 8, 24), top.fabric,
                [0, 3.1, 0], [Math.PI / 2, 0, 0], [1, 0.8, 1]);
            addMesh(THREE, resources, group, new THREE.CylinderGeometry(0.48, 0.48, 0.075, 26), top.fabric,
                [0, 2.17, 0], [0, 0, 0], [1, 1, 0.78]);
            addLegwear(THREE, resources, group, 0x6b7c93, {
                thighTop: 0.2, thighBottom: 0.17, calfTop: 0.17, calfBottom: 0.15, roughness: 0.92
            });
            addShoes(THREE, resources, group, 0xf1eee5, { width: 0.31, height: 0.17, depth: 0.5 });
            return group;
        },

        /* 1 — Vintage */
        function (THREE, resources) {
            var group = new THREE.Group();
            var dress = outfitShell(THREE, resources, group, 0xb45c3f, {
                topRadius: 0.44, bottomRadius: 0.36, topHeight: 0.98, topY: 2.64, roughness: 0.86
            });
            addSleeves(THREE, resources, group, dress.fabric, { radius: 0.145, length: 0.3, x: 0.53, y: 2.78, tilt: -0.2 });
            addMesh(THREE, resources, group, new THREE.CylinderGeometry(0.36, 0.72, 1.18, 30, 1), dress.fabric,
                [0, 1.62, 0], [0, 0, 0], [1, 1, 0.8]);
            addMesh(THREE, resources, group, new THREE.TorusGeometry(0.7, 0.03, 8, 36), dress.fabric,
                [0, 1.05, 0], [Math.PI / 2, 0, 0], [1, 0.8, 1]);
            addMesh(THREE, resources, group, new THREE.TorusGeometry(0.37, 0.035, 8, 30),
                makeMaterial(THREE, resources, 0xe8dcc4, 0.72), [0, 2.17, 0], [Math.PI / 2, 0, 0], [1, 0.76, 1]);
            addMesh(THREE, resources, group, new THREE.TorusGeometry(0.18, 0.03, 8, 24),
                makeMaterial(THREE, resources, 0xe8dcc4, 0.72), [0, 3.12, 0], [Math.PI / 2, 0, 0], [1, 0.8, 1]);
            addShoes(THREE, resources, group, 0x6f4630, { width: 0.28, height: 0.14, depth: 0.44 });
            return group;
        },

        /* 2 — Streetwear */
        function (THREE, resources) {
            var group = new THREE.Group();
            var hoodie = outfitShell(THREE, resources, group, 0x4d5849, {
                topRadius: 0.53, bottomRadius: 0.46, topHeight: 1.06, topY: 2.6, topScaleZ: 0.84, roughness: 0.92
            });
            addMesh(THREE, resources, group, new THREE.SphereGeometry(0.42, 22, 16), hoodie.fabric,
                [0, 3.18, -0.16], [0, 0, 0], [1, 0.82, 0.72]);
            addSleeves(THREE, resources, group, hoodie.fabric, { radius: 0.16, length: 0.7, x: 0.59, tilt: -0.16 });
            addMesh(THREE, resources, group, new THREE.BoxGeometry(0.46, 0.23, 0.05), hoodie.fabric,
                [0, 2.34, 0.39]);
            addLegwear(THREE, resources, group, 0x25282a, {
                thighTop: 0.24, thighBottom: 0.21, calfTop: 0.21, calfBottom: 0.19,
                scaleZ: 1.02, roughness: 0.94
            });
            [-1, 1].forEach(function (side) {
                addMesh(THREE, resources, group, new THREE.BoxGeometry(0.17, 0.19, 0.07), hoodie.fabric,
                    [side * 0.33, 1.28, 0.2]);
            });
            addShoes(THREE, resources, group, 0xd8d3c6, { width: 0.34, height: 0.2, depth: 0.54, roughness: 0.82 });
            return group;
        },

        /* 3 — Minimalis */
        function (THREE, resources) {
            var group = new THREE.Group();
            var top = outfitShell(THREE, resources, group, 0x242424, {
                topRadius: 0.42, bottomRadius: 0.35, topHeight: 1.02, topY: 2.62, topScaleZ: 0.71, roughness: 0.66
            });
            addSleeves(THREE, resources, group, top.fabric, { radius: 0.108, length: 0.68, x: 0.5, tilt: -0.12 });
            addMesh(THREE, resources, group, new THREE.CylinderGeometry(0.175, 0.175, 0.25, 20), top.fabric,
                [0, 3.11, 0]);
            addLegwear(THREE, resources, group, 0xd8cdbb, {
                thighTop: 0.3, thighBottom: 0.33, calfTop: 0.33, calfBottom: 0.35,
                scaleZ: 0.88, roughness: 0.9,
                beltMaterial: makeMaterial(THREE, resources, 0xa98d63, 0.55, { metalness: 0.2 })
            });
            addShoes(THREE, resources, group, 0x5b4434, { width: 0.29, height: 0.14, depth: 0.44, roughness: 0.62 });
            return group;
        },

        /* 4 — Outerwear */
        function (THREE, resources) {
            var group = new THREE.Group();
            var base = makeMaterial(THREE, resources, 0x343534, 0.8);
            addMesh(THREE, resources, group, new THREE.CylinderGeometry(0.4, 0.34, 1.05, 24), base,
                [0, 2.6, 0], [0, 0, 0], [1, 1, 0.72]);
            addMesh(THREE, resources, group, new THREE.CylinderGeometry(0.18, 0.18, 0.26, 20), base,
                [0, 3.1, 0]);

            var coat = makeMaterial(THREE, resources, 0xa98257, 0.86);
            [-1, 1].forEach(function (side) {
                addMesh(THREE, resources, group, new THREE.BoxGeometry(0.3, 1.48, 0.09), coat,
                    [side * 0.25, 2.47, 0.3], [0, 0, side * -0.04]);
                addMesh(THREE, resources, group, new THREE.BoxGeometry(0.31, 0.19, 0.11), coat,
                    [side * 0.18, 3.04, 0.29], [0, 0, side * -0.34]);
            });
            addMesh(THREE, resources, group, new THREE.BoxGeometry(0.8, 1.48, 0.09), coat,
                [0, 2.47, -0.29]);
            addSleeves(THREE, resources, group, coat, { radius: 0.17, length: 0.78, x: 0.58, tilt: -0.15 });
            addMesh(THREE, resources, group, new THREE.TorusGeometry(0.42, 0.035, 8, 32),
                makeMaterial(THREE, resources, 0x6f5234, 0.72), [0, 2.04, 0], [Math.PI / 2, 0, 0], [1, 0.74, 1]);

            [-1, 1].forEach(function (side) {
                addMesh(THREE, resources, group, new THREE.SphereGeometry(0.025, 10, 8),
                    makeMaterial(THREE, resources, 0x5a422b, 0.6), [0.1, 2.75 - side * 0.2, 0.36]);
            });

            addLegwear(THREE, resources, group, 0x1f2122, { thighTop: 0.18, calfTop: 0.15, roughness: 0.92, belt: false });
            [-1, 1].forEach(function (side) {
                addMesh(THREE, resources, group, new THREE.BoxGeometry(0.3, 0.28, 0.46),
                    makeMaterial(THREE, resources, 0x3a2d24, 0.78), [side * 0.19, 0.18, 0.16]);
                addMesh(THREE, resources, group, new THREE.CylinderGeometry(0.15, 0.16, 0.42, 16),
                    makeMaterial(THREE, resources, 0x3a2d24, 0.78), [side * 0.19, 0.45, 0]);
            });
            return group;
        }
    ];

    ready(init);
})(window);
