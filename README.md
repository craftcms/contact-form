# Contact Form for Craft CMS

This plugin allows you to add an email contact form to your website.


## Requirements

This plugin requires Craft CMS 3.0.0-RC11 or later. (For Craft 2 use the [`v1` branch](https://github.com/craftcms/contact-form/tree/v1).)


## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Contact Form”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/contact-form

# tell Craft to install the plugin
./craft install/plugin contact-form
```

## Upgrading from Craft 2

If you’re in the process of upgrading a Craft 2 project to Craft 3, follow these instructions to get Contact Form back up and running:

1. [Install Contact Form 2.x](#installation).
2. Update the `action` input values in your form templates from `contactForm/sendMessage` to `contact-form/send`.
3. Make sure the `redirect` input values in your form templates are [hashed](https://docs.craftcms.com/v3/changes-in-craft-3.html#request-params).
4. If you have a `craft/config/contactform.php` file, move it to `config/` and rename it to `contact-form.php`.
5. If you were using the honeypot captcha feature, install the new [Contact Form Honeypot](https://github.com/craftcms/contact-form-honeypot) plugin.

## Usage

Your contact form template can look something like this:

```twig
{% macro errorList(errors) %}
    {% if errors %}
        <ul class="errors">
            {% for error in errors %}
                <li>{{ error }}</li>
            {% endfor %}
        </ul>
    {% endif %}
{% endmacro %}

{% from _self import errorList %}

<form method="post" action="" accept-charset="UTF-8">
    {{ csrfInput() }}
    <input type="hidden" name="action" value="contact-form/send">
    {{ redirectInput('contact/thanks') }}

    <h3><label for="from-name">Your Name</label></h3>
    <input id="from-name" type="text" name="fromName" value="{{ message.fromName ?? '' }}">
    {{ message is defined and message ? errorList(message.getErrors('fromName')) }}

    <h3><label for="from-email">Your Email</label></h3>
    <input id="from-email" type="email" name="fromEmail" value="{{ message.fromEmail ?? '' }}">
    {{ message is defined and message ? errorList(message.getErrors('fromEmail')) }}

    <h3><label for="subject">Subject</label></h3>
    <input id="subject" type="text" name="subject" value="{{ message.subject ?? '' }}">
    {{ message is defined and message ? errorList(message.getErrors('subject')) }}

    <h3><label for="message">Message</label></h3>
    <textarea rows="10" cols="40" id="message" name="message">{{ message.message ?? '' }}</textarea>
    {{ message is defined and message ? errorList(message.getErrors('message')) }}

    <input type="submit" value="Send">
</form>
```

The only required fields are `fromEmail` and `message`. Everything else is optional.

### Redirecting after submit

If you have a `redirect` hidden input, the user will get redirected to it upon successfully sending the email. The following variables can be used within the URL/path you set:

- `{fromName}`
- `{fromEmail}`
- `{subject}`

For example, if you wanted to redirect to a `contact/thanks` page and pass the sender’s name to it, you could set the input like this:

```twig
{{ redirectInput('contact/thanks?from={fromName}') }}
```

On your `contact/thanks.html` template, you can access that `from` parameter using `craft.app.request.getQueryParam()`:

```twig
<p>Thanks for sending that in, {{ craft.app.request.getQueryParam('from') }}!</p>
```

Note that if you don’t include a `redirect` input, the current page will get reloaded.

### Displaying flash messages

When a contact form is submitted, the plugin will set a `notice` or `success` flash message on the user session. You can display it in your template like this:

```twig
{% if craft.app.session.hasFlash('notice') %}
    <p class="message notice">{{ craft.app.session.getFlash('notice') }}</p>
{% elseif craft.app.session.hasFlash('error') %}
    <p class="message error">{{ craft.app.session.getFlash('error') }}</p>
{% endif %}
```

### Adding additional fields

You can add additional fields to your form by splitting your `message` field into multiple fields, using an array syntax for the input names:

```twig
<h3><label for="message">Message</label></h3>
<textarea rows="10" cols="40" id="message" name="message[body]">{{ message.message.body ?? '' }}</textarea>

<h3><label for="phone">Your phone number</label></h3>
<input id="phone" type="text" name="message[Phone]" value="">

<h3>What services are you interested in?</h3>
<label><input type="checkbox" name="message[Services][]" value="Design"> Design</label>
<label><input type="checkbox" name="message[Services][]" value="Development"> Development</label>
<label><input type="checkbox" name="message[Services][]" value="Strategy"> Strategy</label>
<label><input type="checkbox" name="message[Services][]" value="Marketing"> Marketing</label>
```

If you have a primary “Message” field, you should name it `message[body]`, like in that example.

An email sent with the above form might result in the following message:

    Phone: (555) 123-4567

    Services: Design, Development

    Hey guys, I really loved this simple contact form (I'm so tired of agencies
    asking for everything but my social security number up front), so I trust
    you guys know a thing or two about usability.

    I run a small coffee shop and we want to start attracting more freelancer-
    types to spend their days working from our shop (and sipping fine coffee!).
    A clean new website with lots of social media integration would probably
    help us out quite a bit there. Can you help us with that?

    Hope to hear from you soon.

    Cathy Chino

### Overriding plugin settings

If you create a [config file](https://craftcms.com/docs/config-settings) in your `config/` folder called `contact-form.php`, you can override
the plugin’s settings in the Control Panel.  Since that config file is fully [multi-environment](https://craftcms.com/docs/multi-environment-configs) aware, this is
a handy way to have different settings across multiple environments.

Here’s what that config file might look like along with a list of all of the possible values you can override.

```php
<?php

return [
    'toEmail'             => 'bond@007.com',
    'prependSubject'      => '',
    'prependSender'       => '',
    'allowAttachments'    => false,
    'successFlashMessage' => 'Message sent!'
];
```

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

If you would like your contact form to accept file attachments, follow these steps:

1. Go to Settings → Contact Form in the Control Panel, and make sure the plugin is set to allow attachments.
2. Make sure your opening HTML `<form>` tag contains `enctype="multipart/form-data"`.
3. Add a `<input type="file" name="attachment">` to your form.
4. If you want to allow multiple file attachments, use multiple `<input type="file" name="attachment[]" multiple>` inputs.


### Ajax form submissions

You can optionally post contact form submissions over Ajax if you’d like. Just send a POST request to your site with all of the same data that would normally be sent:

```js
$('#my-form').submit(function(ev) {
    // Prevent the form from actually submitting
    ev.preventDefault();

    // Send it to the server
    $.post({
        url: '/',
        dataType: 'json',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                $('#thanks').fadeIn();
            } else {
                // response.error will be an object containing any validation errors that occurred, indexed by field name
                // e.g. response.error.fromName => ['From Name is required']
                alert('An error occurred. Please try again.');
            }
        }
    });
});
```

### The `afterValidate` event

Modules and plugins can be notified when a submission is being validated, providing their own custom validation logic, using the `afterValidate` event on the `Submission` model:

```php
use craft\contactform\models\Submission;
use yii\base\Event;
use yii\base\Event;

// ...

Event::on(Submission::class, Submission::EVENT_AFTER_VALIDATE, function(Event $e) {
    /** @var Submission $submission */
    $submission = $e->sender;
    
    // Make sure that `message[Phone]` was filled in
    if (empty($submission->message['Phone']) {
        // Add the error
        // (This will be accessible via `message.getErrors('message.phone')` in the template.)
        $submission->addError('message.phone', 'A phone number is required.');
    }
});
```


### The `beforeSend` event

Modules and plugins can be notified right before a message is sent out to the recipients using the `beforeSend` event. This is also an opportunity to flag the message as spam, preventing it from getting sent:

```php
use craft\contactform\events\SendEvent;
use craft\contactform\Mailer;
use yii\base\Event;

// ...

Event::on(Mailer::class, Mailer::EVENT_BEFORE_SEND, function(SendEvent $e) {
    $isSpam = // custom spam detection logic...

    if (!$isSpam) {
        $e->isSpam = true;
    }
});
```


### The `afterSend` event

Modules and plugins can be notified right after a message is sent out to the recipients using the `afterSend` event.

```php
use craft\contactform\events\SendEvent;
use craft\contactform\Mailer;
use yii\base\Event;

// ...

Event::on(Mailer::class, Mailer::EVENT_AFTER_SEND, function(SendEvent $e) {
    // custom logic...
});
```

### Using a “Honeypot” field

Support for the [honeypot captcha technique](https://haacked.com/archive/2007/09/11/honeypot-captcha.aspx/) to fight spam has been moved to a [separate plugin](https://github.com/craftcms/contact-form-honeypot) that complements this one.
