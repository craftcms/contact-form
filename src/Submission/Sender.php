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
        // Without a name, “On behalf of” alone isn’t helpful:
        if (blank($name)) {
            return '';
        }

        return implode(' ', array_filter([
            $this->settings->prependSender,
            $name,
        ]));
    }
}
