<?php

namespace CraftCms\ContactForm\Tests;

use CraftCms\Aliases\AliasesServiceProvider;
use CraftCms\Cms\Plugin\Testing\PluginTestCase;
use CraftCms\DependencyAwareCache\CacheServiceProvider;

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

    /**
     * Register the supporting providers that a real app gets from package discovery.
     */
    protected function getPackageProviders($app): array
    {
        return [
            AliasesServiceProvider::class,
            CacheServiceProvider::class,
            ...parent::getPackageProviders($app),
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('craft.contact-form', $this->contactFormConfig);
    }
}
