// Jejak Rasul — kemajuan pemain disimpan dalam peranti (localStorage).
// Tiada log masuk diperlukan untuk bermain.
(function () {
    'use strict';

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
            try { localStorage.setItem('jr:' + key, JSON.stringify(value)); } catch (e) { /* storan penuh / disekat */ }
        },
    };

    const DAY = 24 * 60 * 60 * 1000;

    // Tarikh pemain mula sesuatu arka; bab dibuka ikut unlock_day_offset.
    function startOf(arc) {
        let start = store.get(arc + ':start', null);
        if (!start) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            start = today.getTime();
            store.set(arc + ':start', start);
        }
        return start;
    }

    function daysSince(start) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return Math.floor((today.getTime() - start) / DAY);
    }

    function lockText(daysLeft) {
        return daysLeft === 1 ? 'Dibuka esok' : 'Dibuka dalam ' + daysLeft + ' hari';
    }

    const done = (arc) => store.get(arc + ':done', []);

    function progressText(arc, total) {
        const n = done(arc).length;
        if (total === 0) return '';
        if (n === 0) return total + ' bab · belum bermula';
        if (n >= total) return 'Semua ' + total + ' pelita telah menyala';
        return n + ' daripada ' + total + ' pelita menyala';
    }

    // ---------- Halaman utama ----------
    document.querySelectorAll('.arc-card[data-arc]').forEach((card) => {
        const arc = card.dataset.arc;
        const total = parseInt(card.dataset.total, 10) || 0;
        const n = done(arc).length;
        card.querySelectorAll('.lamp-dot').forEach((dot, i) => dot.classList.toggle('is-lit', i < n));
        const text = card.querySelector('[data-progress-text]');
        if (text && n > 0) text.textContent = progressText(arc, total);
    });

    // ---------- Peta arka ----------
    const arcHead = document.querySelector('[data-arc-start]');
    if (arcHead) {
        const arc = arcHead.dataset.arcStart;
        const elapsed = daysSince(startOf(arc));
        const finished = done(arc);

        document.querySelectorAll('.journey-stop').forEach((stop) => {
            const id = parseInt(stop.dataset.chapter, 10);
            const unlock = parseInt(stop.dataset.unlock, 10) || 0;
            stop.classList.toggle('is-done', finished.includes(id));
            if (unlock > elapsed) {
                stop.classList.add('is-locked');
                const lock = stop.querySelector('[data-lock-text]');
                lock.textContent = lockText(unlock - elapsed);
                lock.hidden = false;
            }
        });

        const text = document.querySelector('[data-progress-text][data-arc]');
        if (text) text.textContent = progressText(arc, parseInt(text.dataset.total, 10) || 0);
    }

    // ---------- Bab ----------
    const chapter = document.querySelector('article.chapter');
    if (!chapter) return;

    const arc = chapter.dataset.arc;
    const chapterId = parseInt(chapter.dataset.chapter, 10);
    const unlock = parseInt(chapter.dataset.unlock, 10) || 0;
    const elapsed = daysSince(startOf(arc));

    if (unlock > elapsed) {
        chapter.querySelector('[data-chapter-body]').hidden = true;
        const panel = chapter.querySelector('[data-locked-panel]');
        panel.hidden = false;
        panel.querySelector('[data-locked-text]').textContent =
            lockText(unlock - elapsed) + '. Satu bab sehari — sabar menanti juga sebahagian daripada perjalanan.';
        return;
    }

    // Pilihan Yusuf
    const choice = chapter.querySelector('[data-choice]');
    if (choice) {
        const key = 'choice:' + chapterId;
        const show = (opt) => {
            choice.querySelectorAll('[data-option]').forEach((b) => {
                b.classList.toggle('is-picked', b.dataset.option === opt);
                b.disabled = true;
            });
            choice.querySelectorAll('[data-outcome]').forEach((p) => { p.hidden = p.dataset.outcome !== opt; });
        };
        const saved = store.get(key, null);
        if (saved) show(saved);
        choice.querySelectorAll('[data-option]').forEach((b) => b.addEventListener('click', () => {
            store.set(key, b.dataset.option);
            show(b.dataset.option);
        }));
    }

    // Fakta atau Mitos?
    chapter.querySelectorAll('.quiz-item').forEach((item) => {
        const answer = item.dataset.answer;
        item.querySelectorAll('[data-guess]').forEach((b) => b.addEventListener('click', () => {
            item.querySelectorAll('[data-guess]').forEach((x) => {
                x.disabled = true;
                if (x.dataset.guess === answer) x.classList.add('is-correct');
            });
            if (b.dataset.guess !== answer) b.classList.add('is-wrong');
            item.querySelector('.quiz-reveal').hidden = false;
        }));
    });

    // Nyalakan pelita
    const finish = chapter.querySelector('[data-finish]');
    const button = finish.querySelector('[data-complete]');
    const after = finish.querySelector('[data-after-complete]');
    const journal = finish.querySelector('[data-journal]');
    const journalKey = 'journal:' + chapterId;

    function markDone(animate) {
        finish.classList.add('is-done');
        finish.querySelector('[data-finish-title]').textContent = 'Pelita bab ini telah menyala';
        button.hidden = true;
        after.hidden = false;
        if (journal) journal.value = store.get(journalKey, '');
        if (animate && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            after.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    if (done(arc).includes(chapterId)) markDone(false);

    button.addEventListener('click', () => {
        const list = done(arc);
        if (!list.includes(chapterId)) list.push(chapterId);
        store.set(arc + ':done', list);
        finish.querySelectorAll('[data-card]').forEach((c) => store.set('card:' + c.dataset.card, Date.now()));
        markDone(true);
    });

    if (journal) {
        let timer;
        const saved = finish.querySelector('[data-journal-saved]');
        journal.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                store.set(journalKey, journal.value);
                saved.hidden = false;
            }, 400);
        });
    }
})();
