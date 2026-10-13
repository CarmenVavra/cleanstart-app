<x-layouts.page title="Impressum" heading="Impressum" :back="$back" :back-label="$backLabel">
    <x-card aria-labelledby="imprint-operator-h" class="text-base/[1.5]">
        <h2 id="imprint-operator-h" class="text-[19px] font-bold">Anbieter und Medieninhaber</h2>
        <address class="not-italic">
            <x-legal.value :value="$legal['operator']" env="LEGAL_OPERATOR" /><br>
            <x-legal.value :value="$legal['street']" env="LEGAL_STREET" /><br>
            <x-legal.value :value="$legal['zip']" env="LEGAL_ZIP" />
            <x-legal.value :value="$legal['city']" env="LEGAL_CITY" /><br>
            <x-legal.value :value="$legal['country']" env="LEGAL_COUNTRY" />
        </address>

        <h2 class="text-[19px] font-bold">Kontakt</h2>
        <p>
            E-Mail:
            @if (filled($legal['email']))
                <a href="mailto:{{ $legal['email'] }}">{{ $legal['email'] }}</a>
            @else
                <x-legal.value env="LEGAL_EMAIL" />
            @endif
            @if (filled($legal['phone']))
                <br>Telefon: {{ $legal['phone'] }}
            @endif
        </p>

        @if (filled($legal['register']))
            <h2 class="text-[19px] font-bold">Register & Steuernummer</h2>
            <p>{{ $legal['register'] }}</p>
        @endif

        <h2 class="text-[19px] font-bold">Hinweis</h2>
        <p class="text-ink-muted">
            CleanStart ist ein digitaler Begleiter und ersetzt keine ärztliche, psychotherapeutische oder
            suchtmedizinische Behandlung. In akuten Notfällen wähle bitte den Notruf 112.
        </p>
    </x-card>
</x-layouts.page>
