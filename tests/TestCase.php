<?php

namespace CraftCms\ContactForm\Tests;

use CraftCms\Cms\Plugin\Testing\PluginTestCase;

abstract class TestCase extends PluginTestCase
{
    /**
     * Project config overrides (`config/craft/contact-form.php`) for each test.
     *
     * @var array<string, mixed>
     */
    protected array $contactFormConfig = [
        'toEmail' => 'inbox@example.com',
        'prependSubject' => 'Website enquiry',
        'prependSender' => 'Via website:',
        'successFlashMessage' => 'Thanks, we got it!',
    ];

    protected function defineEnvironment($app): void
    {
        $app['config']->set('craft.contact-form', $this->contactFormConfig);
    }
}
