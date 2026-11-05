// Jejak Sirah — paparan pemain.
// Kemajuan disimpan dalam peranti (localStorage). Tiada log masuk diperlukan.
(function () {
    'use strict';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const store = {
        get(key, fallback) {
            try {
                const v = localStorage.getItem('jr:' + key);
                return v === null ? fallback : JSON.parse(v);
            } catch (e) {
                return fallback;
            }
        },
        set(key, value) {
            try { localStorage.setItem('jr:' + key, JSON.stringify(value)); } catch (e) { /* storan disekat */ }
        },
    };

    const DAY = 24 * 60 * 60 * 1000;
    const today = () => { const d = new Date(); d.setHours(0, 0, 0, 0); return d.getTime(); };

    function startOf(arc) {
        let start = store.get(arc + ':start', null);
        if (!start) { start = today(); store.set(arc + ':start', start); }
        return start;
    }
    const daysSince = (start) => Math.floor((today() - start) / DAY);
    const lockText = (n) => (n === 1 ? 'Dibuka esok' : 'Dibuka dalam ' + n + ' hari');
    const done = (arc) => store.get(arc + ':done', []);

    function progressText(arc, total) {
        const n = done(arc).length;
        if (!total) return '';
        if (n === 0) return total + ' bab menanti';
        if (n >= total) return 'Semua ' + total + ' bintang telah bersinar ✦';
        return n + ' daripada ' + total + ' bintang bersinar';
    }

    /* ======================= Kesan: jejak kaki & pasir ======================= */

    const FOOT = '<svg viewBox="0 0 26 40" aria-hidden="true"><g fill="currentColor"><path d="M12 38c-4.6 0-7-4.6-7-10.5C5 20 7.4 14 12.2 14c4.6 0 6.8 5.4 6.8 12.2C19 33 16.6 38 12 38Z"/><ellipse cx="6.4" cy="9.6" rx="2.6" ry="3.2"/><ellipse cx="11.2" cy="6.6" rx="2.2" ry="2.8"/><ellipse cx="15.4" cy="6.8" rx="1.9" ry="2.4"/><ellipse cx="18.6" cy="9" rx="1.6" ry="2"/><ellipse cx="20.6" cy="12.4" rx="1.3" ry="1.7"/></g></svg>';

    if (!reduceMotion) {
        const layer = document.createElement('div');
        layer.className = 'fx-layer';
        layer.setAttribute('aria-hidden', 'true');
        document.body.appendChild(layer);

        const spawn = (cls, x, y, vars, html) => {
            if (layer.childElementCount > 120) return;
            const el = document.createElement('span');
            el.className = cls;
            el.style.left = x + 'px';
            el.style.top = y + 'px';
            Object.entries(vars || {}).forEach(([k, v]) => el.style.setProperty(k, v));
            if (html) el.innerHTML = html;
            el.addEventListener('animationend', () => el.remove(), { once: true });
            layer.appendChild(el);
        };

        // Jejak tapak kaki mengikut tetikus (komputer sahaja)
        if (window.matchMedia('(pointer: fine)').matches) {
            let lastX = null, lastY = null, left = true;
            window.addEventListener('pointermove', (e) => {
                if (e.pointerType !== 'mouse') return;
                if (lastX === null) { lastX = e.clientX; lastY = e.clientY; return; }
                const dx = e.clientX - lastX, dy = e.clientY - lastY;
                if (Math.hypot(dx, dy) < 46) return;
                const angle = Math.atan2(dy, dx) * 180 / Math.PI + 90;
                spawn('fx-step', e.clientX, e.clientY, { '--a': angle + 'deg', '--side': (left ? -6 : 6) + 'px' }, FOOT);
                left = !left;
                lastX = e.clientX;
                lastY = e.clientY;
            }, { passive: true });
        }

        // Sentuh / klik: tapak kaki + riak pasir + butir pasir + kerdipan bintang
        const sand = ['#f2c97d', '#e0a24a', '#d9b98a', '#fff1cf'];
        let lastTap = 0;
        window.addEventListener('pointerdown', (e) => {
            const now = Date.now();
            if (now - lastTap < 80) return;
            lastTap = now;
            const x = e.clientX, y = e.clientY;
            spawn('fx-ripple', x, y);
            spawn('fx-stamp', x, y, { '--a': (Math.random() * 30 - 15) + 'deg' }, FOOT);
            for (let i = 0; i < 10; i++) {
                const a = Math.random() * Math.PI - Math.PI; // ke atas & sisi
                const d = 18 + Math.random() * 38;
                spawn('fx-grain', x, y, {
                    '--dx': Math.cos(a) * d + 'px',
                    '--dy': Math.sin(a) * d * 0.8 + 14 + 'px',
                    '--s': (2 + Math.random() * 3).toFixed(1) + 'px',
                    '--c': sand[i % sand.length],
                });
            }
            if (e.target.closest && e.target.closest('button, a, .choice-btn')) {
                for (let i = 0; i < 4; i++) {
                    const a = (Math.PI / 2) * i + Math.PI / 4;
                    spawn('fx-spark', x, y, { '--dx': Math.cos(a) * 34 + 'px', '--dy': Math.sin(a) * 34 + 'px' });
                }
            }
        }, { passive: true });
    }

    /* ======================= Halaman utama ======================= */

    document.querySelectorAll('.arc-card[data-arc]').forEach((card) => {
        const arc = card.dataset.arc;
        const n = done(arc).length;
        card.querySelectorAll('.lamp-dot').forEach((dot, i) => dot.classList.toggle('is-lit', i < n));
        const cta = card.querySelector('[data-progress-text]');
        if (cta && n > 0) cta.textContent = 'Sambung kembara → (' + n + '/' + card.dataset.total + ')';
    });

    /* ======================= Peta ======================= */

    const mapHead = document.querySelector('[data-arc-start]');
    if (mapHead) {
        const arc = mapHead.dataset.arcStart;
        const elapsed = daysSince(startOf(arc));
        const finished = done(arc);
        const stops = [...document.querySelectorAll('.stop')];
        let current = -1;

        stops.forEach((stop, i) => {
            const id = parseInt(stop.dataset.chapter, 10);
            const unlock = parseInt(stop.dataset.unlock, 10) || 0;
            const isDone = finished.includes(id);
            stop.classList.toggle('is-done', isDone);
            if (unlock > elapsed) {
                stop.classList.add('is-locked');
                const lock = stop.querySelector('[data-lock-text]');
                lock.textContent = lockText(unlock - elapsed);
                lock.hidden = false;
            } else if (!isDone && current === -1) {
                current = i;
                stop.classList.add('is-current');
            }
        });

        // Laluan emas sehingga bab terakhir yang selesai
        let lastDone = -1;
        stops.forEach((s, i) => { if (s.classList.contains('is-done')) lastDone = i; });
        const path = document.querySelector('[data-path-done]');
        if (path && stops.length > 1 && lastDone > 0) {
            const pct = Math.round((lastDone / (stops.length - 1)) * 100);
            setTimeout(() => { path.style.strokeDasharray = pct + ' 100'; }, 400);
        }

        const text = document.querySelector('[data-progress-text][data-arc]');
        if (text) text.textContent = progressText(arc, parseInt(text.dataset.total, 10) || 0);

        const target = stops[current >= 0 ? current : Math.max(lastDone, 0)];
        if (target && !reduceMotion) setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'center' }), 700);
    }

    /* ======================= Mod cerita ======================= */

    const story = document.querySelector('[data-story]');
    if (!story) return;

    const arc = story.dataset.arc;
    const chapterId = parseInt(story.dataset.chapter, 10);
    const unlock = parseInt(story.dataset.unlock, 10) || 0;
    const elapsed = daysSince(startOf(arc));
    const prevBtn = story.querySelector('[data-prev]');
    const nextBtn = story.querySelector('[data-next]');

    if (unlock > elapsed) {
        const locked = story.querySelector('[data-locked-slide]');
        locked.hidden = false;
        locked.classList.add('is-active');
        locked.querySelector('[data-locked-text]').textContent =
            lockText(unlock - elapsed) + '. Satu bab sehari — sabar menanti juga sebahagian daripada perjalanan.';
        story.querySelector('.story-progress').hidden = true;
        story.querySelector('.story-nav').hidden = true;
        return;
    }

    const slides = [...story.querySelectorAll('[data-slide]')];
    const progress = story.querySelector('[data-progress]');
    slides.forEach(() => progress.appendChild(document.createElement('span')));
    const bars = [...progress.children];

    // Pecahkan teks babak kepada perkataan supaya boleh muncul satu demi satu.
    function splitWords(el) {
        let i = 0;
        const walk = (node) => {
            [...node.childNodes].forEach((child) => {
                if (child.nodeType === Node.TEXT_NODE) {
                    const parts = child.textContent.split(/(\s+)/);
                    const frag = document.createDocumentFragment();
                    parts.forEach((p) => {
                        if (p === '') return;
                        if (/^\s+$/.test(p)) { frag.appendChild(document.createTextNode(' ')); return; }
                        const s = document.createElement('span');
                        s.className = 'w';
                        s.style.setProperty('--i', i++);
                        s.textContent = p;
                        frag.appendChild(s);
                    });
                    child.replaceWith(frag);
                } else if (child.nodeType === Node.ELEMENT_NODE) {
                    walk(child);
                }
            });
        };
        walk(el);
        return i;
    }

    const groups = slides.map((slide) => {
        const g = slide.querySelector('[data-reveal-group]');
        if (!g) return null;
        const blocks = [...g.children];
        blocks.forEach((b) => { b.dataset.words = splitWords(b); });
        return { blocks, shown: 0 };
    });

    let index = 0;
    let animTimer = null;
    let animating = false;

    function revealNext(group) {
        const block = group.blocks[group.shown++];
        block.classList.add('is-shown');
        if (reduceMotion) { block.classList.add('is-instant'); return; }
        animating = true;
        clearTimeout(animTimer);
        animTimer = setTimeout(() => { animating = false; updateNav(); }, 550 + block.dataset.words * 45);
    }

    function finishAnimation() {
        const group = groups[index];
        if (group) group.blocks.forEach((b) => { if (b.classList.contains('is-shown')) b.classList.add('is-instant'); });
        clearTimeout(animTimer);
        animating = false;
    }

    function updateNav() {
        prevBtn.disabled = index === 0;
        nextBtn.disabled = index === slides.length - 1;
        const group = groups[index];
        nextBtn.classList.toggle('is-waiting', !animating && (!group || group.shown >= group.blocks.length));
        bars.forEach((b, i) => b.classList.toggle('is-done', i < index || (i === index && (!group || group.shown >= group.blocks.length))));
    }

    function show(i) {
        index = Math.max(0, Math.min(i, slides.length - 1));
        story.scrollTop = 0;
        slides[index].scrollTop = 0;
        slides.forEach((s, k) => s.classList.toggle('is-active', k === index));
        const group = groups[index];
        if (group && group.shown === 0) setTimeout(() => { revealNext(group); updateNav(); }, reduceMotion ? 0 : 500);
        updateNav();
    }

    function next() {
        if (animating) { finishAnimation(); updateNav(); return; }
        const group = groups[index];
        if (group && group.shown < group.blocks.length) { revealNext(group); updateNav(); return; }
        if (index < slides.length - 1) show(index + 1);
    }

    function prev() {
        if (index > 0) show(index - 1);
    }

    nextBtn.addEventListener('click', next);
    prevBtn.addEventListener('click', prev);

    // Sentuh skrin: kiri 30% = undur, selebihnya = teruskan. Abaikan butang & panel.
    story.addEventListener('click', (e) => {
        if (e.target.closest('button, a, textarea, input, .sheet, .panel-glass, .after, .story-top')) return;
        if (e.clientX < window.innerWidth * 0.3) prev(); else next();
    });

    document.addEventListener('keydown', (e) => {
        if (e.target.closest('textarea, input') || story.querySelector('.sheet:not([hidden])')) return;
        if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'Enter') { e.preventDefault(); next(); }
        if (e.key === 'ArrowLeft') prev();
    });

    // Helaian sumber
    story.querySelectorAll('[data-open-sheet]').forEach((b) => b.addEventListener('click', () => {
        const sheet = document.getElementById(b.dataset.openSheet);
        sheet.hidden = false;
        story.classList.add('sheet-open');
        sheet.querySelector('[data-close-sheet]').focus({ preventScroll: true });
    }));
    story.querySelectorAll('.sheet').forEach((sheet) => {
        sheet.addEventListener('click', (e) => {
            if (e.target === sheet || e.target.closest('[data-close-sheet]')) { sheet.hidden = true; story.classList.remove('sheet-open'); }
        });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { story.querySelectorAll('.sheet').forEach((s) => { s.hidden = true; }); story.classList.remove('sheet-open'); }
    });

    // Pilihan Yusuf
    const choice = story.querySelector('[data-choice]');
    if (choice) {
        const key = 'choice:' + chapterId;
        const pick = (opt) => {
            choice.querySelectorAll('[data-option]').forEach((b) => {
                b.classList.toggle('is-picked', b.dataset.option === opt);
                b.disabled = true;
            });
            choice.querySelectorAll('[data-outcome]').forEach((p) => { p.hidden = p.dataset.outcome !== opt; });
        };
        const saved = store.get(key, null);
        if (saved) pick(saved);
        choice.querySelectorAll('[data-option]').forEach((b) => b.addEventListener('click', () => {
            store.set(key, b.dataset.option);
            pick(b.dataset.option);
        }));
    }

    // Fakta atau Mitos?
    story.querySelectorAll('.quiz-item').forEach((item) => {
        const answer = item.dataset.answer;
        item.querySelectorAll('[data-guess]').forEach((b) => b.addEventListener('click', () => {
            const right = b.dataset.guess === answer;
            item.querySelectorAll('[data-guess]').forEach((x) => {
                x.disabled = true;
                if (x.dataset.guess === answer) x.classList.add('is-correct');
            });
            if (!right) b.classList.add('is-wrong');
            const verdict = item.querySelector('[data-verdict]');
            verdict.textContent = right ? 'Tepat!' : 'Hampir — mari semak';
            verdict.classList.add(right ? 'is-right' : 'is-wrong');
            item.querySelector('.quiz-reveal').hidden = false;
        }));
    });

    // Tandakan jejak (bintang)
    const finish = story.querySelector('[data-finish]');
    const lamp = finish.querySelector('[data-complete]');
    const after = finish.querySelector('[data-after-complete]');
    const journal = finish.querySelector('[data-journal]');
    const journalKey = 'journal:' + chapterId;

    function lit() {
        finish.classList.add('is-lit');
        lamp.classList.add('is-lit');
        lamp.disabled = true;
        finish.querySelector('[data-finish-title]').textContent = 'Jejak bab ini telah ditanda';
        finish.querySelector('[data-finish-sub]').hidden = true;
        after.hidden = false;
        if (journal) journal.value = store.get(journalKey, '');
    }

    if (done(arc).includes(chapterId)) lit();

    lamp.addEventListener('click', () => {
        const list = done(arc);
        if (!list.includes(chapterId)) list.push(chapterId);
        store.set(arc + ':done', list);
        finish.querySelectorAll('[data-card]').forEach((c) => store.set('card:' + c.dataset.card, Date.now()));
        lit();
        if (sound.on) sound.chime();
    });

    if (journal) {
        let timer;
        const saved = finish.querySelector('[data-journal-saved]');
        journal.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => { store.set(journalKey, journal.value); saved.hidden = false; }, 400);
        });
    }

    /* ---------- Bunyi angin (dijana, tiada fail audio) ---------- */

    const sound = {
        on: false,
        ctx: null,
        gain: null,
        start() {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            this.ctx = this.ctx || new AC();
            const ctx = this.ctx;
            const len = ctx.sampleRate * 4;
            const buf = ctx.createBuffer(1, len, ctx.sampleRate);
            const data = buf.getChannelData(0);
            let last = 0;
            for (let i = 0; i < len; i++) { last = (last + 0.02 * (Math.random() * 2 - 1)) / 1.02; data[i] = last * 3.5; }
            const src = ctx.createBufferSource();
            src.buffer = buf;
            src.loop = true;
            const filter = ctx.createBiquadFilter();
            filter.type = 'bandpass';
            filter.frequency.value = 400;
            filter.Q.value = 0.6;
            const lfo = ctx.createOscillator();
            const lfoGain = ctx.createGain();
            lfo.frequency.value = 0.08;
            lfoGain.gain.value = 250;
            lfo.connect(lfoGain).connect(filter.frequency);
            this.gain = ctx.createGain();
            this.gain.gain.value = 0;
            this.gain.gain.linearRampToValueAtTime(0.35, ctx.currentTime + 2);
            src.connect(filter).connect(this.gain).connect(ctx.destination);
            src.start();
            lfo.start();
            this.src = src;
            this.lfo = lfo;
        },
        stop() {
            if (!this.ctx || !this.gain) return;
            const t = this.ctx.currentTime;
            this.gain.gain.linearRampToValueAtTime(0, t + 0.8);
            const src = this.src, lfo = this.lfo;
            setTimeout(() => { try { src.stop(); lfo.stop(); } catch (e) { /* sudah berhenti */ } }, 900);
        },
        chime() {
            const ctx = this.ctx;
            [523.25, 659.25, 783.99].forEach((f, i) => {
                const o = ctx.createOscillator();
                const g = ctx.createGain();
                o.type = 'sine';
                o.frequency.value = f;
                const t = ctx.currentTime + i * 0.18;
                g.gain.setValueAtTime(0, t);
                g.gain.linearRampToValueAtTime(0.15, t + 0.03);
                g.gain.exponentialRampToValueAtTime(0.0001, t + 1.6);
                o.connect(g).connect(ctx.destination);
                o.start(t);
                o.stop(t + 1.7);
            });
        },
    };

    const soundBtn = story.querySelector('[data-sound]');
    soundBtn.addEventListener('click', () => {
        sound.on = !sound.on;
        soundBtn.setAttribute('aria-pressed', String(sound.on));
        store.set('sound', sound.on);
        if (sound.on) sound.start(); else sound.stop();
    });

    show(0);
})();
