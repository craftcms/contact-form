<?php
namespace Craft;

class ContactFormController extends BaseController
{
	protected $allowAnonymous = true;

	public function actionSendMessage()
	{
		$this->requirePostRequest();

		$plugin = craft()->plugins->getPlugin('contactform');

		if (!$plugin)
		{
			throw new Exception('Couldn’t find the Contact Form plugin!');
		}

		$settings = $plugin->getSettings();

		if (($toEmail = $settings->toEmail) == null)
		{
			craft()->userSession->setError('The "To Email" address is not set on the plugin’s settings page.');
			Craft::log('Tried to send a contact form request, but missing the "To Email" address on the plugin’s settings page.', LogLevel::Error);
			$this->redirectToPostedUrl();
		}
		else
		{
			$message = new ContactFormModel();
			$savedBody = false;

			$message->fromEmail = craft()->request->getPost('fromEmail');
			$message->fromName  = craft()->request->getPost('fromName');

			if (($postedMessage = craft()->request->getPost('message', null)) != null)
			{
				if (is_array($postedMessage))
				{
					if (isset($postedMessage['body']))
					{
						$savedBody = $postedMessage['body'];
					}

					$message->message = $this->_buildMessage($postedMessage);
				}
				else
				{
					$message->message = $postedMessage;
				}
			}

			$subject = $settings->prependSubject;

			if (($postedSubject = craft()->request->getPost('subject', null)) != null)
			{
				if ($subject)
				{
					$subject .= ' - '.$postedSubject;
				}
				else
				{
					$subject = $postedSubject;
				}
			}

			$message->subject = $subject;

			if ($message->validate())
			{
				$email = new EmailModel();

				$email->fromEmail = $message->fromEmail;
				$email->fromName  = $message->fromName;
				$email->toEmail   = $toEmail;
				$email->subject   = $subject;
				$email->body      = $message->message;

				try
				{
					craft()->email->sendEmail($email);
					craft()->userSession->setNotice('Your message has been sent, someone will be in touch shortly!');

					if (($successRedirectUrl = craft()->request->getPost('successRedirectUrl', null)) != null)
					{
						$this->redirect($successRedirectUrl);
					}
					else
					{
						$this->redirectToPostedUrl();
					}
				}
				catch (\phpmailerException $e)
				{
					Craft::log('Tried to send a contact form request, but something terrible happened: '.$e->getMessage(), LogLevel::Error);
					craft()->userSession->setError(Craft::t('Couldn’t send contact email. Check your email settings.'));
					$this->redirectToPostedUrl();
				}
			}
			else
			{
				craft()->userSession->setError('There was a problem with your submission, please check the form and try again!');
			}

			// If there is a prepended subject, strip it out before we display any errors.
			if ($settings->prependSubject)
			{
				$message->subject = substr($message->subject, strlen($settings->prependSubject) + 3);
			}

			if ($savedBody)
			{
				$message->message = $savedBody;
			}

			craft()->urlManager->setRouteVariables(array(
				'message' => $message
			));
		}
	}

	/**
	 * @param $messages
	 * @return string
	 */
	private function _buildMessage($messages)
	{
		$message = '';

		foreach ($messages as $key => $value)
		{
			$message .= $key.' : '.$value.PHP_EOL.PHP_EOL;
		}

		return $message;
	}
}
