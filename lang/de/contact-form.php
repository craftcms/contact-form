<?php

declare(strict_types=1);

return [
    // model
    'To Email' => 'Empfängeradresse',
    'Your Name' => 'Ihr Name',
    'Your Email' => 'Ihre Email',
    'Message' => 'Nachricht',
    'Subject' => 'Betreff',

    // email body
    'Email' => 'E-Mail',
    'Name' => 'Name',

    // error messages
    'There was a problem with your submission, please check the form and try again!' => 'Es gab ein Problem beim Abschicken des Formulars, bitte Eingaben prüfen und erneut versuchen!',

    // plugin settings
    'The email address(es) that the contact form will send to. Separate multiple email addresses with commas.' => 'Die Email-Adresse(n) an welche das Kontaktformular senden wird. Trennen Sie mehrere Email-Adressen mit Kommas.',
    'Sender Text' => 'Absendertext',
    'Text that will be prepended to the email’s From Name to inform who the Contact Form actually was sent by.' => 'Text, welcher dem Absendernamen der Email vorangestellt wird, um aufzuzeigen von wem das Kontaktformular abgesendet wurde.',
    'On behalf of' => 'Mitteilung von',
    'Subject Text' => 'Betreffzeile',
    'Text that will be prepended to the email’s Subject to flag that it comes from the Contact Form.' => 'Text, welcher der Betreffzeile vorangestellt wird, um aufzuzeigen dass die Mail vom Kontaktformular stammt',
    'Allow attachments?' => 'Anlagen erlauben?',
    'Success Flash Message' => 'Erfolgsnachricht',
    'The flash message displayed after successfully sending a message.' => 'Die Statusnachricht welche angezeigt wird, wenn eine Nachricht erfolgreich versendet wurde.',
    'Your message has been sent.' => 'Ihre Nachricht wurde versendet.',

    // added for 4.0
    'An attachment could not be uploaded.' => 'Eine Anlage konnte nicht hochgeladen werden.',
    'Attachment' => 'Anlage',
    '{ext} files (like {filename}) are not allowed.' => '{ext}-Dateien (wie {filename}) sind nicht erlaubt.',
    'Files without an extension (like {filename}) are not allowed.' => 'Dateien ohne Dateiendung (wie {filename}) sind nicht erlaubt.',
    '{fields} can’t be submitted with this form.' => '{fields} kann mit diesem Formular nicht gesendet werden.',
    'You’ve sent too many messages. Please try again in a minute.' => 'Sie haben zu viele Nachrichten gesendet. Bitte versuchen Sie es in einer Minute erneut.',
    'New message from {siteName}' => 'Neue Nachricht von {siteName}',
    'Submission summary' => 'Zusammenfassung der Einsendung',
    'This is being overridden by the {setting} config setting in your {file} config file.' => 'Dies wird durch die Einstellung {setting} in Ihrer Konfigurationsdatei {file} überschrieben.',
];
