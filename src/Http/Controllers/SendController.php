<?php

namespace CraftCms\ContactForm\Http\Controllers;

use CraftCms\Cms\Http\RespondsWithFlash;
use CraftCms\Cms\Support\Env;
use CraftCms\Cms\SystemMessage\Mailables\SystemMessageMailable;
use CraftCms\ContactForm\Submission\Sender;
use CraftCms\ContactForm\Submission\Summary;
use CraftCms\ContactForm\Events\MessageSending;
use CraftCms\ContactForm\Events\MessageSent;
use CraftCms\ContactForm\Http\Requests\SubmissionRequest;
use CraftCms\ContactForm\Plugin;
use CraftCms\ContactForm\Settings;
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
    public function __invoke(SubmissionRequest $submission): ?Response
    {
        $plugin = Plugin::getInstance();
        /** @var Settings $settings */
        $settings = $plugin->getSettings();

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

        // Emit an event, and check whether it was suppressed (a handler returned `false`) or marked as spam:
        if (event($sendingEvent = new MessageSending($mailable), halt: true) === false || $sendingEvent->isSpam) {
            abort(400, t('Your message could not be sent.', category: 'contact-form'));
        }

        if (! Mail::send($mailable)) {
            abort(500, t('There was a problem with your submission, please check the form and try again!', category: 'contact-form'));
        }

        event(new MessageSent($mailable));

        return $this->asSuccess(
            $settings->successFlashMessage,
            // Don’t attempt to flash the uploads:
            $data->except('attachment'),
        );
    }
}
