<?php

declare(strict_types=1);

use CraftCms\Cms\Validation\Events\ValidationRulesResolving;
use CraftCms\ContactForm\Events\MessageSending;
use CraftCms\ContactForm\Events\MessageSent;
use CraftCms\ContactForm\Settings;
use CraftCms\ContactForm\Validation\SubmissionRuleset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

/**
 * @return list<Email>
 */
function sentEmails(): array
{
    return Mail::mailer()->getSymfonyTransport()->messages()
        ->map(fn ($message) => $message->getOriginalMessage())
        ->all();
}

it('sends a valid submission using the configured settings', function () {
    Event::fake([MessageSent::class]);

    $this->from('/contact')
        ->post('/actions/contact-form/send', [
            'fromName' => 'Jane Doe',
            'fromEmail' => 'jane@example.com',
            'subject' => 'Hello',
            'message' => 'Is this thing on?',
        ])
        ->assertRedirect('/contact')
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success', 'Thanks, we got it!');

    $emails = sentEmails();
    expect($emails)->toHaveCount(1);

    Event::assertDispatched(MessageSent::class, fn (MessageSent $event) => $event->message->hasTo('inbox@example.com'));

    $email = $emails[0];
    expect($email->getTo()[0]->getAddress())->toBe('inbox@example.com')
        ->and($email->getReplyTo()[0]->getAddress())->toBe('jane@example.com')
        ->and($email->getReplyTo()[0]->getName())->toBe('Via website: Jane Doe')
        ->and($email->getSubject())->toBe('Website enquiry - Hello')
        ->and($email->getTextBody())->toContain('Is this thing on?');
});

it('returns validation errors for an invalid submission', function () {
    $this->from('/contact')
        ->post('/actions/contact-form/send', [
            'fromName' => 'Jane Doe',
            'fromEmail' => 'not-an-email',
        ])
        ->assertRedirect('/contact')
        ->assertSessionHasErrors(['fromEmail', 'message']);

    expect(sentEmails())->toBeEmpty();
});

it('sends one notification to every configured recipient', function (string|array $toEmail) {
    // The config file is re-applied when plugins load for the request, so set it there too:
    config()->set('craft.contact-form.toEmail', $toEmail);
    app(Settings::class)->toEmail = $toEmail;

    $this->post('/actions/contact-form/send', [
        'fromEmail' => 'jane@example.com',
        'message' => 'Hello',
    ])->assertSessionHas('success');

    $emails = sentEmails();
    expect($emails)->toHaveCount(1)
        ->and(array_map(fn ($address) => $address->getAddress(), $emails[0]->getTo()))
        ->toBe(['inbox@example.com', 'sales@example.com']);
})->with([
    'comma-separated string' => 'inbox@example.com, sales@example.com',
    'array' => [['inbox@example.com', 'sales@example.com']],
]);

it('returns the failure response when the notification is not handed off', function (Closure $sabotage) {
    $sabotage();

    $this->from('/contact')
        ->post('/actions/contact-form/send', [
            'fromName' => 'Jane Doe',
            'fromEmail' => 'jane@example.com',
            'message' => 'Hello',
        ])
        ->assertRedirect('/contact')
        ->assertSessionHas('error', 'There was a problem with your submission, please check the form and try again!');
})->with([
    'marked as spam' => [fn () => Event::listen(fn (MessageSending $event) => $event->isSpam = true)],
    'stopped by a listener' => [fn () => Event::listen(fn (MessageSending $event) => $event->isValid = false)],
    'transport failure' => [function () {
        Mail::extend('failing', fn () => new class extends AbstractTransport
        {
            protected function doSend(SentMessage $message): void
            {
                throw new TransportException('Connection refused');
            }

            public function __toString(): string
            {
                return 'failing://';
            }
        });
        config()->set('mail.mailers.failing', ['transport' => 'failing']);
        config()->set('mail.default', 'failing');
        Mail::forgetMailers();
    }],
]);

it('renders submitted fields into the posted redirect URL', function () {
    $this->post('/actions/contact-form/send', [
        'fromName' => 'Jane',
        'fromEmail' => 'jane@example.com',
        'message' => 'Hello',
        'redirect' => Crypt::encrypt('contact/thanks?from={fromName}'),
    ])->assertRedirectContains('contact/thanks?from=Jane');
});

it('URL-encodes submitted fields in the posted redirect URL', function () {
    $this->post('/actions/contact-form/send', [
        'fromName' => 'Tom & Jerry?x=1#top',
        'fromEmail' => 'jane@example.com',
        'message' => 'Hello',
        'redirect' => Crypt::encrypt('contact/thanks?from={fromName}'),
    ])->assertRedirectContains('contact/thanks?from=Tom+%26+Jerry%3Fx%3D1%23top');
});

it('formats a plain-text message without a name', function () {
    $this->post('/actions/contact-form/send', [
        'fromEmail' => 'jane@example.com',
        'message' => "First line\r\nSecond line",
    ])->assertSessionHas('success');

    $email = sentEmails()[0];
    expect($email->getTextBody())
        ->toContain("- **Email:** jane@example.com\n\nFirst line\nSecond line")
        ->not->toContain('From:')
        ->and($email->getReplyTo()[0]->getName())->toBe('');
});

it('fails gracefully when there are no recipients', function () {
    config()->set('craft.contact-form.toEmail', '');
    app(Settings::class)->toEmail = '';

    $this->from('/contact')
        ->post('/actions/contact-form/send', [
            'fromEmail' => 'jane@example.com',
            'message' => 'Hello',
        ])
        ->assertRedirect('/contact')
        ->assertSessionHas('error');

    expect(sentEmails())->toBeEmpty();
});

it('validates attachments', function (array $payload, string $error) {
    config()->set('craft.contact-form.allowAttachments', true);
    app(Settings::class)->allowAttachments = true;

    $this->postJson('/actions/contact-form/send', [
        'fromEmail' => 'jane@example.com',
        'message' => 'Hello',
        ...$payload,
    ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.attachment.0', $error);
})->with([
    'text instead of a file' => [['attachment' => 'not-a-file'], 'An attachment could not be uploaded.'],
    'text in a file list' => [['attachment' => ['not-a-file']], 'An attachment could not be uploaded.'],
    'disallowed extension' => [fn () => ['attachment' => UploadedFile::fake()->create('evil.php')], 'PHP files (like evil.php) are not allowed.'],
    'no extension' => [fn () => ['attachment' => UploadedFile::fake()->create('README')], 'Files without an extension (like README) are not allowed.'],
]);

it('limits message sub-fields to allowedMessageFields', function (array $allowed, array $message, ?string $error) {
    config()->set('craft.contact-form.allowedMessageFields', $allowed);
    app(Settings::class)->allowedMessageFields = $allowed;

    $response = $this->postJson('/actions/contact-form/send', [
        'fromEmail' => 'jane@example.com',
        'message' => $message,
    ]);

    $error === null
        ? $response->assertOk()
        : $response->assertUnprocessable()->assertJsonPath('errors.message.0', $error);
})->with([
    'list allows body' => [['Phone'], ['Phone' => '555', 'body' => 'Hi'], null],
    'list rejects others' => [['Phone'], ['Phone' => '555', 'Hack' => 'x'], 'Hack can’t be submitted with this form.'],
    'empty list rejects all but body' => [[], ['Phone' => '555'], 'Phone can’t be submitted with this form.'],
    'rules map rejects others' => [['Phone' => ['nullable', 'string']], ['Hack' => 'x'], 'Hack can’t be submitted with this form.'],
    'rules map validates checkboxes' => [['Services' => ['nullable', 'array', Rule::in(['Design', 'Development'])]], ['Services' => ['Design', 'Development']], null],
]);

it('lets listeners add rules to built-in fields', function () {
    Event::listen(function (ValidationRulesResolving $event) {
        if ($event->ruleset instanceof SubmissionRuleset) {
            $event->addRule('message.body', 'max:5');
        }
    });

    $this->postJson('/actions/contact-form/send', [
        'fromEmail' => 'jane@example.com',
        'message' => ['body' => 'Too long'],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('message.body');
});

it('responds to rate-limited submissions like other failures', function () {
    config()->set('craft.contact-form.rateLimit', 1);
    app(Settings::class)->rateLimit = 1;
    $submission = ['fromEmail' => 'jane@example.com', 'message' => 'Hello'];

    $this->post('/actions/contact-form/send', $submission)->assertSessionHas('success');

    $this->from('/contact')
        ->post('/actions/contact-form/send', $submission)
        ->assertRedirect('/contact')
        ->assertSessionHas('error');

    $this->postJson('/actions/contact-form/send', $submission)
        ->assertTooManyRequests()
        ->assertJsonStructure(['message']);
});

it('keeps the success message in JSON responses', function () {
    $this->postJson('/actions/contact-form/send', [
        'fromEmail' => 'jane@example.com',
        'message' => 'Is this thing on?',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Thanks, we got it!');
});
