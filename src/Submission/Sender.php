<?php

namespace CraftCms\ContactForm\Submission;

use CraftCms\ContactForm\Plugin;

class Sender
{
    public function __construct(
        public Plugin $plugin,
    ) {}

    public function compile(?string $name = null): string
    {
        return join(' ', array_filter([
            $this->plugin->getSettings()->prependSender,
            $name,
        ]));
    }
}
