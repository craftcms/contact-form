<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\contactform\models;

use craft\base\Model;
use craft\web\UploadedFile;

/**
 * Class Submission
 *
 * @package craft\contactform
 */
class Submission extends Model
{
    /**
     * @var string|null
     */
    public ?string $fromName = null;

    /**
     * @var string|null
     */
    public ?string $fromEmail = null;

    /**
     * @var string|null
     */
    public ?string $subject = null;

    /**
     * @var string|string[]|null
     */
    public string|array|null $message = null;

    /**
     * @var UploadedFile[]|null
     */
    public UploadedFile|null $attachment = null;

    /**
     * @inheritdoc
     */
    public function attributeLabels() : array
    {
        return [
            'fromName' => \Craft::t('contact-form', 'Your Name'),
            'fromEmail' => \Craft::t('contact-form', 'Your Email'),
            'message' => \Craft::t('contact-form', 'Message'),
            'subject' => \Craft::t('contact-form', 'Subject'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            [['fromEmail', 'message'], 'required'],
            [['fromEmail'], 'email']
        ];
    }
}
