<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\contactform\models;

use craft\base\Model;
use craft\helpers\App;

class Settings extends Model
{
    /**
     * @var string|string[]|null
     */
    public string|array|null $toEmail = null;

    /**
     * @var string|null
     */
    public ?string $prependSender = null;

    /**
     * @var string|null
     */
    public ?string $prependSubject = null;

    /**
     * @var bool
     */
    public bool $allowAttachments = false;

    /**
     * @var string|null
     */
    public ?string $successFlashMessage = null;

    /**
     * @var int
     */
    public int $validationFailStatusCode = 200;

    /**
     * @inheritdoc
     */
    public function init() : void
    {
        parent::init();

        if($this->toEmail === null) {
            $this->toEmail = App::mailSettings()->fromEmail;
        }

        if ($this->prependSender === null) {
            $this->prependSender = \Craft::t('contact-form', 'On behalf of');
        }

        if ($this->prependSubject === null) {
            $this->prependSubject = \Craft::t('contact-form', 'New message from {siteName}', [
                'siteName' => \Craft::$app->getSites()->getCurrentSite()->name
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
