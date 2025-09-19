<?php

/**
 * @link https://craftcms.com/
 *
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace CraftCms\ContactForm\Events;

use craft\mail\Message;
use CraftCms\ContactForm\Models\Submission;

final readonly class MessageSent
{
    public function __construct(
        public Submission $submission, // The user submission.
        public Message $message, // The message about to be sent.
        public array $toEmails, // The email address(es) the submission will get sent to
        public bool $isSpam = false, // Whether the message appears to be spam, and should not really be sent.
    ) {}
}
