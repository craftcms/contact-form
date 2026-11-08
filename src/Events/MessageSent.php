<?php

declare(strict_types=1);

namespace CraftCms\ContactForm\Events;

use CraftCms\Cms\SystemMessage\Mailables\SystemMessageMailable;

final readonly class MessageSent
{
    public function __construct(
        public SystemMessageMailable $message,
    ) {}
}
