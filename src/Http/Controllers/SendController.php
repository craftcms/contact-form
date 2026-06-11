<?php

namespace CraftCms\ContactForm\Http\Controllers;

use CraftCms\Cms\Cms;
use CraftCms\Cms\Http\RespondsWithFlash;
use CraftCms\Cms\Support\Env;
use CraftCms\Cms\SystemMessage\Mailables\SystemMessageMailable;
use CraftCms\ContactForm\Data\Summary;
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

        $data = $submission->validated();

        $mailable = new SystemMessageMailable(
            key: 'contactform_submission',
            variables: [
                ...$data,
                'summary' => app(Summary::class)->compile($data['message']),
            ],
        )
            ->to(Env::parse($settings->toEmail))
            ->replyTo($data['fromEmail'], $data['fromName']);

        if ($files = $submission->file('attachment')) {
            $attachments = array_map(Attachment::fromUploadedFile(...), $files);
            $mailable->attachMany($attachments);
        }

        if (! Mail::send($mailable)) {
            abort(500, t('There was a problem with your submission, please check the form and try again!', category: 'contact-form'));
        }

        return $this->asSuccess(
            $settings->successFlashMessage,
            $submission->validated(),
        );
    }
}
