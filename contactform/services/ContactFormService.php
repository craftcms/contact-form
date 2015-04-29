<?php
namespace Craft;

/**
 * Contact Form service
 */
class ContactFormService extends BaseApplicationComponent
{
	/**
	 * Sends an email submitted through a contact form.
	 *
	 * @param ContactFormModel $message
	 * @throws Exception
	 * @return bool
	 */
	public function sendMessage(ContactFormModel $message)
	{
		$settings = craft()->plugins->getPlugin('contactform')->getSettings();

		if (!$settings->toEmail)
		{
			throw new Exception('The "To Email" address is not set on the plugin’s settings page.');
		}

		// Fire an 'onBeforeSend' event
		Craft::import('plugins.contactform.events.ContactFormEvent');
		$event = new ContactFormEvent($this, array('message' => $message));
		$this->onBeforeSend($event);

		if ($event->isValid)
		{
			if (!$event->fakeIt)
			{
				$toEmails = ArrayHelper::stringToArray($settings->toEmail);

                // translation subject / body
                if (craft()->getEdition() >= Craft::Client)
                {
                    $rebrandMessage = craft()->emailMessages->getMessage('contactform');
                    $translationSubject = $rebrandMessage->subject;
                    $translationBody  = $rebrandMessage->body;
                }
                else
                {
                    $translationSubject = Craft::t('contactform_subject', null, null, 'en_us');
                    $translationBody = Craft::t('contactform_body', null, null, 'en_us');
                }

                // prepend message subject?
                $message->subject = $settings->prependSubject . ($settings->prependSubject && $message->subject ? ' - ' : '') . $message->subject;

                // get email settings
                $emailSettings = craft()->email->getSettings();

				foreach ($toEmails as $toEmail)
				{
					$email = new EmailModel();

					$email->fromEmail = $emailSettings['emailAddress'];
					$email->replyTo   = $message->fromEmail;
					$email->sender    = $emailSettings['emailAddress'];
					$email->fromName  = $settings->prependSender . ($settings->prependSender && $message->fromName ? ' ' : '') . $message->fromName;
					$email->toEmail   = $toEmail;
					$email->subject   = $translationSubject;
					$email->body      = $translationBody;

					if ($message->attachment)
					{
						$email->addAttachment($message->attachment->getTempName(), $message->attachment->getName(), 'base64', $message->attachment->getType());
					}

					craft()->email->sendEmail($email, $message->getAttributes());

				}
			}
			return true;
		}

		return false;
	}

	/**
	 * Fires an 'onBeforeSend' event.
	 *
	 * @param ContactFormEvent $event
	 */
	public function onBeforeSend(ContactFormEvent $event)
	{
		$this->raiseEvent('onBeforeSend', $event);
	}
}
