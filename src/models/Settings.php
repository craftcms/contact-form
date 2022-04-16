<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\contactform\models;

use craft\base\Model;

class Settings extends Model
{
    /**
     * @var string|string[]|null
     */
    public $toEmail;

    /**
     * @var string|null
     */
    public $prependSender;

    /**
     * @var string|null
     */
    public $prependSubject;

    /**
     * @var bool
     */
    public $allowAttachments = false;

    /**
     * @var string|null
     */
    public $successFlashMessage;

    /**
     * @var string[]|null List of allowed `message` sub-keys that can be posted to `contact-form/send` (besides `body`).
     * @since 2.5.0
     */
    public $allowedMessageFields;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->prependSender === null) {
            $this->prependSender = \Craft::t('contact-form', 'On behalf of');
        }

        if ($this->prependSubject === null) {
            $this->prependSubject = \Craft::t('contact-form', 'New message from {siteName}', [
                'siteName' => \Craft::$app->getSites()->getCurrentSite()->name,
            ]);
        }

        if ($this->successFlashMessage === null) {
            $this->successFlashMessage = \Craft::t('contact-form', 'Your message has been sent.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            [['toEmail', 'successFlashMessage'], 'required'],
            [['toEmail', 'prependSender', 'prependSubject', 'successFlashMessage'], 'string'],
        ];
    }
}
