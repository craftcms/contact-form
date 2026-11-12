<?php

declare(strict_types=1);

return [
    // model
    'To Email' => 'Destinatario',
    'Your Name' => 'Nominativo',
    'Your Email' => 'Email',
    'Message' => 'Messaggio',
    'Subject' => 'Oggetto',

    // email body
    'Email' => 'Email',
    'Name' => 'Nome',

    // error messages
    'There was a problem with your submission, please check the form and try again!' => "Si è verificato un problema con l'invio, controlla il modulo e riprova!",

    // plugin settings
    'The email address(es) that the contact form will send to. Separate multiple email addresses with commas.' => 'Gli indirizzi che riceveranno le email. Separa gli indirizzi multipli con le virgole.',
    'Sender Text' => 'Testo del mittente',
    'Text that will be prepended to the email’s From Name to inform who the Contact Form actually was sent by.' => "Testo che verrà anteposto al mittente dell'email per indicare da chi è stato effettivamente inviato il modulo contatti.",
    'On behalf of' => 'Per conto di',
    'Subject Text' => 'Oggetto',
    'Text that will be prepended to the email’s Subject to flag that it comes from the Contact Form.' => "Testo che verrà anteposto all'oggetto dell'email per segnalare che proviene dal modulo contatti.",
    'Allow attachments?' => 'Consentire allegati?',
    'Success Flash Message' => 'Messaggio di successo',
    'The flash message displayed after successfully sending a message.' => 'Il messaggio flash visualizzato dopo aver inviato con successo un messaggio.',
    'Your message has been sent.' => 'Il tuo messaggio è stato inviato.',

    // added for 4.0
    'An attachment could not be uploaded.' => 'Non è stato possibile caricare un allegato.',
    'Attachment' => 'Allegato',
    '{ext} files (like {filename}) are not allowed.' => 'I file {ext} (come {filename}) non sono consentiti.',
    'Files without an extension (like {filename}) are not allowed.' => 'I file senza estensione (come {filename}) non sono consentiti.',
    '{fields} can’t be submitted with this form.' => '{fields} non può essere inviato con questo modulo.',
    'You’ve sent too many messages. Please try again in a minute.' => 'Hai inviato troppi messaggi. Riprova tra un minuto.',
    'New message from {siteName}' => 'Nuovo messaggio da {siteName}',
    'Submission summary' => 'Riepilogo dell’invio',
    'This is being overridden by the {setting} config setting in your {file} config file.' => 'Questa impostazione è sovrascritta dall’impostazione {setting} nel file di configurazione {file}.',
];
