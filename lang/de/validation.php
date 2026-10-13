<?php

/*
 * Deutsche Validierungsmeldungen für die in CleanStart verwendeten Regeln.
 * Nicht übersetzte Regeln fallen automatisch auf Englisch zurück.
 */
return [
    'accepted' => ':Attribute muss bestätigt werden.',
    'after_or_equal' => ':Attribute muss am oder nach dem :date liegen.',
    'current_password' => 'Das Passwort ist nicht korrekt.',
    'alpha_dash' => ':Attribute darf nur Buchstaben, Ziffern, Binde- und Unterstriche enthalten.',
    'array' => ':Attribute muss eine Liste sein.',
    'before_or_equal' => ':Attribute darf nicht in der Zukunft liegen.',
    'boolean' => ':Attribute muss wahr oder falsch sein.',
    'confirmed' => 'Die Bestätigung von :attribute stimmt nicht überein.',
    'date' => ':Attribute ist kein gültiges Datum.',
    'distinct' => ':Attribute enthält einen doppelten Wert.',
    'email' => ':Attribute muss eine gültige E-Mail-Adresse sein.',
    'enum' => 'Der gewählte Wert für :attribute ist ungültig.',
    'integer' => ':Attribute muss eine ganze Zahl sein.',
    'lowercase' => ':Attribute darf nur Kleinbuchstaben enthalten.',
    'max' => [
        'array' => ':Attribute darf höchstens :max Einträge haben.',
        'string' => ':Attribute darf höchstens :max Zeichen lang sein.',
    ],
    'min' => [
        'string' => ':Attribute muss mindestens :min Zeichen lang sein.',
    ],
    'password' => [
        'letters' => ':Attribute muss mindestens einen Buchstaben enthalten.',
        'numbers' => ':Attribute muss mindestens eine Ziffer enthalten.',
        'uncompromised' => ':Attribute ist in einem Datenleck aufgetaucht. Bitte wähle ein anderes.',
    ],
    'regex' => 'Das Format von :attribute ist ungültig.',
    'required' => ':Attribute ist ein Pflichtfeld.',
    'required_with' => ':Attribute wird benötigt, wenn :values ausgefüllt ist.',
    'string' => ':Attribute muss ein Text sein.',
    'unique' => ':Attribute ist bereits vergeben.',

    'attributes' => [
        'username' => 'Benutzername',
        'email' => 'E-Mail-Adresse',
        'password' => 'Passwort',
        'sobriety_start_at' => 'Clean seit',
        'trusted_contact_name' => 'Name der Vertrauensperson',
        'trusted_contact_phone' => 'Telefonnummer',
    ],
];
