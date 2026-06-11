<?php

namespace CraftCms\ContactForm\Validation;

use CraftCms\Cms\Support\Flash;
use CraftCms\Cms\Validation\Ruleset;
use Illuminate\Validation\Validator;
use Override;
use function CraftCms\Cms\t;

class SubmissionRules extends Ruleset
{
    public function prepareForValidation(): void
    {
        // Rulesets instantiated without an explicit subject validate the request:
        $request = $this->resolveSubject();

        // The request can be manipulated
        $request->merge([
            // `message` can be a single string or a map of “fields”
            'message' => array_filter($request->array('message'), fn ($val) => $val !== '' && $val !== null),
            // Force “attachment” to be returned as an array:
            'attachment' => $request->array('attachment'),
        ]);
    }

    public function rules(): array
    {
        return [
            'fromEmail' => ['required', 'email'],
            'fromName' => ['required', 'string'],
            'message' => ['required'],
            'attachment.*' => ['file'],
        ];
    }

    public function attributes(): array
    {
        return [
            'fromName' => t('Your Name', category: 'contact-form'),
            'fromEmail' => t('Your Email', category: 'contact-form'),
            'message' => t('Message', category: 'contact-form'),
            'subject' => t('Subject', category: 'contact-form'),
        ];
    }

    #[Override]
    protected function failedValidation(Validator $validator): never
    {
        Flash::error(t('There was a problem with your submission, please check the form and try again!', category: 'contact-form'));

        parent::failedValidation($validator);
    }
}
