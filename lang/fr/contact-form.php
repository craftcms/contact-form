<?php

declare(strict_types=1);

return [
    // model
    'To Email' => 'Destinataire',
    'Your Name' => 'Votre nom',
    'Your Email' => 'Votre email',
    'Message' => 'Message',
    'Subject' => 'Sujet',

    // email body
    'Email' => 'E-mail',
    'Name' => 'Nom',

    // error messages
    'There was a problem with your submission, please check the form and try again!' => 'Une erreur est survenue lors de la soumission, vérifiez le formulaire et essayez à nouveau !',

    // plugin settings
    'The email address(es) that the contact form will send to. Separate multiple email addresses with commas.' => 'La ou les adresses qui recevront les emails. Séparez les adresses multiples par des virgules.',
    'Sender Text' => 'Texte expéditeur',
    'Text that will be prepended to the email’s From Name to inform who the Contact Form actually was sent by.' => "Texte qui sera ajouté avant le nom de l'expéditeur pour indiquer par qui le formulaire de contact a été envoyé.",
    'On behalf of' => 'De la part de',
    'Subject Text' => 'Texte sujet',
    'Text that will be prepended to the email’s Subject to flag that it comes from the Contact Form.' => "Texte qui sera ajouté au début du sujet du mail pour indiquer qu'il a été envoyé depuis le formulaire de contact.",
    'Allow attachments?' => 'Autoriser les pièces jointes ?',
    'Success Flash Message' => 'Message de succès',
    'The flash message displayed after successfully sending a message.' => 'Le message affiché après avoir envoyé un message avec succès.',
    'Your message has been sent.' => 'Votre message a été envoyé.',

    // added for 4.0
    'An attachment could not be uploaded.' => 'Une pièce jointe n’a pas pu être téléversée.',
    'Attachment' => 'Pièce jointe',
    '{ext} files (like {filename}) are not allowed.' => 'Les fichiers {ext} (comme {filename}) ne sont pas autorisés.',
    'Files without an extension (like {filename}) are not allowed.' => 'Les fichiers sans extension (comme {filename}) ne sont pas autorisés.',
    '{fields} can’t be submitted with this form.' => '{fields} ne peut pas être envoyé avec ce formulaire.',
    'You’ve sent too many messages. Please try again in a minute.' => 'Vous avez envoyé trop de messages. Veuillez réessayer dans une minute.',
    'New message from {siteName}' => 'Nouveau message de {siteName}',
    'Submission summary' => 'Résumé de l’envoi',
    'This is being overridden by the {setting} config setting in your {file} config file.' => 'Ce paramètre est remplacé par le paramètre {setting} de votre fichier de configuration {file}.',
];
