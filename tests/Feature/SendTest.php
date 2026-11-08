<?php

declare(strict_types=1);

use CraftCms\ContactForm\Events\MessageSending;
use CraftCms\ContactForm\Events\MessageSent;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
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
    config()->set('craft.contact-form.toEmail', $toEmail);

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

it('keeps the success message in JSON responses', function () {
    $this->postJson('/actions/contact-form/send', [
        'fromEmail' => 'jane@example.com',
        'message' => 'Is this thing on?',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Thanks, we got it!');
});
