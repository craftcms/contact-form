<?php

namespace CraftCms\ContactForm\Http\Requests;

use Closure;
use CraftCms\Cms\Support\Arr;
use CraftCms\Cms\Support\Flash;
use CraftCms\ContactForm\Plugin;
use CraftCms\ContactForm\Settings;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

use Override;
use function CraftCms\Cms\t;

class SubmissionRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $attachment = $this->file('attachment');

        $this->merge([
            // `message` can be a single string or a map of “fields”
            'message' => array_filter($this->array('message'), fn ($val) => $val !== '' && $val !== null),
        ]);
    }

    public function rules(): array
    {
        /** @var Settings $settings */
        $settings = Plugin::getInstance()->getSettings();

        $rules = [
            'fromEmail' => ['required', 'email'],
            'fromName' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            'message' => ['required'],
            'attachment' => [
                'nullable',
                function (string $attribute, mixed $value, Closure $fail) use ($settings) {
                    if (! $settings->allowAttachments) {
                        $fail(t('Attachments are not allowed.', category: 'contact-form'));
                    }

                    // Normalize single files to an array:
                    if ($value instanceof UploadedFile) {
                        $value = [$value];
                    }

                    foreach ($value as $file) {
                        if (! $this->isValidFile($file)) {
                            $fail(t('An attachment could not be uploaded.', category: 'contact-form'));
                        }
                    }
                },
            ],
        ];

        // Has the project defined any additional fields?
        if ($settings->allowedMessageFields !== null) {
            if (Arr::isAssoc($settings->allowedMessageFields)) {
                // Key-value pairs can be used for advanced control over nested fields:
                foreach ($settings->allowedMessageFields as $field => $fieldRules) {
                    $fieldKey = sprintf('message.%s', $field);

                    $rules[$fieldKey] = $fieldRules;
                }
            } else {
                // A “list” of fields just marks nested fields as permitted, so we can use a plain array rule:
                $rules['message'][] = Rule::array($settings->allowedMessageFields);
                // (Note that we’re *appending* this to the base `required` rule, defined above!)
            }
        }

        return $rules;
    }

    #[Override]
    protected function failedValidation(Validator $validator): void
    {
        Flash::error(t('There was a problem with your submission, please check the form and try again!', category: 'contact-form'));

        parent::failedValidation($validator);
    }
}
