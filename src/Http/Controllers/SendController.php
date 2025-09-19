<?php

namespace CraftCms\ContactForm\Http\Controllers;

use Craft;
use craft\web\UploadedFile;
use CraftCms\Cms\Http\RespondsWithModel;
use CraftCms\ContactForm\Facades\Mailer;
use CraftCms\ContactForm\Models\Submission;
use CraftCms\ContactForm\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as IlluminateValidator;
use Symfony\Component\HttpFoundation\Response;

final class SendController
{
    use RespondsWithModel;

    /**
     * Sends a contact form submission.
     */
    public function __invoke(Request $request): ?Response
    {
        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();

        $this->prepareData($request);

        $validator = Validator::make($request->all(), [
            'fromEmail' => ['required', 'email'],
            'fromName' => ['required', 'string'],
            'message' => ['required'],
        ]);

        if ($validator->fails()) {
            $submission = $this->populateModel($validator->getData(), $validator);

            return $this->asModelFailure(
                $submission,
                Craft::t('contact-form', 'There was a problem with your submission, please check the form and try again!'),
                'submission',
                [
                    'errors' => $submission->getErrors(),
                ],
            );
        }

        $data = $validator->validated();

        $submission = $this->populateModel($data);

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
            );
        }

        return $this->asModelSuccess(
            $submission,
            $settings->successFlashMessage,
            'submission',
        );
    }

    private function prepareData(Request $request): void
    {
        $message = $request->input('message');
        if (is_array($message)) {
            $message = array_filter($message, function ($value) {
                return $value !== '' && $value !== null;
            });
        }
        $request->merge([
            'message' => $message,
        ]);
    }

    private function populateModel(array $data, ?IlluminateValidator $validator = null): Submission
    {
        $submission = new Submission;
        $submission->fromEmail = $data['fromEmail'] ?? null;
        $submission->fromName = $data['fromName'] ?? null;
        $submission->subject = $data['subject'] ?? null;
        $submission->message = $data['message'] ?? null;

        if ($validator !== null) {
            $errors = $validator->errors()->getMessages();
            $submission->addErrors($errors);
        }

        return $submission;
    }
}
