# Release Notes for Contact Form

## Unreleased

### Added
- Contact Form is now translated into Dutch. ([#139](https://github.com/craftcms/contact-form/pull/139))

### Fixed
- Fixed a bug where the submission email address was not being validated. ([#145]()https://github.com/craftcms/contact-form/issues/145))

## 2.2.2 - 2017-07-19

### Fixed
- Fixed a PHP error introduced in 2.2.1 that broke submissions that were using a single `message` form input.

## 2.2.1 - 2017-07-18

### Fixed
- Fixed a bug where blank messages wouldn’t fail validation if the message was split into multiple fields.

## 2.2.0 - 2017-07-18

### Added
- Contact Form is now translated into Arabic. ([#125](https://github.com/craftcms/contact-form/pull/125))

### Changed
- Contact emails no longer list the Name field if none was provided. ([#126](https://github.com/craftcms/contact-form/issues/126))
- Event listeners for `craft\contactform\Mailer::EVENT_BEFORE_SEND` can now make changes to `craft\contactform\events\SendEvent::$toEmails`, and they will be respected. ([#112](https://github.com/craftcms/contact-form/pull/112))  

### Fixed
- Fixed a bug where single carriage returns in email message bodies were being ignored. ([#118](https://github.com/craftcms/contact-form/issues/118))
- Fixed a bug where HTML in email bodies wasn’t getting escaped. ([#104](https://github.com/craftcms/contact-form/issues/104))
- Fixed an error that occurred when submitting a contact form with an empty file attachment field. ([#116](https://github.com/craftcms/contact-form/pull/116))

## 2.1.1 - 2017-12-04

### Changed
- Loosened the Craft CMS version requirement to allow any 3.x version.

## 2.1.0 - 2017-10-12

### Added
- Added German translations.

### Changed
- Email message bodies now include the sender’s name and email. ([#97](https://github.com/craftcms/contact-form/pull/97))

## 2.0.3 - 2017-09-15

### Added
- Craft 3 Beta 27 compatibility.

## 2.0.2 - 2017-07-07

### Added
- Craft 3 Beta 20 compatibility.

## 2.0.1 - 2017-06-12

### Fixed
- Fixed a bug where the `message` variable was not available to contact form templates when the submission contained validation errors.

## 2.0.0 - 2017-05-16

### Added
- Added Craft 3 compatibility.
- Added the `afterSend` event.

### Changed
- The `beforeSend` event now has `$submission` and `$message` properties, set to the user submission model and the compiled email message, respectively.
- The `contactForm/sendMessage` action is now `contact-form/send`.

### Removed
- Removed honeypot field support. (Moved to the [contact-form-honeypot](https://github.com/craftcms/contact-form-honeypot) plugin.)
- Removed the `beforeMessageCompile` event.
- Removed the `$isValid` property from the `beforeSend` event. Use the `beforeValidate` event on the `Submission` model to prevent submissions from going through.

## 1.8.1 - 2016-09-02

### Fixed
- Fixed a bug where the HTML body of an email was being escaped displaying HTML entities in the email.

## 1.8.0 - 2016-08-18

### Added
- Added the ability for plugins to modify the email's plain text and HTML body via the `contactForm.beforeMessageCompile` event.

### Fixed
- Fixed a bug where Twig code that was entered in the email body or subject was getting parsed.

## 1.7.0 - 2016-01-18

### Added
- Added the ability to access individual message fields values via `message.messageFields` when a validation error occurred. For example, the value of the input `message[Phone]` can now be accessed via `message.messageFields['Phone']`.

### Changed
- Custom message field values only have a single line break between them in the generated email body now, rather than two.

## 1.6.0 - 2015-12-20

### Added
- Added the ability to attach multiple files to the contact email.
- Added the ability to change the flash success message via the "successFlashMessage" setting.
- Added the ability to override plugin settings via a `craft/config/contactform.php` config setting.

### Changed
- The "prependSender" and "prependSubject" settings can now be empty strings.

### Fixed
- Fixed a bug where the "allowAttachments" config setting wasn't being respected.

## 1.5.0 - 2015-12-20

### Added
- Added support for some Craft 2.5 features.

## 1.4.0 - 2014-06-01

### Added
- Added support for passing `{fromName}`, `{fromEmail}`, and `{subject}` in the ‘redirect’ URL.

## 1.3.0 - 2014-01-07

### Added
- Added support for multiple email addresses
- Added the ContactFormService
- Added the `contactForm.beforeSend` event, allowing third party plugins to add extra validation

## 1.2.0 - 2013-12-30

### Added
- Added honeypot captcha support

## 1.1.0 - 2013-12-04

### Added
- Added the ability to submit attachments
- Added the ability to submit the form over Ajax
- Added the ability to submit checkbox lists, which get compiled into comma-separated lists in the email

## 1.0.0 - 2013-10-03

- Initial release
