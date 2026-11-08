<?php

declare(strict_types=1);

namespace CraftCms\ContactForm\Events;

use CraftCms\Cms\Shared\Concerns\ValidatableEvent;
use CraftCms\Cms\SystemMessage\Mailables\SystemMessageMailable;

/**
 * Set `$isValid` to `false` to stop the notification, or `$isSpam` to `true` to flag the submission as spam.
 */
final class MessageSending
{
    use ValidatableEvent;

    public bool $isSpam = false;

    public function __construct(
        public SystemMessageMailable $message,
    ) {}
}
