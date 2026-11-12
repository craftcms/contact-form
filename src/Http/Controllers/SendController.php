<?php

declare(strict_types=1);

namespace CraftCms\ContactForm\Http\Controllers;

use CraftCms\Cms\Http\Mixins\RequestMixin;
use CraftCms\Cms\Http\RespondsWithFlash;
use CraftCms\Cms\SystemMessage\Mailables\SystemMessageMailable;
use CraftCms\ContactForm\Events\MessageSending;
use CraftCms\ContactForm\Events\MessageSent;
use CraftCms\ContactForm\Settings;
use CraftCms\ContactForm\Submission\Sender;
use CraftCms\ContactForm\Submission\Summary;
use CraftCms\ContactForm\Validation\SubmissionRuleset;
use Illuminate\Mail\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use function CraftCms\Cms\t;

final class SendController
{
    use RespondsWithFlash;

    /**
     * Sends a contact form submission.
     */
    public function __invoke(SubmissionRuleset $submission, Settings $settings, Sender $sender, Summary $summary): Response
    {
        // Pre-validate so our fail hooks run…
        $submission->validate();

        // …then grab the request-like `ValidatedInput`:
        $data = $submission->safe();

        $mailable = new SystemMessageMailable(
            key: 'contactform_submission',
            variables: [
                // Always set, so a blank name doesn’t fall back to the system email’s sender name:
                'fromName' => null,
                ...$data->all(),
                'settings' => $settings,
                'summary' => $summary->compile($data->input('message')),
            ],
        );

        // The anonymous user does not have control over the recipient:
        $mailable->to($settings->getToEmails());
        /** @todo Allow control over recipient via signed params, using {@see RequestMixin::getSigned()}. */

        // A “from” address is required, but only set as the `Reply-To` address, not directly `From` (to avoid upsetting mail providers):
        $mailable->replyTo(
            $data->input('fromEmail'),
            $sender->compile($data->input('fromName')),
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

        // Don’t attempt to flash the uploads (they can’t be serialized to the session):
        $submitted = $data->except('attachment');

        event($sendingEvent = new MessageSending($mailable));

        // All failure states are treated the same, to avoid disclosing the mode. The mailer is only reached when a listener didn’t stop the notification:
        $failure = match (true) {
            ! $sendingEvent->isValid => 'A notification was stopped by an event listener.',
            $sendingEvent->isSpam => 'A submission was marked as spam.',
            $mailable->to === [] && $mailable->cc === [] && $mailable->bcc === [] => 'The notification has no recipients. Check the “To Email” setting.',
            ! $this->handOff($mailable) => 'There was an error when handing off a notification to the mailer.',
            default => null,
        };

        if ($failure !== null) {
            Log::error("Contact Form: $failure", ['from' => $data->input('fromEmail')]);

            // Return generic failure notice:
            return $this->asFailure(t('There was a problem with your submission, please check the form and try again!', category: 'contact-form'), [
                'submission' => $submitted,
            ]);
        }

        // Ok, it definitely sent!
        event(new MessageSent($mailable));

        return $this->asSuccess(
            $settings->successFlashMessage,
            // Nested, so a submitted `message` field can’t replace the flash message in JSON responses:
            ['submission' => $submitted],
            // Redirect templates can reference submitted fields, like `contact/thanks?from={fromName}`, so they’re URL-encoded:
            $this->getPostedRedirectUrl((object) array_map(
                fn (mixed $value) => is_string($value) ? rawurlencode($value) : $value,
                $submitted,
            )),
        );
    }

    /**
     * Hands the notification off to the mailer, reporting transport failures instead of throwing them.
     */
    private function handOff(SystemMessageMailable $mailable): bool
    {
        try {
            return Mail::send($mailable) !== null;
        } catch (TransportExceptionInterface $e) {
            report($e);

            return false;
        }
    }
}
