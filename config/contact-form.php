<?php

declare(strict_types=1);

/**
 * Contact Form config.
 *
 * Settings in this file override the ones saved from Settings → Contact Form, and lock them in the control panel.
 * Only add `toEmail`, `prependSender`, `prependSubject`, `allowAttachments` or `successFlashMessage` here if you want to manage them in code.
 */

return [
    // How many submissions per minute (per IP address) to allow. `null` disables the limit.
    'rateLimit' => null,

    // The `message[...]` sub-fields to allow, as a list of names (`['Phone', 'Services']`)
    // or a map of Laravel validation rules (`['Phone' => ['nullable', 'string']]`).
    // `null` allows any sub-fields; `[]` allows none besides `message[body]`.
    'allowedMessageFields' => null,
];
