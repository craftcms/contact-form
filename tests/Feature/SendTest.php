<?php

use Illuminate\Support\Facades\Mail;
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
