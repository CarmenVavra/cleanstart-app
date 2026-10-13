/**
 * Alpine-Komponenten des CleanStart-Dashboards.
 *
 * - `dashboard`: Live-Counter, Meilensteine, Begrüßung, Notfall-Sheet und Atemübung (rein clientseitig).
 * - `checkin`:   UI-Zustand des Daily Check-ins; Stimmung und Trigger sind per $wire.entangle
 *                mit der Livewire-Komponente synchronisiert und werden beim Speichern übertragen.
 */

const DAY_MS = 864e5;
const HOUR_MS = 36e5;
const MINUTE_MS = 6e4;
/** 4-7-8-Atmung: Kreisgröße (scale) am Ende jeder Phase, Dauer in Sekunden. */
const BREATH_PHASES = [
    { label: 'Einatmen …', seconds: 4, scale: 1 },
    { label: 'Halten …', seconds: 7, scale: 1 },
    { label: 'Ausatmen …', seconds: 8, scale: 0.55 },
];
const BREATH_MIN_SCALE = 0.55;

const pad = (n) => String(n).padStart(2, '0');

export function dashboard(config) {
    return {
        start: new Date(config.start).getTime(),
        milestones: config.milestones,
        now: Date.now(),
        sos: false,
        breathing: false,
        phaseIndex: 0,
        remaining: 0,
        circleScale: BREATH_MIN_SCALE,
        circleDuration: 0,
        tickTimer: null,
        breathTimer: null,

        init() {
            this.tickTimer = setInterval(() => (this.now = Date.now()), 1000);
        },

        destroy() {
            clearInterval(this.tickTimer);
            clearInterval(this.breathTimer);
        },

        // --- Counter -------------------------------------------------------
        get elapsed() {
            return Number.isNaN(this.start) ? 0 : Math.max(0, this.now - this.start);
        },
        get fracDays() {
            return this.elapsed / DAY_MS;
        },
        get days() {
            return Math.floor(this.elapsed / DAY_MS);
        },
        get hours() {
            return Math.floor(this.elapsed / HOUR_MS) % 24;
        },
        get minutes() {
            return Math.floor(this.elapsed / MINUTE_MS) % 60;
        },
        get hoursPadded() {
            return pad(this.hours);
        },
        get minutesPadded() {
            return pad(this.minutes);
        },
        get daysWord() {
            return this.days === 1 ? 'Tag' : 'Tage';
        },
        get counterAria() {
            return `${this.days} ${this.daysWord}, ${this.hours} Stunden und ${this.minutes} Minuten clean`;
        },

        // --- Meilensteine --------------------------------------------------
        isUnlocked(days) {
            return this.fracDays >= days;
        },
        get unlockedCount() {
            return this.milestones.filter((m) => this.isUnlocked(m.days)).length;
        },
        get nextIndex() {
            return this.milestones.findIndex((m) => this.fracDays < m.days);
        },
        get hasNext() {
            return this.nextIndex >= 0;
        },
        get progress() {
            if (!this.hasNext) return 100;
            const prev = this.nextIndex > 0 ? this.milestones[this.nextIndex - 1].days : 0;
            const next = this.milestones[this.nextIndex].days;
            return Math.round(((this.fracDays - prev) / (next - prev)) * 100);
        },
        get progressWidth() {
            return `${Math.max(3, this.progress)}%`;
        },
        get nextText() {
            if (!this.hasNext) return '';
            const next = this.milestones[this.nextIndex];
            if (next.days === 1) {
                return `Noch ${24 - Math.floor(this.fracDays * 24)} Std. bis „${next.title}“`;
            }
            const left = Math.ceil(next.days - this.fracDays);
            return `Noch ${left} ${left === 1 ? 'Tag' : 'Tage'} bis „${next.title}“`;
        },

        // --- Kopfzeile -----------------------------------------------------
        get greeting() {
            const hour = new Date(this.now).getHours();
            if (hour < 11) return 'Guten Morgen';
            if (hour < 18) return 'Guten Tag';
            return 'Guten Abend';
        },
        get todayLabel() {
            return new Date(this.now).toLocaleDateString('de-DE', { weekday: 'long', day: 'numeric', month: 'long' });
        },

        // --- Notfall-Sheet & Atemübung --------------------------------------
        openSos() {
            this.sos = true;
        },
        closeSos() {
            this.stopBreathing();
            this.sos = false;
        },
        get breathPhase() {
            return BREATH_PHASES[this.phaseIndex].label;
        },
        toggleBreath() {
            if (this.breathing) {
                this.stopBreathing();
                return;
            }
            this.circleScale = BREATH_MIN_SCALE;
            this.circleDuration = 0;
            this.breathing = true;
            this.enterBreathPhase(0);
            this.breathTimer = setInterval(() => this.tickBreath(), 1000);
        },
        tickBreath() {
            this.remaining -= 1;
            if (this.remaining <= 0) {
                this.enterBreathPhase((this.phaseIndex + 1) % BREATH_PHASES.length);
            }
        },
        enterBreathPhase(index) {
            const phase = BREATH_PHASES[index];
            this.phaseIndex = index;
            this.remaining = phase.seconds;
            // Erst nach dem Rendern setzen, damit die CSS-Transition über die volle Phasendauer läuft.
            requestAnimationFrame(() =>
                requestAnimationFrame(() => {
                    if (!this.breathing) return;
                    this.circleDuration = phase.seconds;
                    this.circleScale = phase.scale;
                }),
            );
        },
        stopBreathing() {
            clearInterval(this.breathTimer);
            this.breathTimer = null;
            this.breathing = false;
            this.phaseIndex = 0;
            this.remaining = 0;
        },
    };
}

export function checkin(state) {
    return {
        mood: state.mood,
        triggers: state.triggers,
        tried: false,

        get hint() {
            if (this.mood !== null) return '';
            return this.tried ? 'Bitte wähle zuerst deine Stimmung.' : 'Dauert nur ein paar Sekunden.';
        },

        // --- Stimmung (Radiogroup mit Roving-Tabindex) -----------------------
        isMood(value) {
            return this.mood === value;
        },
        isDimmed(value) {
            return this.mood !== null && this.mood !== value;
        },
        moodTabindex(value) {
            return (this.mood ?? 1) === value ? 0 : -1;
        },
        selectMood(value) {
            this.mood = value;
            this.tried = false;
        },
        moveMood(delta) {
            const current = this.mood ?? (delta > 0 ? 0 : 6);
            const next = ((current - 1 + delta + 5) % 5) + 1;
            this.selectMood(next);
            this.$nextTick(() => this.$root.querySelector(`[data-mood="${next}"]`)?.focus());
        },

        // --- Trigger -------------------------------------------------------
        hasTrigger(value) {
            return this.triggers.includes(value);
        },
        toggleTrigger(value) {
            this.triggers = this.hasTrigger(value)
                ? this.triggers.filter((t) => t !== value)
                : [...this.triggers, value];
        },

        submit() {
            if (this.mood === null) {
                this.tried = true;
                return;
            }
            this.$wire.save();
        },
    };
}
