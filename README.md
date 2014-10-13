# Contact Form plugin for Craft

This plugin allows you to add an email contact form to your website.


## Installation

To install Contact Form, follow these steps:

1.  Upload the contactform/ folder to your craft/plugins/ folder.
2.  Go to Settings > Plugins from your Craft control panel and enable the Contact Form plugin.
3.  Click on “Contact Form” to go to the plugin’s settings page, and configure the plugin how you’d like.

## Usage

Your contact form template can look something like this:

```jinja
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
    {{ getCsrfInput() }}
    <input type="hidden" name="action" value="contactForm/sendMessage">
    <input type="hidden" name="redirect" value="contact/thanks">

    <h3><label for="fromName">Your Name</label></h3>
    <input id="fromName" type="text" name="fromName" value="{% if message is defined %}{{ message.fromName }}{% endif %}">
    {{ message is defined and message ? errorList(message.getErrors('fromName')) }}

    <h3><label for="fromEmail">Your Email</label></h3>
    <input id="fromEmail" type="text" name="fromEmail" value="{% if message is defined %}{{ message.fromEmail }}{% endif %}">
    {{ message is defined and message ? errorList(message.getErrors('fromEmail')) }}

    <h3><label for="subject">Subject</label></h3>
    <input id="subject" type="text" name="subject" value="{% if message is defined %}{{ message.subject }}{% endif %}">
    {{ message is defined and message ? errorList(message.getErrors('subject')) }}

    <h3><label for="message">Message</label></h3>
    <textarea rows="10" cols="40" id="message" name="message">{% if message is defined %}{{ message.message }}{% endif %}</textarea>
    {{ message is defined and message ? errorList(message.getErrors('message')) }}

    <input type="submit" value="Send">
</form>
```

The only required fields are “fromEmail” and “message”. Everything else is optional.

### Redirecting after submit

If you have a ‘redirect’ hidden input, the user will get redirected to it upon successfully sending the email. The following variables can be used within the URL/path you set:

- `{fromName}`
- `{fromEmail}`
- `{subject}`

For example, if you wanted to redirect to a “contact/thanks” page and pass the sender’s name to it, you could set the input like this:

    <input type="hidden" name="redirect" value="contact/thanks?from={fromName}">

On your contact/thanks.html template, you can access that ‘from’ parameter using [craft.request.getQuery()](http://buildwithcraft.com/docs/templating/craft.request#getQuery):

```jinja
<p>Thanks for sending that in, {{ craft.request.getQuery('from') }}!</p>
```

Note that if you don’t include a ‘redirect’ input, the current page will get reloaded.


### Adding additional fields

You can add additional fields to your form by splitting your “message” field into multiple fields, using an array syntax for the input names:

```jinja
<h3><label for="message">Message</label></h3>
<textarea rows="10" cols="40" id="message" name="message[body]">{% if message is defined %}{{ message.message }}{% endif %}</textarea>

<h3><label for="phone">Your phone number</label></h3>
<input id="phone" type="text" name="message[Phone]" value="">

<h3>What services are you interested in?</h3>
<label><input type="checkbox" name="message[Services][]" value="Design"> Design</label>
<label><input type="checkbox" name="message[Services][]" value="Development"> Development</label>
<label><input type="checkbox" name="message[Services][]" value="Strategy"> Strategy</label>
<label><input type="checkbox" name="message[Services][]" value="Marketing"> Marketing</label>
```

If you have a primary “Message” field, you should name it ``message[body]``, like in that example.

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


### The “Honeypot” field
The [Honeypot Captcha][honeypot] is a simple anti-spam technique, which greatly reduces the efficacy of spambots without expecting your visitors to decipher various tortured letterforms.

[honeypot]: http://haacked.com/archive/2007/09/11/honeypot-captcha.aspx/ "The origins of the Honeypot Captcha"

In brief, it works like this:

1. You add a normal text field (our “honeypot”) to your form, and hide it using CSS.
2. Normal (human) visitors won't fill out this invisible text field, but those crazy spambots will.
3. The ContactForm plugin checks to see if the “honeypot” form field contains text. If it does, it assumes the form was submitted by “Evil People”, and ignores it (but pretends that everything is A-OK, so the evildoer is none the wiser).

### Example “Honeypot” implementation
When naming your form field, it's probably best to avoid monikers such as “dieEvilSpammers”, in favour of something a little more tempting. For example:

```html
<input id="preferredKitten" name="preferredKitten" type="text">
```

In this case, you could hide your form field using the following CSS:

```css
input#preferredKitten { display: none; }
```

### File attachments

If you would like your contact form to accept file attachments, follow these steps:

1. Go to Settings > Plugins > Contact Form in your CP and make sure the plugin is set to allow attachments.
2. Make sure your opening HTML `<form>` tag contains `enctype="multipart/form-data"`.
3. Add a `<input type="file" name="attachment">` to your form.


### Ajax form submissions

You can optionally post contact form submissions over Ajax if you’d like. Just send a POST request to your site with all of the same data that would normally be sent:

```js
$('#my-form').submit(function(ev) {
    // Prevent the form from actually submitting
    ev.preventDefault();

    // Get the post data
    var data = $(this).serialize();

    // Send it to the server
    $.post('/', data, function(response) {
        if (response.success) {
            $('#thanks').fadeIn();
        } else {
            // response.error will be an object containing any validation errors that occurred, indexed by field name
            // e.g. response.error.fromName => ['From Name is required']
            alert('An error occurred. Please try again.');
        }
    });
});
```

### The `contactForm.beforeSend` event

Other plugins can be notified right before an email is sent with the Contact Form plugin,
and they are even given a chance to prevent the email from getting sent at all.

```php
class SomePlugin extends BasePlugin
{
    // ...

    public function init()
    {
        craft()->on('contactForm.beforeSend', function(ContactFormEvent $event) {
            $message = $event->params['message'];

            // ...

            if ($isVulgar)
            {
                // Setting $isValid to false will cause a validation error
                // and prevent the email from being sent

                $message->addError('message', 'Do you kiss your mother with those lips?');
                $event->isValid = false;
            }

            if ($isSpam)
            {
                // Setting $fakeIt to true will make things look as if the email was sent,
                // but really it wasn't

                $event->fakeIt = true;
            }
        });
    }
}
```

## Changelog

### 1.4

* Added support for passing `{fromName}`, `{fromEmail}`, and `{subject}` in the ‘redirect’ URL.

### 1.3

* Added support for multiple email addresses
* Added the ContactFormService
* Added the `contactForm.beforeSend` event, allowing third party plugins to add extra validation

### 1.2

* Added honeypot captcha support

### 1.1

* Added the ability to submit attachments
* Added the ability to submit the form over Ajax
* Added the ability to submit checkbox lists, which get compiled into comma-separated lists in the email

### 1.0

* Initial release
