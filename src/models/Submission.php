<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.com/license
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
            'fromName' => \Craft::t('contactform', 'Your Name'),
            'fromEmail' => \Craft::t('contactform', 'Your Email'),
            'message' => \Craft::t('contactform', 'Message'),
            'subject' => \Craft::t('contactform', 'Subject'),
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
