<?php

namespace CraftCms\ContactForm\Http\Controllers;

use Craft;
use craft\web\UploadedFile;
use CraftCms\Cms\Http\RespondsWithModel;
use CraftCms\ContactForm\Facades\Mailer;
use CraftCms\ContactForm\Models\Submission;
use CraftCms\ContactForm\Plugin;
use Symfony\Component\HttpFoundation\Response;

final class SendController
{
    use RespondsWithModel;

    /**
     * Sends a contact form submission.
     */
    public function index(): ?Response
    {
        $request = Craft::$app->getRequest();
        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();

        $submission = new Submission;
        $submission->fromEmail = $request->getBodyParam('fromEmail');
        $submission->fromName = $request->getBodyParam('fromName');
        $submission->subject = $request->getBodyParam('subject');

        $message = $request->getBodyParam('message');
        if (is_array($message)) {
            $submission->message = array_filter($message, function ($value) {
                return $value !== '';
            });
        } else {
            $submission->message = $message;
        }

        if ($settings->allowAttachments && isset($_FILES['attachment']) && isset($_FILES['attachment']['name'])) {
            if (is_array($_FILES['attachment']['name'])) {
                $submission->attachment = UploadedFile::getInstancesByName('attachment');
            } else {
                $submission->attachment = UploadedFile::getInstanceByName('attachment');
            }
        }

        if (! Mailer::send($submission)) {
            return $this->asModelFailure(
                $submission,
                Craft::t('contact-form', 'There was a problem with your submission, please check the form and try again!'),
                'submission',
                [
                    'errors' => $submission->getErrors(),
                ],
            );
        }

        return $this->asModelSuccess(
            $submission,
            $settings->successFlashMessage,
            'submission',
        );
    }
}
