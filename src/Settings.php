<?php

/**
 * @link https://craftcms.com/
 *
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace CraftCms\ContactForm;

use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Support\Arr;
use CraftCms\Cms\Support\Facades\Sites;

use CraftCms\Cms\Validation\Rules\EnvValueRule;
use function CraftCms\Cms\t;

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
    {
        if (! isset($config['prependSender'])) {
            $config['prependSender'] = t('On behalf of', category: 'contact-form');
        }

        if (! isset($config['prependSubject'])) {
            $config['prependSubject'] = t('New message from {siteName}', ['siteName' => Sites::getCurrentSite()->name], 'contact-form');
        }

        if (! isset($config['successFlashMessage'])) {
            $config['successFlashMessage'] = t('Your message has been sent.', category: 'contact-form');
        }

        parent::__construct($config);
    }

    public function validationData(): array
    {
        // Omit validation rules from settings that end up in project config:
        return Arr::except(parent::validationData(), 'allowedMessageFields');
    }

    public function getRules(): array
    {
        return [
            'toEmail' => [new EnvValueRule(['required', 'email'])],
            'successFlashMessage' => ['required', 'string'],
            'prependSender' => ['nullable', 'string'],
            'prependSubject' => ['nullable', 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'toEmail' => t('To: address', category: 'contact-form'),
        ];
    }
}
