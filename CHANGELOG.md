Changelog
=========

## 1.9.0 - 2017-06-29

### Fixed
- Fixed a bug where if a plugin added errors to the `message` model in the `onBeforeSend()` event, they would not make it back to the template to display. ([#84](https://github.com/craftcms/cms/issues/84))

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
