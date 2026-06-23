<?php

namespace CraftCms\ContactForm\Validation;

use Closure;
use CraftCms\Cms\Cms;
use CraftCms\Cms\Support\Arr;
use CraftCms\Cms\Support\Flash;
use CraftCms\Cms\Validation\Ruleset;
use CraftCms\ContactForm\Plugin;
use CraftCms\ContactForm\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

use Override;
use function CraftCms\Cms\t;

class SubmissionRuleset extends Ruleset
{
    public function prepareForValidation(): void
    {
        $request = $this->resolveSubject();

        $request->merge([
            // `message` can be a single string or a map of “fields”
            'message' => array_filter($request->array('message'), fn ($val) => $val !== '' && $val !== null),
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
                Rule::prohibitedIf(fn () => ! $settings->allowAttachments),
                // function (string $attribute, mixed $value, Closure $fail) use ($settings) {
                //     if (!$settings->allowAttachments) {
                //         $fail(t('Attachments are not allowed.', category: 'contact-form'));
                //     }
                // },
                function (string $attribute, mixed $value, Closure $fail) use ($settings) {
                    // Normalize single files to an array:
                    if ($value instanceof UploadedFile) {
                        $value = [$value];
                    }

                    foreach ($value as $file) {
                        // Was the file uploaded properly?
                        if (! $file->isValid()) {
                            $fail(t('An attachment could not be uploaded.', category: 'contact-form'));
                        }

                        // Does it have a permitted extension?
                        $extension = pathinfo((string) $file->getClientOriginalName(), PATHINFO_EXTENSION);

                        if (! in_array(strtolower($extension), Cms::config()->allowedFileExtensions, true)) {
                            $fail(t('{ext} files (like {filename}) are not allowed.', ['ext' => strtoupper($extension), 'filename' => $file->getClientOriginalName()], 'contact-form'));
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
    protected function failedValidation(Validator $validator): never
    {
        Flash::error(t('There was a problem with your submission, please check the form and try again!', category: 'contact-form'));

        parent::failedValidation($validator);
    }
}
