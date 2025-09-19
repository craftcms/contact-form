<?php

/**
 * @link https://craftcms.com/
 *
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace CraftCms\ContactForm\Models;

use CraftCms\Cms\Plugin\PluginSettings;

class Settings extends PluginSettings
{
    /**
     * @var string|string[]|null
     */
    public string|array|null $toEmail = null;

    public ?string $prependSender = null;

    public ?string $prependSubject = null;

    public bool $allowAttachments = false;

    public ?string $successFlashMessage = null;

    /**
     * @var string[]|null List of allowed `message` sub-keys that can be posted to `contact-form/send` (besides `body`).
     *
     * @since 2.5.0
     */
    public ?array $allowedMessageFields = null;

    public function __construct(array $config = [])
    // public function init(): void
    {
        // parent::init();

        // TODO: we used to use craft\base\Model's init() which a) triggered EVENT_INIT b) was auto run at the end of Model's __construct()
        // this works, but doesn't trigger the EVENT_INIT + I can't access the translator

        $craft = app('Craft');

        if ($this->prependSender === null) {
            $this->prependSender = 'On behalf of';
            //$this->prependSender = \Craft::t('contact-form', 'On behalf of');
        }

        if ($this->prependSubject === null) {
            $this->prependSubject = sprintf('New message from %s', $craft->getSites()->getCurrentSite()->name);
            //$this->prependSubject = \Craft::t('contact-form', 'New message from {siteName}', [
            //  'siteName' => \Craft::$app->getSites()->getCurrentSite()->name,
            //]);
        }

        if ($this->successFlashMessage === null) {
            $this->successFlashMessage = 'Your message has been sent.';
            //$this->successFlashMessage = \Craft::t('contact-form', 'Your message has been sent.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getRules(): array
    {
        return [
            'toEmail' => ['required', 'string'],
            'successFlashMessage' => ['required', 'string'],
            'prependSender' => ['nullable', 'string'],
            'prependSubject' => ['nullable', 'string'],
        ];
    }
}
