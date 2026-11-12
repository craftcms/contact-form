<?php

declare(strict_types=1);

return [
    // model
    'To Email' => 'Destinatario',
    'Your Name' => 'Su Nombre',
    'Your Email' => 'Su email',
    'Message' => 'Mensaje',
    'Subject' => 'Asunto',

    // email body
    'Email' => 'Correo electrónico',
    'Name' => 'Nombre',

    // error messages
    'There was a problem with your submission, please check the form and try again!' => 'Hubo un problema con su envío, por favor revise el formulario y vuelva a intentarlo!',

    // plugin settings
    'The email address(es) that the contact form will send to. Separate multiple email addresses with commas.' => 'La(s) direccion(es) de email a las que se enviará el email de contacto. Separa las distintas direcciones de email con comas.',
    'Sender Text' => 'Texto del remitente',
    'Text that will be prepended to the email’s From Name to inform who the Contact Form actually was sent by.' => 'Texto que se antepondrá al Nombre del remitente del email para informar a quién se envió realmente el correo de contacto.',
    'On behalf of' => 'A nombre de',
    'Subject Text' => 'Texto del asunto',
    'Text that will be prepended to the email’s Subject to flag that it comes from the Contact Form.' => 'Texto que se agregará previamente al asunto del correo para informar que proviene del formulario de contacto.',
    'Allow attachments?' => 'Permitir adjuntos?',
    'Success Flash Message' => 'Mensaje flash para correos enviados',
    'The flash message displayed after successfully sending a message.' => 'El mensaje flash que se muestra después de enviar un mensaje correctamente.',
    'Your message has been sent.' => 'Su mensaje ha sido enviado.',

    // added for 4.0
    'An attachment could not be uploaded.' => 'No se pudo subir un archivo adjunto.',
    'Attachment' => 'Archivo adjunto',
    '{ext} files (like {filename}) are not allowed.' => 'No se permiten archivos {ext} (como {filename}).',
    'Files without an extension (like {filename}) are not allowed.' => 'No se permiten archivos sin extensión (como {filename}).',
    '{fields} can’t be submitted with this form.' => '{fields} no se puede enviar con este formulario.',
    'You’ve sent too many messages. Please try again in a minute.' => 'Has enviado demasiados mensajes. Inténtalo de nuevo en un minuto.',
    'New message from {siteName}' => 'Nuevo mensaje de {siteName}',
    'Submission summary' => 'Resumen del envío',
    'This is being overridden by the {setting} config setting in your {file} config file.' => 'Esto se está sobrescribiendo con el ajuste {setting} de tu archivo de configuración {file}.',
];
