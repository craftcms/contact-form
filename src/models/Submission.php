<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\contactform\models;

use Craft;
use craft\base\Model;
use craft\web\UploadedFile;
use craft\web\Request;
use craft\contactform\Plugin;

/**
 * Class Submission
 *
 * @package craft\contactform
 */
class Submission extends Model implements SubmissionInterface
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
     * 
     */
    public function populateModel(Request $request): Submission
    {
        $this->fromEmail = $request->getBodyParam('fromEmail');
        $this->fromName = $request->getBodyParam('fromName');
        $this->subject = $request->getBodyParam('subject');

        return $this;
    }

    /**
     * Compiles the "From" name value from the submitted name.
     *
     * @return string
     */
    public function compileFromName(): string
    {
        $settings = Plugin::getInstance()->getSettings();
        return $settings->prependSender.($settings->prependSender && $this->fromName ? ' ' : '').$this->fromName;
    }

    /**
     * Compiles the real email subject from the submitted subject.
     *
     * @return string
     */
    public function compileSubject(): string
    {
        $settings = Plugin::getInstance()->getSettings();
        return $settings->prependSubject.($settings->prependSubject && $this->subject ? ' - ' : '').$this->subject;
    }

    /**
     * Compiles the real email textual body from the submitted message.
     *
     * @param Submission $submission
     * @return string
     */
    public function compileTextBody(): string
    {
        $fields = [];

        if ($this->fromName) {
            $fields[Craft::t('contact-form', 'Name')] = $this->fromName;
        }

        $fields[Craft::t('contact-form', 'Email')] = $this->fromEmail;

        if (is_array($this->message)) {
            $body = $this->message['body'] ?? '';
            $fields = array_merge($fields, $this->message);
            unset($fields['body']);
        } else {
            $body = (string)$this->message;
        }

        $text = '';

        foreach ($fields as $key => $value) {
            $text .= ($text ? "\n" : '')."- **{$key}:** ";
            if (is_array($value)) {
                $text .= implode(', ', $value);
            } else {
                $text .= $value;
            }
        }

        if ($body !== '') {
            $body = preg_replace('/\R/', "\n\n", $body);
            $text .= "\n\n".$body;
        }

        return $text;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fromName' => Craft::t('contact-form', 'Your Name'),
            'fromEmail' => Craft::t('contact-form', 'Your Email'),
            'message' => Craft::t('contact-form', 'Message'),
            'subject' => Craft::t('contact-form', 'Subject'),
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
