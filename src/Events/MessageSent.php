<?php

/**
 * @link https://craftcms.com/
 *
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace CraftCms\ContactForm\Events;

use CraftCms\Cms\SystemMessage\Mailables\SystemMessageMailable;

final readonly class MessageSent
{
    public function __construct(
        public SystemMessageMailable $submission,
    ) {}
}
