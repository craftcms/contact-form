<?php

/**
 * @link https://craftcms.com/
 *
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace CraftCms\ContactForm\Models;

use craft\base\Model;
use craft\web\UploadedFile;

/**
 * Class Submission
 */
class Submission extends Model
{
    /**
     * @var string|null
     */
    public ?string $fromName;

    /**
     * @var string|null
     */
    public ?string $fromEmail;

    /**
     * @var string|null
     */
    public ?string $subject;

    /**
     * @var string|string[]|string[][]|null
     *
     * @phpstan-var string|array<string|string[]>|null
     */
    public string|array|null $message;

    /**
     * @var UploadedFile|UploadedFile[]|null[]|null
     *
     * @phpstan-var UploadedFile|array<UploadedFile|null>|null
     */
    public UploadedFile|array|null $attachment;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'fromName' => \Craft::t('contact-form', 'Your Name'),
            'fromEmail' => \Craft::t('contact-form', 'Your Email'),
            'message' => \Craft::t('contact-form', 'Message'),
            'subject' => \Craft::t('contact-form', 'Subject'),
        ];
    }
}
