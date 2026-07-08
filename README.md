# Contact Form for Craft CMS

Allow your visitors to submit basic information via your website’s front-end, and recieve custom email notifications.

> [!DANGER]
> This is an unreleased version of Contact Form, compatible only with the Craft 6.x alpha.
> It may change in significant ways during this phase of the Laravel transition! 

> [!IMPORTANT]
> A few things have changed in version 3.x that affect how you deal with form data.
> See the [Upgrading](#upgrading) section for more information!

## Requirements

This plugin requires Craft CMS 6.0.0-alpha.1 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the **Plugin Store** in your project’s [control panel](https://craftcms.com/docs/5.x/system/control-panel.html), search for _Contact Form_, then click on the **Install** button.

#### With Composer

Open your terminal and run the following commands:

```bash
# Move to the project directory:
cd /path/to/my-project

# Require the plugin package:
ddev composer require craftcms/contact-form

# Install the plugin:
ddev artisan craft:plugin:install contact-form
```

## Upgrading

With the transition to Laravel, we’re taking advantage of a number of new features, and adopting some patterns that affect your templates.
That said, [configuration](#configuration) and submission requests are entirely compatible.

During the Craft 6.x migration, the [adapter](https://craftcms.com/docs/6.x/) was added to your project, which exposes legacy APIs to plugins and templates.
While Contact Form doesn’t require the adapter itself, you can keep it around to reduce the work required to get your forms working again.

Here is the minimum set of required changes:

1. The `submission` variable is no longer available in templates, when validation fails. Instead, the submitted fields are flashed back to the session, and can be retrieved using the `old()` helper function:
    ```twig
    # Replace...
    <input type="text" name="fromEmail" value="{{ submission.fromEmail ?? '' }}">
    # ...with:
    <input type="text" name="fromEmail" value="{{ old('fromEmail') }}">
    ```
1. Global success and error messages (like the one you configure in the plugin’s settings) are also retrieved differently. Look for instances of `craft.app.session` (in particular, `craft.app.session.getFlash('success')` and `.getFlash('error')`) and replace them with the new `session()` helper function: `session('success')` and `session('error')`. See the section on [flash messages](#displaying-flash-messages) for a complete example.
1. Field-specific error messages are accessed via a new global `errors` variable. This is a new behavior throughout Craft (not specific to Contact Form), so other plugins may require similar updates.
    ```twig
    {# Replace this... #}
    {% if submission.hasErrors('body') %}
        {{ ul(submission.getErrors('body')) }}
    {% endif %}

    {# ...with this: #}
    {% if errors.has('body') %}
        {{ ul(errors.get('body')) }}
    {% endif %}
    ```
    You can abstract this error discovery and output into a Twig macro, as described in the [templating](#displaying-flash-messages) guide, below.

If your project customizes Contact Form behavior via [events](https://craftcms.com/docs/6.x/extend/events.html), you’ll need to adjust the bindings:

| Old Event                                                   | New Event                                                   |
|-------------------------------------------------------------|-------------------------------------------------------------|
| `craft\contactform\models\Submission::EVENT_AFTER_VALIDATE` | See [custom validation rules](#validation-on-extra-fields). |
| `craft\contactform\Mailer::EVENT_BEFORE_SEND`               | Use `CraftCms\ContactForm\Events\MessageSending`            |
| `craft\contactform\Mailer::EVENT_AFTER_SEND`                | Use `CraftCms\ContactForm\Events\MessageSent`               |

> [!IMPORTANT]
> The model validation lifecycle has received significant changes in Craft 6.x.
> If you had previously registered validation rules via `craft\contactform\models\Submission::EVENT_DEFINE_RULES`, you can either set them via configuration, or use the `CraftCms\Cms\Validation\Events\ValidationRulesResolving` event.

## Configuration

Contact Form’s features are controlled from a few different places:

1. **Control Panel** — Visit **Settings** &rarr; **Contact Form** for a guided configuration experience.
2. **System Messages** — The [notification template](#notifications) can be modified by visiting **Utilities** &rarr; **System Messages**.
3. **Plugin Config File** — Create `config/craft/contact-form.php` for [advanced control](#config-reference), including [custom validation rules](#validation-on-extra-fields).

### Config Reference

You can directly configure settings via a `config/craft/contact-form.php` file.
The following options are available:

```php
<?php

return [
    // Configurable via control panel:
    'toEmail' => 'bond@007.com',
    'prependSubject' => '', // Deprecated!
    'prependSender' => '',
    'allowAttachments' => false,
    'successFlashMessage' => 'Message sent!',
    // Configurable via this file only:
    'allowedMessageFields' => [
        // See “Validation on extra fields,” below!
    ],
];
```

> [!WARNING]
> Unrecognized keys can cause errors.

`prependSubject` has been deprecated, in favor of customizing the subject template via **System Messages**.
The default subject template includes this setting’s value, but it is safe to replace it with explicit, localized values.

## Usage

When installed, the plugin registers an “action route” that can recieve regular HTML form submissions and [Ajax](#ajax-form-submissions) requests.
You must submit data to that endpoint (the `/actions/contact-form/send` path) or with the action’s name (`contact-form/send`) in a body parameter named `action`.

### Form Template

A basic contact form template looks something like this:

```twig
{% macro errorList(field) %}
    {% if errors.has(field) %}
        {{ ul(errors.get(field)) }}
    {% endif %}
{% endmacro %}

<form method="post">
    {{ csrfInput() }}
    {{ actionInput('contact-form/send') }}
    {{ redirectInput('contact/thanks') }}

    <h3><label for="from-name">Your Name</label></h3>
    {{ input('text', 'fromName', old('fromName'), {
        id: 'from-name',
        autocomplete: 'name',
    }) }}
    {{ errorList('fromName') }}

    <h3><label for="from-email">Your Email</label></h3>
    {{ input('email', 'fromEmail', old('fromEmail'), {
        id: 'from-email',
        autocomplete: 'email',
    }) }}
    {{ errorList('fromEmail') }}

    <h3><label for="subject">Subject</label></h3>
    {{ input('text', 'subject', old('subject'), {
        id: 'subject',
    }) }}
    {{ errorList('subject') }}

    <h3><label for="message">Message</label></h3>
    {{ tag('textarea', {
        text: old('message'),
        id: 'message',
        name: 'message',
        rows: 10,
        cols: 40,
    }) }}
    {{ errorList(errors, 'message') }}

    <button type="submit">Send</button>
</form>
```

The only required fields are `fromEmail` and `message`; everything else is optional, unless you’ve customized `allowedMessageFields`.

### Displaying flash messages

When a contact form is submitted, the plugin will flash the configured “success” message to the user’s session.
You can display it in your template like this:

```twig
{% if session('success') %}
    <p class="flash success">{{ session('success') }}</p>
{% elseif session('error') %}
    <p class="flash error">{{ session('error') }}</p>
{% endif %}
```

You can override the **Success Flash Message** (`successFlashMessage`) setting on a single form, using the `successMessageInput()` helper:

```twig
{{ successMessageInput('Thanks for inquiring about renting our space! A team member will reply within two business days.') }}
```

### Displaying errors

In addition to the [global flash message](#displaying-flash-messages), field-level validation errors are added to the user’s session, and can be displayed next to each field.
The example above uses a Twig macro to consolidate this output.
For accessibility, we recommend binding the errors to specific inputs, rather than relying on proximity:

```twig
<h3><label for="message">Message</label></h3>
{{ tag('textarea', {
    text: old('message'),
    id: 'message',
    name: 'message',
    rows: 10,
    cols: 40,
    'aria-describedby': errors.has('message') ? 'cf-errorlist-message' : null,
}) }}
{{ errorList(errors, 'message') }}
```

The macro would also require an update:

```twig
{% macro errorList(field) %}
    {% if errors.has(field) %}
        {{ ul(errors.get(field), { id: "cf-errorlist-#{field}" }) }}
    {% endif %}
{% endmacro %}
```

### Redirecting after submit

If you have a `redirect` hidden input, the user will get redirected to it upon successfully sending the email.
All of the submitted data is available to that object template.
For example, if you wanted to redirect to a `contact/thanks` page and pass the sender’s name to it, you could set the input like this:

```twig
{{ redirectInput('contact/thanks?from={fromName}') }}
```

In your `contact/thanks.twig` template, you can access that `from` parameter using `Request.query()`:

```twig
<p>Thanks for sending that in, {{ Request.query('from') }}!</p>
```

If you don’t include a `redirect` input, the current page will get reloaded.

### Additional fields

You can send additional data in your form by splitting the `message` field into multiple sub-fields, using the standard array-like syntax for input names:

```twig
<label for="contact-message">Message</label>
<textarea id="contact-message" name="message[body]">{{ old('message.body') }}</textarea>

<label for="contact-phone">Your phone number</label>
<input id="contact-phone" type="text" name="message[Phone]" value="{{ old('message.Phone') }}">

<h3>What services are you interested in?</h3>
<label>
    <input type="checkbox" name="message[Services][]" value="Design">
    Design
</label>
<label>
    <input type="checkbox" name="message[Services][]" value="Development">
    Development
</label>
<label>
    <input type="checkbox" name="message[Services][]" value="Strategy">
    Strategy
</label>
<label>
    <input type="checkbox" name="message[Services][]" value="Marketing">
    Marketing
</label>
```

> [!TIP]
> If you have a primary “Message” field, you should name it `message[body]`, as shown above.
> Overlapping input `name` attributes (like `message` and `message[Phone]`) can overwrite one another as the form request is built.

To preserve the state of checkboxes, you can test for the presence of the input’s value:

```twig
{{ input('text', 'message[Serivces][]', 'Design', {
    checked: 'Design' in old('message.Services'),
}) }}
```

With the [default notification template](#notification-template), the above form might result in the following message:

```
- **Name:** Cathy Chino
- **Email:** example@email.com
- **Phone:** (555) 123-4567
- **Services:** Design, Development

Hey guys, I really loved this simple contact form (I'm so tired of agencies
asking for everything but my social security number up front), so I trust
you guys know a thing or two about usability.

I run a small coffee shop and we want to start attracting more freelancer-
types to spend their days working from our shop (and sipping fine coffee!).
A clean new website with lots of social media integration would probably
help us out quite a bit there. Can you help us with that?

Hope to hear from you soon.

Cathy
```

#### Validation on extra fields

By default, there are no restrictions on the number, names, or types of nested `message` fields.
You can limit which fields are allowed using the `allowedMessageFields` setting in `config/craft/contact-form.php`:

```php
<?php

return [
    'allowedMessageFields' => ['Phone', 'Services'],
];
```

A `body` field will always be permitted when validating nested input.

Alternatively, provide a key-value map following [Laravel’s validation rule format](https://laravel.com/docs/13.x/validation) for more control over nested field validation:

```php
<?php

use Illuminate\Validation\Rule;

return [
    'allowedMessageFields' => [
        'Phone' => ['nullable', 'string'],
        'Services' => [
            'nullable',
            Rule::in(['Design', 'Development', 'Strategy', 'Marketing', 'Other']),
        ],
    ],
];
```

Validation messages are currently _not_ configurable, so rules should be as simple as possible to keep automatic error messages and localization helpful.

### Notification Template

A recipient designated via the plugin’s settings is sent a notification when a submission is received.
You can customize the subject and body of this message via **Utilities** &rarr; **System Messages**; both fields are rendered as Twig templates, with access to few variables:

- `summary` — An automatically-generated Markdown representation of the incoming data, including [nested `message` fields](#additional-fields).
- `fromEmail`, `fromName`, `subject`, `message`, `attachment`, `...` — Validated input, as provided by the user. `message` may be a string, or an array, depending on what was submitted; use `{% if message is array %}` to check.
- `settings` — The plugin’s complete settings model (an instance of `CraftCms\ContactForm\Settings`).

The default template outputs the `fromName`, `fromEmail`, as well as the `summary`.
If you need more control over the format of this message, you can replace `summary` with whatever fields you expect to be present.

> [!IMPORTANT]
> Use [custom validation rules](#validation-on-extra-fields) to keep the schema of `message` consistent, or guard against missing fields with the null coalescing operator (`??`):
> ```twig
> **Preferred Day:** {{ message.PreferredDay ?? 'Not selected' }}
> ```

### Dynamically adding email recipients

You can programmatically add email recipients from your template by adding a hidden input field named `toEmail` like so:

```twig
<input type="hidden" name="toEmail" value="{{ 'me@example.com'|hash }}">
```

If you want to add multiple recipients, you can provide a comma separated list of emails like so:

```twig
<input type="hidden" name="toEmail" value="{{ 'me@example.com,me2@example.com'|hash }}">
```

Then from your `config/contact-form.php` config file, you’ll need to add a bit of logic:

```php
<?php

$config = [];
$request = Craft::$app->request;

if (
    !$request->getIsConsoleRequest() &&
    ($toEmail = $request->getValidatedBodyParam('toEmail')) !== null
) {
    $config['toEmail'] = $toEmail;
}

return $config;
```

In this example if `toEmail` does not exist or fails validation (it was tampered with), the plugin will fallback to the “To Email” defined in the plugin settings, so you must have that defined as well.

### File attachments

By default, attachments are _not_ allowed.
To enable uploaded attachments, follow these steps:

1. Go to **Settings** → **Contact Form** in the control panel, and enable **Allow attachments**. You may also set `allowAttachments` from your `config/craft/contact-form.php` config file.
2. Make sure your opening HTML `<form>` tag contains `enctype="multipart/form-data"`.
3. Add a `<input type="file" name="attachment">` to your form.
4. If you want to allow multiple file attachments, use [the `multiple` attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Attributes/multiple), and add `[]` to the input’s name: `<input type="file" name="attachment[]" multiple>`.

Uploaded files’ extensions are checked against Craft’s `allowedFileExtensions` config setting.
This does _not_ validate the actual file mime types, file sizes, or other obfuscated content like ZIP files—please inform notification recipients that enabling attachments exposes them to malicious uploads.

#### Limiting upload size

To enforce a maximum attachment size _per file_, add a validation rule via the [ruleset event](#validation-events).

### Ajax form submissions

Contact Form also supports submissions over Ajax. Send a POST request to the same route, with the same data that would normally be sent.

Given this form…

```twig
<form id="my-form" method="post" action="{{ actionUrl('contact-form/send') }}">
  {{ csrfInput() }}

  {# Inputs from other examples... #}

  <button>Send</button>
</form>
```

…your script would look like this:

```js
const $form = document.getElementById('my-form');

$form.addEventListener('submit', function(e) {
    // Prevent the form from submitting natively:
    e.preventDefault();

    // Gather input values:
    const body = new FormData(this);

    fetch(this.action, {
        method: this.method,
        body,
    })
        .then((r) => r.json())
        .then(function(data) {
            // Success and failure responses are structured in the same way!
            console.log(data);

            if (data.errors)
            // -> { message: "Your message has been sent.", submission: { ... } }
            alert(data.message);
        })
        .catch(function(err) {
            // This will only be called for lower-level transport exceptions:
            console.error(err);
            alert(err.message);
        });
});
```

If you are using the `{{ actionInput('contact-form/send') }}` helper function (as in other examples), `this.action` will resolve to the hidden `<input>` element, not a string.
Use an empty string for the fetch URI, instead (`fetch('', { ... })`).

## Extension

Other plugins (and your application) can alter or react to your forms’ behavior via [events](https://craftcms.com/docs/6.x/extend/events.html).
Listeners are either defined as classes in your application’s `Listeners` directory, or a service provider’s `boot()` method.

### Sending Events

The plugin emits a pair of events, before and after a message is sent.

```php
use CraftCms\ContactForm\Events\MessageSending;

Event::listen(function (MessageSending $event) {
    $mailable = $event->message;
});
```

You can suppress the email and return an error by setting `$event->isSpam = true` or returning `false` in the handler.
The “message” (an instance of `CraftCms\Cms\SystemMessage\Mailables\SystemMessageMailable`) contains all the validated submission data (`$event->message->variables`), as well as the notification’s `To` and `From` headers (among others).

> [!WARNING]
> Craft may replace some headers with site-specific overrides as it prepares the message.

When a message is sent, we emit a `CraftCms\ContactForm\Events\MessageSent`:

```php
use CraftCms\ContactForm\Events\MessageSent;

Event::listen(function (MessageSent $event) {
    $addresses = collect($event->message->to)->select('address');
    Log::info(sprintf('Sent a contact form notification to: %s', $addresses->join(', ')));
});
```

Keep in mind that mail in Craft 6.x is delivered by a separate queue process.
Contact Form’s `MessageSent` event fires after handoff to the mailer, but this will often be before the message is actually processed, and before [Laravel’s own mail event](https://laravel.com/docs/13.x/mail#events).
In most cases, failure after handoff to a driver means there is a low-level deliverability issue that can only be solved by the provider or recipient.

### Validation Events

The [ruleset](https://github.com/craftcms/laravel-ruleset-validation) that validates submissions emits an event, giving you an opportunity to add or tweak validation rules, (beyond [message sub-fields](#validation-on-extra-fields)):

```php
use CraftCms\Cms\Validation\Events\ValidationRulesResolving;
use CraftCms\ContactForm\Validation\SubmissionRuleset;

Event::listen(function (ValidationRulesResolving $event) {
    if (! $event->ruleset instanceof SubmissionRuleset) {
        return;
    }

    // Require a name, overwriting the default (['nullable', 'string']):
    $event->rules['name'] = ['string'];

    // Add a new top-level permitted field:
    $event->rules['referredBy'] = ['nullable', 'string'];

    // Limit the message body to 1000 characters:
    $event->addRule('message.body', 'max:1000');

    // Limit each attachment to 500 kilobytes:
    $event->addRule('attachment.*', 'size:500');
});
```

Rules are applied to _all_ submissions!
If you have multiple forms, you must make extra fields `nullable`, or use [conditional rules](https://laravel.com/docs/13.x/validation#conditionally-adding-rules).

### Using a “Honeypot” field

Support for the [honeypot CAPTCHA technique](https://haacked.com/archive/2007/09/11/honeypot-captcha.aspx/) to fight spam has been moved to a [separate plugin](https://github.com/craftcms/contact-form-honeypot).
It has been updated for Craft 6.x, and is a great resource if you are new to extending Craft!
