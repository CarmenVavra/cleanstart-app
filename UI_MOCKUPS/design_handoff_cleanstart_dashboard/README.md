# Handoff: CleanStart – Haupt-Dashboard

## Overview
Mobile-first Dashboard für **CleanStart**, eine Web-App zur Rückfallprävention bei Suchterkrankungen. Enthält: Notfall-Button mit Hilfe-Sheet, Clean-Counter (Tage/Stunden/Minuten), Meilenstein-Badges und ein Daily Check-in (Stimmung 1–5 + Trigger-Tags).

**Ziel-Stack:** Laravel + Livewire + Tailwind CSS.

## About the Design Files
`CleanStart Dashboard.dc.html` ist eine **Design-Referenz in HTML** (Prototyp mit Inline-Styles und einer kleinen JS-Logikklasse), kein Produktionscode. Aufgabe: das Design in Laravel/Livewire/Blade mit Tailwind-Klassen nachbauen, entsprechend den bestehenden Patterns des Projekts. Öffnen im Browser: Datei zusammen mit `support.js` im selben Ordner ablegen und `CleanStart Dashboard.dc.html` öffnen (Runtime wird von dort geladen).

## Fidelity
**High-fidelity.** Farben, Typografie, Abstände, Zustände und Interaktionen sind final und sollen pixelgenau umgesetzt werden.

---

## Screen: Dashboard

### Layout
- Container: `max-width: 440px`, zentriert, `padding: 20px 20px 40px`, vertikaler Flex-Stack mit `gap: 20px`, `min-height: 100vh`.
- Seitenhintergrund `#E9F0EE`.
- Reihenfolge: Header → Notfall-Button → Counter-Karte → Meilensteine-Karte → Check-in-Karte.
- Tailwind-Äquivalent: `mx-auto max-w-[440px] px-5 pt-5 pb-10 flex flex-col gap-5 min-h-screen`.

### 1. Header
- Flex row, `justify-between`, `align-items:center`, gap 12px, padding-top 4px.
- Links (Spalte, gap 2px):
  - Wortmarke: 10×10px Kreis `#3F7D6B` + „CleanStart“, 14px/700, letter-spacing .04em, Farbe `#3F7D6B`, gap 8px.
  - H1: „{Begrüßung}, Alex“ – 24px/700, line-height 1.2, `#1E2B2A`. Begrüßung nach Uhrzeit: < 11 Uhr „Guten Morgen“, < 18 Uhr „Guten Tag“, sonst „Guten Abend“.
  - Datum: `toLocaleDateString('de-DE', {weekday:'long', day:'numeric', month:'long'})` → z. B. „Dienstag, 13. Oktober“, 15px, `#4D5F5C`.
- Rechts: Profil-Button 48×48px, rund, Rand 2px `#CBD8D4`, BG `#FFFFFF`, Initial „A“ 18px/700 `#23463D`. `aria-label="Profil öffnen"`.

### 2. Notfall-Button
- Wrapper `position:relative`, margin-top 4px.
- Button: volle Breite, `min-height: 96px`, `border-radius: 24px`, BG `#C2362B` (hover `#AD2E24`, active `scale(.98)`), Text `#FFFFFF`, padding 16px 20px, Shadow `0 10px 24px -10px rgba(194,54,43,.7)`, Fokus `outline: 4px solid #1E2B2A; outline-offset: 4px`.
- Inhalt (Flex, zentriert, gap 16px):
  - Icon-Kreis 52×52px, BG `rgba(255,255,255,.18)`, Rand 2px `rgba(255,255,255,.55)`, „!“ 28px/700 (`aria-hidden`).
  - Text-Spalte: „NOTFALL“ 30px/700, letter-spacing .08em, line-height 1; darunter „Sofort Hilfe holen“ 16px, opacity .95.
- `aria-haspopup="dialog"`, `aria-label="Notfall – sofort Hilfe holen"`.
- **Puls:** zwei absolut positionierte Layer (`inset:0`, radius 24px, BG `#C2362B`, `z-index:0`, `pointer-events:none`) hinter dem Button, Animation `cs-pulse 2.4s ease-out infinite`, zweiter Layer mit `1.2s` Delay:
  ```css
  @keyframes cs-pulse { 0% { transform: scale(1,1); opacity:.5 } 100% { transform: scale(1.06,1.45); opacity:0 } }
  ```
- Klick öffnet das **Notfall-Sheet** (siehe unten).

### 3. Counter-Karte („Clean seit“)
- `<section>`, BG `#23463D`, Text `#F2F7F5`, radius 28px, padding 24px 22px 22px, Flex-Spalte gap 18px.
- Kopfzeile (`justify-between`, baseline): H2 „CLEAN SEIT“ 15px/700, uppercase, letter-spacing .08em, `#B9D9CD`; rechts „seit 29. August 2026“ 14px `#B9D9CD` (`de-DE`, day numeric, month long, year numeric).
- Tage: Zahl 84px/700, line-height .9, `tabular-nums`, letter-spacing -.02em + „Tage“/„Tag“ 24px/700 `#D6EBE3`, gap 10px, baseline. `aria-label="{d} Tage, {h} Stunden und {m} Minuten clean"`.
- Kacheln (Flex, gap 10px, je `flex:1`): BG `rgba(255,255,255,.08)`, radius 16px, padding 12px 14px. Wert 30px/700 tabular-nums, zweistellig mit führender Null; Label 14px `#B9D9CD` („Stunden“, „Minuten“, optional „Sekunden“).
- Fortschritt zum nächsten Meilenstein: Track 10px hoch, radius 999px, BG `rgba(255,255,255,.14)`; Füllung `#9CCBB9`, Breite = Fortschritt seit vorherigem Meilenstein (min. 3 %). `role="progressbar"` mit aria-valuenow. Text darunter 15px `#D6EBE3`: „Noch {n} Tage bis „{Meilenstein}““ (Singular „Tag“; vor dem 24h-Meilenstein „Noch {n} Std. bis „24 Stunden““). Ausgeblendet, wenn alle Meilensteine erreicht.
- Update jede Sekunde (oder jede Minute, falls Sekunden aus).

### 4. Meilensteine-Karte
- `<section>`, BG `#FFFFFF`, radius 24px, padding 22px 20px, Shadow `0 1px 2px rgba(30,43,42,.06)`, gap 18px.
- Kopf: H2 „Erreichte Meilensteine“ 19px/700; rechts „{x} von 8“ 15px `#4D5F5C`.
- Grid `repeat(4, minmax(0,1fr))`, gap 16px (Zeile) / 8px (Spalte), als `<ul>`.
- Badge-Item: Spalte, zentriert, gap 8px; `aria-label="{Label} – erreicht | noch gesperrt"`.
  - **Erreicht:** Kreis 64px, BG `oklch(0.5 0.09 {hue})`, Text `#FFFFFF` 17px/700, Halo `box-shadow: 0 0 0 4px oklch(0.93 0.03 {hue})`.
  - **Gesperrt:** Kreis 64px, BG `#EEF1F0`, Rand 2px dashed `#AEBBB8`, Text `#5E6E6B` 17px/700; Schloss-Plakette 24px rund unten rechts (−4px), BG `#5E6E6B`, Rand 2px `#FFFFFF`, weißes Schloss-Icon 10×12.
  - Label darunter 14px, line-height 1.2; `#1E2B2A` (erreicht) / `#5E6E6B` (gesperrt).

| Tage | Kurz | Label | Hue |
|---|---|---|---|
| 1 | 24h | 24 Stunden | 165 |
| 3 | 3 | 3 Tage | 190 |
| 7 | 7 | 1 Woche | 215 |
| 14 | 14 | 2 Wochen | 240 |
| 30 | 30 | 30 Tage | 275 |
| 90 | 90 | 90 Tage | 310 |
| 180 | ½J | 6 Monate | 30 |
| 365 | 1J | 1 Jahr | 70 |

Freigeschaltet, wenn vergangene Zeit (in Tagen, mit Nachkommastellen) ≥ Schwelle.

### 5. Daily Check-in-Karte
- `<section>`, gleiche Kartenstyles wie Meilensteine, gap 20px.
- Kopf: Eyebrow „DAILY CHECK-IN“ 14px/700, uppercase, letter-spacing .08em, `#3F7D6B`; H2 „Wie geht es dir heute?“ 22px/700.

**Stimmung** (`role="radiogroup"`, Flex, gap 6px), 5 Buttons `role="radio"` mit `aria-checked`:
| Wert | Emoji | Label |
|---|---|---|
| 1 | 😣 | Sehr schlecht |
| 2 | 😕 | Schlecht |
| 3 | 😐 | Okay |
| 4 | 🙂 | Gut |
| 5 | 😄 | Sehr gut |
- Button: `flex:1`, min-height 84px, radius 18px, padding 8px 2px, Spalte gap 6px. Emoji 30px; Label 13px, line-height 1.15.
- Normal: BG `#F4F7F6`, Rand 2px transparent, Label 400.
- Ausgewählt: BG `#DCEBE5`, Rand 2px `#3F7D6B`, Label 700.
- Nicht ausgewählt, während ein anderer gewählt ist: Emoji `filter: grayscale(.6) opacity(.7)`.
- Transition background/border-color .15s.

**Trigger** – H3 „Was beeinflusst dich gerade?“ 17px/700, rechts „Optional · mehrere möglich“ 14px `#4D5F5C` (wrap erlaubt). Chips (`role="group"`, Flex-wrap, gap 8px), Buttons mit `aria-pressed`:
`Stress, Einsamkeit, Müdigkeit, Langeweile, Wut, Hunger, Suchtdruck, Soziale Situation`
- Chip: min-height 48px, padding 0 18px, radius 999px, 16px.
- Normal: BG `#F4F7F6`, Rand 2px `#D3DDDA`, Text `#1E2B2A`, 400.
- Aktiv: BG `#2F6487`, Rand `#2F6487`, Text `#FFFFFF`, 700, vorangestelltes „✓“ (gap 6px).

**Speichern**
- Button volle Breite, min-height 56px, radius 16px, 18px/700, Text „Check-in speichern“.
- Ohne Stimmung: BG `#D5DDDA`, Text `#4D5F5C`, `aria-disabled="true"`, cursor not-allowed. Mit Stimmung: BG `#23463D`, Text `#FFFFFF`.
- Hinweiszeile darunter (14px `#4D5F5C`, zentriert, `aria-live="polite"`, min-height 18px):
  - ohne Stimmung: „Dauert nur ein paar Sekunden.“
  - Klick ohne Stimmung: „Bitte wähle zuerst deine Stimmung.“
  - mit Stimmung: leer.

**Gespeichert-Zustand** (ersetzt das Formular, `role="status"`): BG `#DCEBE5`, radius 18px, padding 18px, gap 12px.
- Emoji 36px + „Danke, gespeichert.“ 18px/700 + „Stimmung: {Label}“ 15px `#33504A`.
- Text 15px/1.45 `#33504A`: mit Triggern „Notiert: {Liste}. Gut, dass du hinschaust.“, ohne „Keine Trigger notiert. Schön, dass du dir Zeit genommen hast.“
- „Bearbeiten“-Button: min-height 44px, padding 0 18px, radius 12px, Rand 2px `#3F7D6B`, transparent, `#23463D` 16px/700 → zurück ins Formular (Werte bleiben erhalten).

---

## Notfall-Sheet (Modal)
- Overlay `fixed inset-0`, BG `rgba(20,32,30,.6)`, z-index 50, Inhalt unten zentriert. Klick aufs Overlay oder `Esc` schließt.
- Sheet: `role="dialog" aria-modal="true" aria-labelledby="sos-h"`, max-width 440px, max-height 92vh, scrollbar, BG `#FFFFFF`, radius `28px 28px 0 0`, padding 12px 20px 28px, gap 16px.
- Griff: 44×5px, radius 999px, `#CBD8D4`, zentriert.
- H2 „Du bist nicht allein.“ 26px/700; Text „Atme einmal tief durch. Wähle, was dir jetzt am meisten hilft.“ 17px/1.45 `#4D5F5C`.
- Aktionen (je min-height 72px, radius 18px, padding 14px 18px, Titel 19px/700, Untertitel 15px):
  1. `<a href="tel:08001110111">` BG `#2F6487`, Text weiß – „Telefonseelsorge anrufen“ / „0800 111 0 111 · kostenlos, rund um die Uhr“.
  2. `<a href="tel:…">` BG `#DDE8F1` – „Sabine anrufen“ / „Deine Vertrauensperson“ (**Platzhalter**: Name + Nummer aus Nutzerprofil).
  3. Button BG `#DCEBE5`, `aria-expanded` – „Atemübung starten“ ↔ „Atemübung beenden“ / „Geführt, 4 Sekunden ein – 4 Sekunden aus“.
- Atemübung (bei aktiv): 180×180px Kreis, `radial-gradient(circle, #B9D9CD 0%, #DCEBE5 70%)`, Animation `cs-breathe 8s ease-in-out infinite`; Mitte Text 22px/700 `#23463D`, `aria-live="polite"`, wechselt alle 4s „Einatmen …“ / „Ausatmen …“ (startet mit Einatmen, synchron zum Animationsstart).
  ```css
  @keyframes cs-breathe { 0%,100% { transform: scale(.55) } 50% { transform: scale(1) } }
  ```
- Text 15px `#4D5F5C`: „Bei akuter Gefahr für dich oder andere: **Notruf 112**“ (`tel:112`, 700, `#C2362B`).
- „Schließen“-Button: min-height 52px, radius 14px, Rand 2px `#CBD8D4`, BG weiß, 17px/700. Schließen beendet auch die Atemübung.
- Empfehlung für Produktion: Fokus beim Öffnen ins Sheet setzen, Fokus-Trap, beim Schließen zurück auf den Notfall-Button.

## Interactions & Behavior (Zusammenfassung)
- Counter-Tick: 1s-Intervall (clientseitig, z. B. Alpine.js `x-init` mit `setInterval`; kein Livewire-Polling nötig).
- Puls und Atemkreis: reine CSS-Animationen.
- `@media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important } }`
- Fokus-Stil überall: `outline: 3px solid #2F6487; outline-offset: 2–3px` (Notfall-Button: 4px `#1E2B2A`).
- Alle Tippflächen ≥ 44px.

## State Management
| State | Typ | Zweck |
|---|---|---|
| `startDate` | datetime (User) | Basis für Counter + Meilensteine |
| `mood` | int 1–5 \| null | Check-in Stimmung |
| `triggers` | string[] | gewählte Trigger |
| `saved` | bool | Check-in gespeichert (pro Tag) |
| `sos` | bool | Notfall-Sheet offen |
| `breathing`, `phase` | bool, 0/1 | Atemübung |

Vorschlag Livewire: Komponente `Dashboard` mit `$mood`, `$triggers`, `$saved`; `save()` speichert einen `DailyCheckin` (user_id, date, mood, triggers JSON) und setzt `$saved = true`. Wenn heute bereits ein Check-in existiert, initial im Gespeichert-Zustand rendern. UI-only State (`sos`, Atemübung, Counter-Tick) mit Alpine.js.

## Design Tokens
**Farben**
- Seiten-BG `#E9F0EE` · Karte `#FFFFFF` · Fläche hell `#F4F7F6` · Gesperrt-BG `#EEF1F0`
- Text `#1E2B2A` · Text sekundär `#4D5F5C` · Text auf Mint `#33504A` · Gesperrt `#5E6E6B`
- Grün dunkel (Counter, Primär-Button) `#23463D` · Grün `#3F7D6B` · Mint `#DCEBE5` · Mint-Text auf Dunkel `#B9D9CD` / `#D6EBE3` · Fortschritt `#9CCBB9`
- Blau `#2F6487` · Blau hell `#DDE8F1`
- Notfall-Rot `#C2362B` (hover `#AD2E24`)
- Rahmen `#CBD8D4`, `#D3DDDA`, `#AEBBB8` (dashed) · Disabled-BG `#D5DDDA`

**Typografie** – Atkinson Hyperlegible (Google Fonts, 400/700), Fallback `system-ui, sans-serif`. Größen: 84 · 30 · 26 · 24 · 22 · 19 · 18 · 17 · 16 · 15 · 14 · 13 px. Zahlen mit `font-variant-numeric: tabular-nums`.

**Radien** – 999 (Pills) · 28 (Counter, Sheet) · 24 (Karten, Notfall) · 18 (Mood, Sheet-Aktionen) · 16 (Kacheln, Speichern) · 14 · 12.

**Abstände** – 4 · 6 · 8 · 10 · 12 · 16 · 18 · 20 · 22 · 24 px.

**Schatten** – Karte `0 1px 2px rgba(30,43,42,.06)` · Notfall `0 10px 24px -10px rgba(194,54,43,.7)`.

**Tailwind-Config (Vorschlag)**
```js
theme: { extend: {
  fontFamily: { sans: ['"Atkinson Hyperlegible"', 'system-ui', 'sans-serif'] },
  colors: {
    page: '#E9F0EE', ink: { DEFAULT: '#1E2B2A', muted: '#4D5F5C', soft: '#33504A', locked: '#5E6E6B' },
    sage: { 900: '#23463D', 600: '#3F7D6B', 300: '#9CCBB9', 200: '#B9D9CD', 100: '#DCEBE5' },
    ocean: { 700: '#2F6487', 100: '#DDE8F1' },
    sos: { DEFAULT: '#C2362B', hover: '#AD2E24' },
    line: '#CBD8D4', surface: '#F4F7F6',
  },
  keyframes: {
    'cs-pulse': { '0%': { transform: 'scale(1,1)', opacity: '.5' }, '100%': { transform: 'scale(1.06,1.45)', opacity: '0' } },
    'cs-breathe': { '0%,100%': { transform: 'scale(.55)' }, '50%': { transform: 'scale(1)' } },
  },
  animation: { pulse-sos: 'cs-pulse 2.4s ease-out infinite', breathe: 'cs-breathe 8s ease-in-out infinite' },
}}
```

## Assets
Keine Bilder. Emojis als Unicode. Schloss-Icon als Inline-SVG (Bügel-Pfad + Rechteck) – im Projekt gern durch Heroicons `lock-closed` (solid, 12px) ersetzen.

## Files
- `CleanStart Dashboard.dc.html` – Design-Referenz (Template + Logik in einer Datei)
- `support.js` – Runtime, nur zum Öffnen der Referenz im Browser nötig
