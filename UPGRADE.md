# Upgrading to Contact Form 4.0 (Craft 6)

Contact Form 4.0 is a native Craft 6 plugin. This guide lists what changed from 3.x and what to update in your project.

## Requirements

- Craft CMS 6.0 or later
- PHP 8.5 or later
- The `craftcms/yii2-adapter` package is not needed.

## Upgrade steps

1. Upgrade your project to Craft 6 first.
2. Update the plugin: `composer require craftcms/contact-form:^4.0`.
3. If you have a `config/contact-form.php` file, move it to `config/craft/contact-form.php`. Otherwise you can create one with `php artisan vendor:publish --tag=contact-form`.
4. Update your templates and any event listeners as described below.

Settings saved in the control panel are kept. Form submissions still post to the `contact-form/send` action, with the same field names.

## Breaking changes

### Templates

When a submission fails validation, the visitor is redirected back to the form, and the submitted data is flashed to the session. The `submission` variable is no longer available.

| Before | After |
| --- | --- |
| `submission.fromEmail ?? ''` | `old('fromEmail')` |
| `submission.message.body ?? ''` | `old('message.body')` |
| `submission.getErrors('fromEmail')` | `errors.get('fromEmail')` |
| `craft.app.session.getFlash('success')` | `session('success')` |
| `craft.app.session.getFlash('error')` | `session('error')` |

Before:

```twig
<input type="email" name="fromEmail" value="{{ submission.fromEmail ?? '' }}">
{% if submission and submission.hasErrors('fromEmail') %}
    {{ ul(submission.getErrors('fromEmail')) }}
{% endif %}
```

After:

```twig
<input type="email" name="fromEmail" value="{{ old('fromEmail') }}">
{% if errors.has('fromEmail') %}
    {{ ul(errors.get('fromEmail')) }}
{% endif %}
```

See the [form template](README.md#form-template) in the README for a complete example.

### Notifications

- The notification’s subject and body are now edited in **Utilities** → **System Messages**, under “When the contact form is submitted”. That utility requires Craft Pro; other editions use the default notification.
- One notification is sent to all of the **To Email** recipients, rather than one notification per recipient.
- The submitter’s name and email are set as the `Reply-To` address. The `From` address is your system email address.

### Ajax responses

- Validation errors return a `422` response with `message` and `errors` keys.
- Submissions over the `rateLimit` return a `429` response with a `message` key.
- Other failures return a `400` response.
- Successful responses include the submitted fields under a `submission` key, alongside `message`.

### Events

Yii events were replaced by Laravel events.

| Before | After |
| --- | --- |
| `craft\contactform\Mailer::EVENT_BEFORE_SEND` | `CraftCms\ContactForm\Events\MessageSending` |
| `craft\contactform\Mailer::EVENT_AFTER_SEND` | `CraftCms\ContactForm\Events\MessageSent` |
| `craft\contactform\models\Submission::EVENT_AFTER_VALIDATE` | `CraftCms\Cms\Validation\Events\ValidationRulesResolving` |

Before:

```php
use craft\contactform\events\SendEvent;
use craft\contactform\Mailer;
use yii\base\Event;

Event::on(Mailer::class, Mailer::EVENT_BEFORE_SEND, function(SendEvent $e) {
    if (isSpam($e->submission)) {
        $e->isSpam = true;
    }
});
```

After:

```php
use CraftCms\ContactForm\Events\MessageSending;
use Illuminate\Support\Facades\Event;

Event::listen(function (MessageSending $event): void {
    if (isSpam($event->message->variables)) {
        $event->isSpam = true;
    }
});
```

- `MessageSending::$message` and `MessageSent::$message` are `CraftCms\Cms\SystemMessage\Mailables\SystemMessageMailable` instances. The submitted fields are in `$message->variables`, and the recipients in `$message->to`.
- Set `$event->isSpam = true` or `$event->isValid = false` on `MessageSending` to stop the notification. The visitor sees the same error either way.
- To change recipients, update `$event->message->to`. The `toEmails` property is gone.
- Validation rules for submissions can be added from a `ValidationRulesResolving` listener, or with the `allowedMessageFields` setting. See [Validation events](README.md#validation-events).

### Classes and namespaces

| Before | After |
| --- | --- |
| `craft\contactform\Plugin` | `CraftCms\ContactForm\Plugin` |
| `craft\contactform\models\Settings` | `CraftCms\ContactForm\Settings` |
| `craft\contactform\controllers\SendController` | `CraftCms\ContactForm\Http\Controllers\SendController` |
| `craft\contactform\models\Submission` | `CraftCms\ContactForm\Validation\SubmissionRuleset` validates the request data. |
| `craft\contactform\Mailer` | Removed. The controller sends a `SystemMessageMailable`. |
| `craft\contactform\events\SendEvent` | `CraftCms\ContactForm\Events\MessageSending` and `MessageSent` |

The plugin’s settings can be injected, or resolved with `app(\CraftCms\ContactForm\Settings::class)`.

### Settings and config

- The config file moved from `config/contact-form.php` to `config/craft/contact-form.php`.
- `allowedMessageFields` can also be a map of Laravel validation rules for each `message` sub-field.
- Added the `rateLimit` setting, which limits submissions per minute for each IP address. It can only be set in the config file.
- `allowedMessageFields` now rejects any `message` sub-field it doesn’t list (except `body`), whether it’s a list of names or a map of rules.
- `prependSubject` is deprecated. Edit the subject in **Utilities** → **System Messages** instead.
- The config file is loaded before Craft is bootstrapped, so it can’t reference `Craft::$app`. To send to recipients chosen in the template, use a `MessageSending` listener. See [Dynamically adding email recipients](README.md#dynamically-adding-email-recipients).

## Removed

- `craft\contactform\Plugin::getMailer()` and the `mailer` component.
- The `submission` template variable.
