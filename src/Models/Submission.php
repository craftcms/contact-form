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
// TODO: should we still extend craft\base\Model?
// TODO: how about using the new CraftCms\Cms\Component\Concerns\ValidatableComponent trait?
// TODO: what about before and after validate events? for now, the old EVENT_AFTER_VALIDATE still triggers and can be used
class Submission extends Model
{
    public ?string $fromName = null;

    public ?string $fromEmail = null;

    public ?string $subject = null;

    /**
     * @var string|string[]|string[][]|null
     *
     * @phpstan-var string|array<string|string[]>|null
     */
    public string|array|null $message = null;

    /**
     * @var UploadedFile|UploadedFile[]|null[]|null
     *
     * @phpstan-var UploadedFile|array<UploadedFile|null>|null
     */
    public UploadedFile|array|null $attachment = null;

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
