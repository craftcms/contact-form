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
    public $fromName;

    /**
     * @var string|null
     */
    public $fromEmail;

    /**
     * @var string|null
     */
    public $subject;

    /**
     * @var string|string[]|null
     */
    public $message;

    /**
     * @var UploadedFile[]|null
     */
    public $attachment;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
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
    public function rules()
    {
        return [
            [['fromEmail', 'message'], 'required'],
        ];
    }
}
