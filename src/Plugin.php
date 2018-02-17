<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\contactform;

use Craft;
use craft\contactform\models\Settings;

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
     * @return Mailer
     */
    public function getMailer(): Mailer
    {
        return $this->get('mailer');
    }

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
