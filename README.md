# Contact Form plugin for Craft

This plugin allows you to add an email contact form to your website.


## Installation

To install Contact Form, follow these steps:

1.  Upload the contactform/ folder to your craft/plugins/ folder.
2.  Go to Settings > Plugins from your Craft control panel and enable the Contact Form plugin.
3.  Click on “Contact Form” to go to the plugin’s settings page, and configure the plugin how you’d like.


## Usage

Your contact form template can look something like this:

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
        <input type="hidden" name="action" value="contactForm/sendMessage">
        <input type="hidden" name="redirect" value="contact/thanks">

        <h3><label for="fromName">Your Name</label></h3>
        <input id="fromName" type="text" name="fromName" value="{% if message is defined %}{{ message.fromName }}{% endif %}">
        {% if message is defined %}{{ errorList(message.getErrors('fromName')) }}{% endif %}

        <h3><label for="fromEmail">Your Email</label></h3>
        <input id="fromEmail" type="text" name="fromEmail" value="{% if message is defined %}{{ message.fromEmail }}{% endif %}">
        {% if message is defined %}{{ errorList(message.getErrors('fromEmail')) }}{% endif %}

        <h3><label for="subject">Subject</label></h3>
        <input id="subject" type="text" name="subject" value="{% if message is defined %}{{ message.subject }}{% endif %}">
        {% if message is defined %}{{ errorList(message.getErrors('subject')) }}{% endif %}

        <h3><label for="message">Message</label></h3>
        <textarea rows="10" cols="40" id="message" name="message">{% if message is defined %}{{ message.message }}{% endif %}</textarea>
        {% if message is defined %}{{ errorList(message.getErrors('message')) }}{% endif %}

        <input type="submit" value="Send">
    </form>

The only required fields are “fromEmail” and “message”. Everything else is optional.

If you have a “redirect” hidden input, the user will get redirected to it upon successfully sending the email.


### Adding additional fields

You can add additional fields to your form by splitting your “message” field into multiple fields, using an array syntax for the input names:

    <h3><label for="message">Message</label></h3>
    <textarea rows="10" cols="40" id="message" name="message[body]">{% if message is defined %}{{ message.message }}{% endif %}</textarea>

    <h3><label for="phone">Your phone number</label></h3>
    <input id="phone" type="text" name="message[Phone]" value="">

    <h3>What services are you interested in?</h3>
    <label><input type="checkbox" name="message[Services][]" value="Design"> Design</label>
    <label><input type="checkbox" name="message[Services][]" value="Development"> Development</label>
    <label><input type="checkbox" name="message[Services][]" value="Strategy"> Strategy</label>
    <label><input type="checkbox" name="message[Services][]" value="Marketing"> Marketing</label>

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


### Adding BCC recipients

You can add additional recipients to a form by using the following Twig function/filter. The example code below will create a hidden input field in your form which contains the encrypted BCC recipient. You can add as many BCC recipients as you wish.

    {{ 'my@email.com' | contact_recipient }}


### File attachments

If you would like your contact form to accept file attachments, follow these steps:

1. Go to Settings > Plugins > Contact Form in your CP and make sure the plugin is set to allow attachments.
2. Make sure your opening HTML `<form>` tag contains `enctype="multipart/form-data"`.
3. Add a `<input type="file" name="attachment">` to your form.


### Ajax form submissions

You can optionally post contact form submissions over Ajax if you’d like. Just send a POST request to your site with all of the same data that would normally be sent:

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


## Changelog

### 1.1

* Addded the ability to submit attachments
* Added the ability to submit the form over Ajax
* Added the ability to submit checkbox lists, which get compiled into comma-separated lists in the email

### 1.0

* Initial release
