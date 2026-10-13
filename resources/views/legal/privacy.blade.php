<x-layouts.page title="Datenschutz" heading="Datenschutzerklärung" :back="$back" :back-label="$backLabel">
    <x-card aria-labelledby="privacy-intro-h" class="text-base/[1.5]">
        <h2 id="privacy-intro-h" class="text-[19px] font-bold">Kurz gesagt</h2>
        <ul class="flex list-disc flex-col gap-1.5 pl-5">
            <li>Du nutzt CleanStart mit einem <strong>Pseudonym</strong> – ein echter Name ist nicht nötig.</li>
            <li>Notizen, Reflexionen, dein Notfallplan und deine Vertrauensperson werden <strong>verschlüsselt</strong> gespeichert.</li>
            <li>Kein Tracking, keine Werbung, keine Analyse-Tools, keine Schriften oder Skripte von Drittanbietern.</li>
            <li>Du kannst <strong>alle deine Daten herunterladen</strong> und dein <strong>Konto jederzeit löschen</strong> (im Profil).</li>
        </ul>
    </x-card>

    <x-card aria-labelledby="privacy-controller-h" class="text-base/[1.5]">
        <h2 id="privacy-controller-h" class="text-[19px] font-bold">1. Verantwortlicher</h2>
        <address class="not-italic">
            <x-legal.value :value="$legal['operator']" env="LEGAL_OPERATOR" /><br>
            <x-legal.value :value="$legal['street']" env="LEGAL_STREET" /><br>
            <x-legal.value :value="$legal['city']" env="LEGAL_CITY" /><br>
            E-Mail: <x-legal.value :value="$legal['email']" env="LEGAL_EMAIL" />
        </address>

        <h2 class="text-[19px] font-bold">2. Welche Daten wir verarbeiten</h2>
        <ul class="flex list-disc flex-col gap-1.5 pl-5">
            <li><strong>Konto:</strong> Pseudonym, E-Mail-Adresse (nur für Anmeldung und Passwort-Zurücksetzen), Passwort (nur als sicherer Hash), Land (für die passenden Hilfsnummern), Zeitpunkt deiner Einwilligung.</li>
            <li><strong>Abstinenz:</strong> Startzeitpunkt deiner aktuellen Serie, frühere Serien und – freiwillig – deine Reflexion und Trigger bei einem Neustart.</li>
            <li><strong>Daily Check-in:</strong> Stimmung (1–5), ausgewählte Trigger und optional eine persönliche Notiz.</li>
            <li><strong>Craving-Timer:</strong> der Zeitpunkt, wenn du einen überstandenen Suchtdruck speicherst. Ein laufender Timer wird nur in deinem Browser-Tab (sessionStorage) vermerkt, damit er beim Neuladen weiterläuft.</li>
            <li><strong>Notfallplan & Vertrauensperson:</strong> deine Einträge sowie Name und Telefonnummer einer Vertrauensperson, falls du sie angibst.</li>
            <li><strong>Technisch notwendig:</strong> ein Sitzungs-Cookie und ein Sicherheits-Token (CSRF), damit die Anmeldung funktioniert. Es werden keine Marketing- oder Analyse-Cookies gesetzt.</li>
        </ul>

        <h2 class="text-[19px] font-bold">3. Zweck und Rechtsgrundlage</h2>
        <p>
            Wir verarbeiten deine Daten ausschließlich, um dir die Funktionen von CleanStart bereitzustellen
            (Art. 6 Abs. 1 lit. b DSGVO). Angaben zu Stimmung, Triggern und Abstinenz sind Gesundheitsdaten;
            diese verarbeiten wir nur mit deiner ausdrücklichen Einwilligung (Art. 9 Abs. 2 lit. a DSGVO), die du
            bei der Registrierung erteilst. Du kannst sie jederzeit mit Wirkung für die Zukunft widerrufen,
            indem du dein Konto löschst.
        </p>

        <h2 class="text-[19px] font-bold">4. Speicherdauer</h2>
        <p>
            Deine Daten bleiben gespeichert, solange dein Konto besteht. Löschst du dein Konto, werden alle
            zugehörigen Daten sofort und endgültig entfernt.
        </p>

        <h2 class="text-[19px] font-bold">5. Hosting und Empfänger</h2>
        <p>
            CleanStart wird betrieben bei: <x-legal.value :value="$legal['hoster']" env="LEGAL_HOSTER" />.
            Der Hosting-Anbieter verarbeitet Daten nur in unserem Auftrag (Art. 28 DSGVO). Beim Aufruf der Seite
            werden technisch bedingt Server-Logdaten (z. B. IP-Adresse, Zeitpunkt) kurzzeitig gespeichert.
            Eine Weitergabe an sonstige Dritte findet nicht statt.
        </p>
        <p>
            Die Anruf-Buttons (z. B. Telefonseelsorge, Vertrauensperson, Notruf) öffnen lediglich die Telefon-App
            deines Geräts. CleanStart erfährt nicht, ob und mit wem du telefonierst.
        </p>

        <h2 class="text-[19px] font-bold">6. Deine Rechte</h2>
        <p>
            Du hast das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung,
            Datenübertragbarkeit und Widerspruch (Art. 15–21 DSGVO). Datenexport und Kontolöschung kannst du
            direkt im Profil selbst durchführen. Außerdem kannst du dich bei der zuständigen
            Datenschutz-Aufsichtsbehörde beschweren:
            <x-legal.value :value="$legal['supervisory_authority']" env="LEGAL_SUPERVISORY_AUTHORITY" />.
        </p>

        <h2 class="text-[19px] font-bold">7. Sicherheit</h2>
        <p>
            Die Übertragung erfolgt verschlüsselt (HTTPS). Sensible Freitexte und Kontaktdaten werden in der
            Datenbank zusätzlich verschlüsselt gespeichert, sodass sie auch bei einem Datenleck nicht im Klartext
            lesbar sind.
        </p>
    </x-card>
</x-layouts.page>
