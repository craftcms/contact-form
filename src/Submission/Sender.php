<?php

declare(strict_types=1);

namespace CraftCms\ContactForm\Submission;

use CraftCms\ContactForm\Settings;

class Sender
{
    public function __construct(
        public Settings $settings,
    ) {}

    public function compile(?string $name = null): string
    {
        return implode(' ', array_filter([
            $this->settings->prependSender,
            $name,
        ]));
    }
}
