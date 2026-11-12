<?php

declare(strict_types=1);

namespace CraftCms\ContactForm\Validation;

use Closure;
use CraftCms\Cms\Cms;
use CraftCms\Cms\Support\Arr;
use CraftCms\Cms\Support\Flash;
use CraftCms\Cms\Validation\Ruleset;
use CraftCms\ContactForm\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Override;

use function CraftCms\Cms\t;

class SubmissionRuleset extends Ruleset
{
    public function rules(): array
    {
        $settings = app(Settings::class);

        $rules = [
            'fromEmail' => ['required', 'email'],
            'fromName' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
            // Some form of a `message` is required, but its type is flexible...
            'message' => ['required'],
            // ...and `body` is always an allowed sub-field. (Rules stay arrays, so listeners can append to them.)
            'message.body' => ['nullable', 'string'],
            'attachment' => [
                'nullable',
                Rule::prohibitedIf(fn () => ! $settings->allowAttachments),
                function (string $attribute, mixed $value, Closure $fail) {
                    // Normalize single files to an array:
                    foreach (Arr::wrap($value) as $file) {
                        // Was the file uploaded properly? (Text posted under the file’s name doesn’t count.)
                        if (! $file instanceof UploadedFile || ! $file->isValid()) {
                            $fail(t('An attachment could not be uploaded.', category: 'contact-form'));

                            continue;
                        }

                        // Does it have a permitted extension?
                        $filename = (string) $file->getClientOriginalName();
                        $extension = pathinfo($filename, PATHINFO_EXTENSION);

                        if ($extension === '') {
                            $fail(t('Files without an extension (like {filename}) are not allowed.', ['filename' => $filename], 'contact-form'));
                        } elseif (! in_array(strtolower($extension), Cms::config()->allowedFileExtensions, true)) {
                            $fail(t('{ext} files (like {filename}) are not allowed.', ['ext' => strtoupper($extension), 'filename' => $filename], 'contact-form'));
                        }
                    }
                },
            ],
        ];

        // Has the project limited which sub-fields can be sent?
        if ($settings->allowedMessageFields !== null) {
            $isMap = Arr::isAssoc($settings->allowedMessageFields);
            $allowedFields = $isMap ? array_keys($settings->allowedMessageFields) : $settings->allowedMessageFields;

            // Reject any other sub-fields (`body` is always allowed):
            $rules['message'][] = function (string $attribute, mixed $value, Closure $fail) use ($allowedFields) {
                if (! is_array($value)) {
                    return;
                }

                $unknown = array_diff(array_keys($value), [...$allowedFields, 'body']);

                if ($unknown !== []) {
                    $fail(t('{fields} can’t be submitted with this form.', ['fields' => implode(', ', $unknown)], 'contact-form'));
                }
            };

            // Key-value pairs can be used for advanced control over nested fields:
            if ($isMap) {
                foreach ($settings->allowedMessageFields as $field => $fieldRules) {
                    // Only nested `message` values get these rules (they’re skipped when `message` is a string):
                    $rules["message.$field"] = [Rule::when(fn ($data) => is_array($data['message'] ?? null), $fieldRules)];
                }
            }
        }

        return $rules;
    }

    #[Override]
    public function attributes(): array
    {
        $attributes = [
            'fromName' => t('Name', category: 'contact-form'),
            'fromEmail' => t('Email', category: 'contact-form'),
            'subject' => t('Subject', category: 'contact-form'),
            'message' => t('Message', category: 'contact-form'),
            'message.body' => t('Message', category: 'contact-form'),
            'attachment' => t('Attachment', category: 'contact-form'),
            'attachment.*' => t('Attachment', category: 'contact-form'),
        ];

        // Label sub-fields the way notifications do:
        $fields = app(Settings::class)->allowedMessageFields ?? [];

        foreach (Arr::isAssoc($fields) ? array_keys($fields) : $fields as $field) {
            $attributes["message.$field"] = t($field, category: 'site');
        }

        return $attributes;
    }

    #[Override]
    protected function failedValidation(Validator $validator): never
    {
        Flash::error(t('There was a problem with your submission, please check the form and try again!', category: 'contact-form'));

        parent::failedValidation($validator);
    }
}
