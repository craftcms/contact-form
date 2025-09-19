<?php

namespace CraftCms\ContactForm;

use Craft;
use craft\elements\User;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\mail\Message;
use CraftCms\Cms\Component\Concerns\HasComponentEvents;
use CraftCms\Cms\Support\Arr;
use CraftCms\Cms\Support\Env;
use CraftCms\ContactForm\Events\MessageSending;
use CraftCms\ContactForm\Events\MessageSent;
use CraftCms\ContactForm\Models\Submission;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Event;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Markdown;

#[Singleton]
final readonly class Mailer
{
    use HasComponentEvents;

    /**
     * @event SubmissionEvent The event that is triggered before a message is sent
     */
    public const EVENT_BEFORE_SEND = 'beforeSend';

    /**
     * @event SubmissionEvent The event that is triggered after a message is sent
     */
    public const EVENT_AFTER_SEND = 'afterSend';

    /**
     * Sends an email submitted through a contact form.
     *
     * @param  bool  $runValidation  Whether the section should be validated
     *
     * @throws InvalidConfigException if the plugin settings don't validate
     */
    public function send(Submission $submission, bool $runValidation = true): bool
    {
        // Get the plugin settings and make sure they validate before doing anything
        $settings = Plugin::getInstance()->getSettings();
        if (! $settings->validate()) {
            throw new InvalidConfigException('The Contact Form settings don’t validate.');
        }

        if ($runValidation && ! $submission->validate()) {
            Craft::info('Contact form submission not saved due to validation error.', __METHOD__);

            return false;
        }

        $mailer = Craft::$app->getMailer();

        // Prep the message
        $fromEmail = $this->getFromEmail($mailer->from);
        $fromName = $this->compileFromName($submission->fromName);
        $subject = $this->compileSubject($submission->subject);
        $textBody = $this->compileTextBody($submission);
        $htmlBody = $this->compileHtmlBody($textBody);

        // Flag for file attachment validation.
        $validAttachments = true;

        $message = (new Message)
            ->setFrom([$fromEmail => $fromName])
            ->setReplyTo([$submission->fromEmail => (string) $submission->fromName])
            ->setSubject($subject)
            ->setTextBody($textBody)
            ->setHtmlBody($htmlBody);

        if ($submission->attachment !== null) {
            $allowedFileTypes = Craft::$app->getConfig()->getGeneral()->allowedFileExtensions;

            if (! is_array($submission->attachment)) {
                $submission->attachment = [$submission->attachment];
            }

            foreach ($submission->attachment as $attachment) {
                if (! $attachment) {
                    continue;
                }

                // Validate that the file is safe to send by e-mail
                $extension = pathinfo($attachment->name, PATHINFO_EXTENSION);

                if (! in_array(strtolower($extension), $allowedFileTypes)) {
                    $validAttachments = false;
                }

                $message->attach($attachment->tempName, [
                    'fileName' => $attachment->name,
                    'contentType' => FileHelper::getMimeType($attachment->tempName),
                ]);
            }
        }

        // Grab any "to" emails set in the plugin settings.
        $toEmails = Env::parse($settings->toEmail);
        $toEmails = is_string($toEmails) ? StringHelper::split($toEmails) : $toEmails;

        // Fire a message sending event (formerly 'beforeSend')
        Event::dispatch($event = new MessageSending(
            submission: $submission,
            message: $message,
            toEmails: $toEmails,
        ));

        if ($event->isSpam) {
            Craft::warning('Contact form submission suspected to be spam.', __METHOD__);

            return true;
        }

        if ($validAttachments === false) {
            Craft::error('Contact form submission contains a disallowed filetype.', __METHOD__);

            return false;
        }

        foreach ($event->toEmails as $toEmail) {
            $message->setTo($toEmail);
            $mailer->send($message);
        }

        // Fire a message sent event (formerly 'afterSend')
        if (Event::hasListeners(MessageSent::class)) {
            Event::dispatch(new MessageSent(
                submission: $submission,
                message: $message,
                toEmails: $event->toEmails,
            ));
        }

        return true;
    }

    /**
     * Returns the "From" email value on the given mailer $from property object.
     *
     * @param  string|array|User|User[]|null  $from
     *
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
     */
    public function compileFromName(?string $fromName = null): string
    {
        $settings = Plugin::getInstance()->getSettings();

        return $settings->prependSender.($settings->prependSender && $fromName ? ' ' : '').$fromName;
    }

    /**
     * Compiles the real email subject from the submitted subject.
     */
    public function compileSubject(?string $subject = null): string
    {
        $settings = Plugin::getInstance()->getSettings();

        return $settings->prependSubject.($settings->prependSubject && $subject ? ' - ' : '').$subject;
    }

    /**
     * Compiles the real email textual body from the submitted message.
     */
    public function compileTextBody(Submission $submission): string
    {
        $fields = [];

        if ($submission->fromName) {
            $fields[Craft::t('contact-form', 'Name')] = $submission->fromName;
        }

        $fields[Craft::t('contact-form', 'Email')] = $submission->fromEmail;

        if (is_array($submission->message)) {
            $settings = Plugin::getInstance()->getSettings();
            $messageFields = array_merge($submission->message);
            $body = Arr::pull($messageFields, 'body', '');
            foreach ($messageFields as $key => $value) {
                if ($settings->allowedMessageFields === null || in_array($key, $settings->allowedMessageFields)) {
                    $label = Craft::t('site', $key);
                    $fields[$label] = $value;
                }
            }
        } else {
            $body = (string) $submission->message;
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
            $body = preg_replace('/\R/u', "\n", $body);
            $text .= "\n\n".$body;
        }

        return $text;
    }

    /**
     * Compiles the real email HTML body from the compiled textual body.
     */
    public function compileHtmlBody(string $textBody): string
    {
        $html = Html::encode($textBody);
        $html = Markdown::process($html);

        return $html;
    }
}
