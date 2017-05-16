<?php

namespace craft\contactform;

use Craft;
use craft\contactform\events\SendEvent;
use craft\contactform\models\Submission;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\FileHelper;
use craft\mail\Message;
use yii\base\Component;
use yii\base\InvalidConfigException;
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
     * @param bool       $runValidation Whether the section should be validated
     *
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
        $fromName = $this->compileFromName($submission->fromName);
        $subject = $this->compileSubject($submission->subject);
        $textBody = $this->compileTextBody($submission->message);
        $htmlBody = $this->compileHtmlBody($textBody);

        $message = (new Message())
            ->setFrom([$fromEmail => $fromName])
            ->setReplyTo([$submission->fromEmail => $submission->fromName])
            ->setSubject($subject)
            ->setTextBody($textBody)
            ->setHtmlBody($htmlBody);

        if ($submission->attachment !== null) {
            foreach ($submission->attachment as $attachment) {
                $message->attach($attachment->tempName, [
                    'fileName' => $attachment->name,
                    'contentType' => FileHelper::getMimeType($attachment->tempName),
                ]);
            }
        }

        // Grab any "to" emails set in the plugin settings.
        $toEmails = ArrayHelper::toArray($settings->toEmail);

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

        foreach ($toEmails as $toEmail) {
            $message->setTo($toEmail);
            $mailer->send($message);
        }

        // Fire an 'afterSend' event
        $this->trigger(self::EVENT_AFTER_SEND, new SendEvent([
            'submission' => $submission,
            'message' => $message,
            'toEmails' => $toEmails,
        ]));

        return true;
    }

    /**
     * Returns the "From" email value on the given mailer $from property object.
     *
     * @param string|array|User|User[]|null $from
     *
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
     * Compiles the "From" name value from the submitted name.
     *
     * @param string|null $fromName
     *
     * @return string
     */
    public function compileFromName(string $fromName = null): string
    {
        $settings = Plugin::getInstance()->getSettings();
        return $settings->prependSender . ($settings->prependSender && $fromName ? ' ' : '') . $fromName;
    }

    /**
     * Compiles the real email subject from the submitted subject.
     *
     * @param string|null $subject
     *
     * @return string
     */
    public function compileSubject(string $subject = null): string
    {
        $settings = Plugin::getInstance()->getSettings();
        return $settings->prependSubject.($settings->prependSubject && $subject ? ' - ' : '').$subject;
    }

    /**
     * Compiles the real email textual body from the submitted message.
     *
     * @param string|string[] $message
     *
     * @return string
     */
    public function compileTextBody($message): string
    {
        if (!is_array($message)) {
            return (string)$message;
        }

        // Start with the body, if submitted
        $compiledMessage = '';

        // Now loop through the rest
        foreach ($message as $key => $value) {
            if ($key === 'body') {
                continue;
            }

            if ($compiledMessage) {
                $compiledMessage .= "\n\n";
            }

            $compiledMessage .= $key.': ';

            if (is_array($value)) {
                $compiledMessage .= implode(', ', $value);
            } else {
                $compiledMessage .= $value;
            }
        }

        if (!empty($message['body'])) {
            if ($compiledMessage) {
                $compiledMessage .= "\n\n";
            }

            $compiledMessage .= $message['body'];
        }

        return $compiledMessage;
    }

    /**
     * Compiles the real email HTML body from the compiled textual body.
     *
     * @param string $textBody
     *
     * @return string
     */
    public function compileHtmlBody(string $textBody): string
    {
        $html = Markdown::process($textBody);

        // Prevent Twig tags from getting parsed
        // TODO: probably safe to remove?
        $html = str_replace(['{', '}'], ['&lbrace;', '&rbrace;'], $html);

        return $html;
    }
}
