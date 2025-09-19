<?php

/**
 * @link https://craftcms.com/
 *
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace CraftCms\ContactForm;

use Craft;
use CraftCms\Cms\Plugin\Plugin as CraftPlugin;
use CraftCms\ContactForm\Models\Settings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Class Plugin
 *
 * @property Settings $settings
 * @property Mailer $mailer
 *
 * @method Settings getSettings()
 */
class Plugin extends CraftPlugin
{
    public string $schemaVersion = '1.0.0';

    public bool $hasCpSettings = true;

    //    protected array $vite = [
    //        'input' => [
    //            'resources/js/plugin.js',
    //            'resources/css/plugin.css',
    //        ],
    //        'publicDirectory' => 'resources/dist',
    //    ];

    protected array $scripts = [];

    protected array $styles = [];

    protected array $publishables = [];

    public function bootPlugin(): void
    {
        Log::info(
            sprintf(
                '%s plugin loaded',
                static::getInstance()->name
            ),
            [__METHOD__]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createSettingsModel(): ?Settings
    {
        return new Settings;
    }

    /**
     * {@inheritdoc}
     */
    protected function settingsHtml(): ?string
    {
        // Get and pre-validate the settings
        $settings = $this->getSettings();
        $settings->validate();

        // Get the settings that are being defined by the config file
        $overrides = Config::get('craft.'.strtolower($this->handle), []);

        return Craft::$app->view->renderTemplate('contact-form/_settings.twig', [
            'settings' => $settings,
            'overrides' => array_keys($overrides),
        ]);
    }
}
