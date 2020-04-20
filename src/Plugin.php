<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\orderform;

use Craft;
use craft\orderform\models\Settings;

/**
 * Class Plugin
 *
 * @property Settings $settings
 * @property Mailer $mailer
 * @method Settings getSettings()
 */
class Plugin extends \craft\base\Plugin
{
    /**
     * @inheritdoc
     */
    public $hasCpSettings = true;


    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        // Get and pre-validate the settings
        $settings = $this->getSettings();
        $settings->validate();

        // Get the settings that are being defined by the config file
        $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));

        return Craft::$app->view->renderTemplate('contact-form/_settings', [
            'settings' => $settings,
            'overrides' => array_keys($overrides),
        ]);
    }
}
