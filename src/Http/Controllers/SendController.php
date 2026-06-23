<?php

namespace CraftCms\ContactForm\Http\Controllers;

use CraftCms\Cms\Http\RespondsWithFlash;
use CraftCms\Cms\Support\Env;
use CraftCms\Cms\SystemMessage\Mailables\SystemMessageMailable;
use CraftCms\ContactForm\Submission\Sender;
use CraftCms\ContactForm\Submission\Summary;
use CraftCms\ContactForm\Events\MessageSending;
use CraftCms\ContactForm\Events\MessageSent;
use CraftCms\ContactForm\Plugin;
use CraftCms\ContactForm\Settings;
use CraftCms\ContactForm\Validation\SubmissionRuleset;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

use function CraftCms\Cms\t;

final class SendController
{
    use RespondsWithFlash;

    /**
     * Sends a contact form submission.
     */
    public function __invoke(SubmissionRuleset $submission): ?Response
    {
        $plugin = Plugin::getInstance();
        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        // Pre-validate so our fail hooks run…
        $submission->validate();

        // …then grab the request-like `ValidatedInput`:
        $data = $submission->safe();

        $mailable = new SystemMessageMailable(
            key: 'contactform_submission',
            variables: [
                ...$data->all(),
                'settings' => $settings,
                'summary' => app(Summary::class)->compile($data->input('message')),
            ],
        );

        // The anonymous user does not have control over the recipient:
        $mailable->to(Env::parse($settings->toEmail));

        $mailable->replyTo(
            $data->string('fromEmail'),
            app(Sender::class)->compile($data->string('fromName') ?? null),
        );

        // Attach any (valid) files that were uploaded:
        $files = $data->input('attachment');

        if ($files) {
            // Normalize to an array (again):
            if (! is_array($files)) {
                $files = [$files];
            }

            $attachments = array_map(Attachment::fromUploadedFile(...), $files);
            $mailable->attachMany($attachments);
        }

        $result = event($sendingEvent = new MessageSending($mailable), halt: true);

        // All failure states are treated the same, to avoid disclosing the mode:
        // - Handler suppressing the event (explicitly returning `false`)
        // - Handler marking the submission as spam
        // - Mailer failures
        if (
            $result === false
            || $sendingEvent->isSpam
            || ! Mail::send($mailable)
        ) {
            return $this->asFailure(t('There was a problem with your submission, please check the form and try again!', category: 'contact-form'), $data->except('attachment'));
        }

        // Ok, it definitely sent!
        event(new MessageSent($mailable));

        return $this->asSuccess(
            $settings->successFlashMessage,
            // Don’t attempt to flash the uploads (they can’t be serialized to the session):
            $data->except('attachment'),
        );
    }
}
