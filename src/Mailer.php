<?php

namespace craft\contactform;

use Craft;
use craft\contactform\events\SendEvent;
use craft\contactform\models\Submission;
use craft\elements\User;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\mail\Message;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Markdown;

class Mailer extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event SubmissionEvent The event that is triggered before a message is sent
     */
    const EVENT_BEFORE_SEND = 'beforeSend';

    /**
     * @event SubmissionEvent The event that is triggered after a message is sent
     */
    const EVENT_AFTER_SEND = 'afterSend';

    // Public Methods
    // =========================================================================

    /**
     * Sends an email submitted through a contact form.
     *
     * @param Submission $submission
     * @param bool $runValidation Whether the section should be validated
     * @throws InvalidConfigException if the plugin settings don't validate
     * @return bool
     */
    public function send(Submission $submission, bool $runValidation = true): bool
    {
        // Get the plugin settings and make sure they validate before doing anything
        $settings = Plugin::getInstance()->getSettings();
        if (!$settings->validate()) {
            throw new InvalidConfigException('The Contact Form settings don’t validate.');
        }

        if ($runValidation && !$submission->validate()) {
            Craft::info('Contact form submission not saved due to validation error.', __METHOD__);
            return false;
        }

        $mailer = Craft::$app->getMailer();

        // Prep the message
        $fromEmail = $this->getFromEmail($mailer->from);
        $fromName = $submission->compileFromName();
        $subject = $submission->compileSubject();
        $textBody = $submission->compileTextBody();
        $htmlBody = $this->compileHtmlBody($textBody);

        $message = (new Message())
            ->setFrom([$fromEmail => $fromName])
            ->setReplyTo([$submission->fromEmail => $submission->fromName])
            ->setSubject($subject)
            ->setTextBody($textBody)
            ->setHtmlBody($htmlBody);

        if ($submission->attachment !== null) {
            foreach ($submission->attachment as $attachment) {
                if (!$attachment) {
                    continue;
                }
                $message->attach($attachment->tempName, [
                    'fileName' => $attachment->name,
                    'contentType' => FileHelper::getMimeType($attachment->tempName),
                ]);
            }
        }

        // Grab any "to" emails set in the plugin settings.
        $toEmails = is_string($settings->toEmail) ? StringHelper::split($settings->toEmail) : $settings->toEmail;

        // Fire a 'beforeSend' event
        $event = new SendEvent([
            'submission' => $submission,
            'message' => $message,
            'toEmails' => $toEmails,
        ]);
        $this->trigger(self::EVENT_BEFORE_SEND, $event);

        if ($event->isSpam) {
            Craft::info('Contact form submission suspected to be spam.', __METHOD__);
            return true;
        }

        foreach ($event->toEmails as $toEmail) {
            $message->setTo($toEmail);
            $mailer->send($message);
        }

        // Fire an 'afterSend' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SEND)) {
            $this->trigger(self::EVENT_AFTER_SEND, new SendEvent([
                'submission' => $submission,
                'message' => $message,
                'toEmails' => $event->toEmails,
            ]));
        }

        return true;
    }

    /**
     * Returns the "From" email value on the given mailer $from property object.
     *
     * @param string|array|User|User[]|null $from
     * @return string
     * @throws InvalidConfigException if it can’t be determined
     */
    public function getFromEmail($from): string
    {
        if (is_string($from)) {
            return $from;
        }
        if ($from instanceof User) {
            return $from->email;
        }
        if (is_array($from)) {
            $first = reset($from);
            $key = key($from);
            if (is_numeric($key)) {
                return $this->getFromEmail($first);
            }
            return $key;
        }
        throw new InvalidConfigException('Can\'t determine "From" email from email config settings.');
    }

    /**
     * Compiles the real email HTML body from the compiled textual body.
     *
     * @param string $textBody
     * @return string
     */
    public function compileHtmlBody(string $textBody): string
    {
        $html = Html::encode($textBody);
        $html = Markdown::process($html);

        return $html;
    }
}
