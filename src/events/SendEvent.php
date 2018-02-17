<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\contactform\events;

use craft\contactform\models\Submission;
use craft\mail\Message;
use yii\base\Event;

class SendEvent extends Event
{
    /**
     * @var Submission The user submission.
     */
    public $submission;

    /**
     * @var Message The message about to be sent.
     */
    public $message;

    /**
     * @var string[] The email address(es) the submission will get sent to.
     */
    public $toEmails;

    /**
     * @var bool Whether the message appears to be spam, and should not really be sent.
     */
    public $isSpam = false;
}
