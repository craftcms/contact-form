ContactForm plugin for Craft
============================

How to install and use:

1.  Place the contactform folder in your craft/plugins folder.
2.  Go to Settings->Plugins from your Craft control panel and enable the Contact Form plugin.
3.  Click on “Contact Form” to go to the plugin’s settings page.
4.  Enter the email address you would like the contact requests to be sent to.
5.  You may also enter additional prefix fields to allow you to improve the identification of emails when you receive them, if you use the "fromEmail" and "subject" fields in the example below.

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

    <form method="post" action="" accept-charset="UTF-8">
        <input type="hidden" name="action" value="contactForm/sendMessage">
        <input type="hidden" name="successRedirectUrl" value="success">

        <h3><label for="fromEmail">From Email</label></h3>
        <input id="fromEmail" type="text" name="fromEmail" value="{% if message is defined %}{{ message.fromEmail }}{% endif %}">

        {% if message is defined %}
            {{ _self.errorList(message.getErrors('fromEmail')) }}
        {% endif %}
    
        <h3><label for="fromName">From Name</label></h3>
        <input id="fromName" type="text" name="fromName" value="{% if message is defined %}{{ message.fromName }}{% endif %}">

        {% if message is defined %}
            {{ _self.errorList(message.getErrors('fromName')) }}
        {% endif %}

        <h3><label for="subject">Subject</label></h3>
        <input id="subject" type="text" name="subject" value="{% if message is defined %}{{ message.subject }}{% endif %}">

        {% if message is defined %}
            {{ _self.errorList(message.getErrors('subject')) }}
        {% endif %}

        <h3><label for="message">Message</label></h3>
        <textarea rows="10" cols="40" id="message" name="message">{% if message is defined %}{{ message.message }}{% endif %}</textarea>

        {% if message is defined %}
            {{ _self.errorList(message.getErrors('message')) }}
        {% endif %}

        <input type="submit" value="Submit">
    </form>


Note that “fromName” and “subject” are both optional fields.  The only required ones are “fromEmail” and “message”.

If you have a “successRedirectUrl” hidden input, the user will get redirected to it upon successfully sending the email.

If you want to add additional fields to capture, then change your message input name and add the others like so:

    <h3><label for="message">Message</label></h3>
    <textarea rows="10" cols="40" id="message" name="message[body]">{% if message is defined %}{{ message.message }}{% endif %}</textarea>

    <h3><label for="phone">Phone</label></h3>
    <input id="phone" type="text" name="message[phone]" value="">

## Installation

To install Contact Form, copy the contactform/ folder into craft/plugins/, and then go to Settings > Plugins and click the “Install” button next to “Contact Form”. Then click on the “Contact Form” link to edit its settings, where you can choose the email address that should receive contact form submissions, and what the subject text shouldb be.
