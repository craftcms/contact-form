<?php
namespace Craft;

/**
 * Contact Form controller
 */
class ContactFormController extends BaseController
{
	/**
	 * @var Allows anonymous access to this controller's actions.
	 * @access protected
	 */
	protected $allowAnonymous = true;


	/**
	 * Sends an email based on the posted params.
	 *
	 * @throws Exception
	 */
	public function actionSendMessage()
	{
		$this->requirePostRequest();

		$plugin = craft()->plugins->getPlugin('contactform');

		if (!$plugin)
		{
			// This shouldn't be possible considering the request got this far, but whatever
			throw new Exception('Couldn’t find the Contact Form plugin!');
		}

		$settings = $plugin->getSettings();

		if (!$settings->toEmail)
		{
			$error = 'The "To Email" address is not set on the plugin’s settings page.';
			if (craft()->request->isAjaxRequest())
			{
				$this->returnErrorJson($error);
			}
			else
			{
				craft()->userSession->setError($error);
				Craft::log('Tried to send a contact form request, but missing the "To Email" address on the plugin’s settings page.', LogLevel::Error);
				$this->redirectToPostedUrl();
			}
		}

		// Check whether the form passes the 'honeypot' test. If not, we pretend everything worked just fine,
		// so the evil spammer is none the wiser.
		if (!$this->validateHoneypot($settings->honeypotField))
		{
			$this->returnSuccess();
			return;
		}

		$message = new ContactFormModel();
		$savedBody = false;

		$message->fromEmail = craft()->request->getPost('fromEmail');
		$message->fromName	= craft()->request->getPost('fromName', '');

		$fromName = $message->fromName;

		if ($fromName)
		{
			if (!empty($settings->prependSender))
			{
				$fromName = $settings->prependSender.' '.$message->fromName;
			}
			else
			{
				$fromName = $message->fromName;
			}
		}

		// Set the message body
		$postedMessage = craft()->request->getPost('message');

		if ($postedMessage)
		{
			if (is_array($postedMessage))
			{
				if (isset($postedMessage['body']))
				{
					// Save the message body in case we need to reassign it in the event there's a validation error
					$savedBody = $postedMessage['body'];
				}

				// Compile the message from each of the individual values
				$compiledMessage = '';

				foreach ($postedMessage as $key => $value)
				{
					if ($key != 'body')
					{
						if ($compiledMessage)
						{
							$compiledMessage .= "\n\n";
						}

						$compiledMessage .= $key.': ';

						if (is_array($value))
						{
							$compiledMessage .= implode(', ', $value);
						}
						else
						{
							$compiledMessage .= $value;
						}
					}
				}

				if (!empty($postedMessage['body']))
				{
					if ($compiledMessage)
					{
						$compiledMessage .= "\n\n";
					}

					$compiledMessage .= $postedMessage['body'];
				}

				$message->message = $compiledMessage;
			}
			else
			{
				$message->message = $postedMessage;
			}
		}

		// Set the subject
		$subject = $settings->prependSubject;
		$postedSubject = craft()->request->getPost('subject');

		if ($postedSubject)
		{
			if ($subject)
			{
				$subject .= ' - ';
			}

			$subject .= $postedSubject;
		}

		$message->subject = $subject;

		// Validate and send
		if ($message->validate())
		{
			$email = new EmailModel();
			$emailSettings = craft()->email->getSettings();

			$email->fromEmail = $emailSettings['emailAddress'];
			$email->replyTo   = $message->fromEmail;
			$email->sender	  = $emailSettings['emailAddress'];
			$email->fromName  = $fromName;
			$email->toEmail   = $settings->toEmail;
			$email->subject   = $subject;
			$email->body	  = $message->message;

			$attachment = \CUploadedFile::getInstanceByName('attachment');

			if ($attachment)
			{
				$email->addAttachment($attachment->getTempName(), $attachment->getName(), 'base64', $attachment->getType());
			}

			// onBeforeSend()
			// Gives plugins a chance to intervene before the email is sent
			$hookParams		= array('emailModel' => $email);
			$hookResponses	= craft()->plugins->call('contactFormOnBeforeSend', $hookParams);

			foreach ($hookResponses as $plugin => $response)
			{
				// @see Honeypot behavior above
				if (false === $response)
				{
					$this->returnSuccess();
					return;
				}
			}

			craft()->email->sendEmail($email);
			craft()->userSession->setNotice('Your message has been sent, someone will be in touch shortly!');

			$this->returnSuccess();
		}
		else
		{
			if (craft()->request->isAjaxRequest())
			{
				return $this->returnErrorJson($message->getErrors());
			}
			else
			{
				craft()->userSession->setError('There was a problem with your submission, please check the form and try again!');

				if ($settings->prependSubject)
				{
					$message->subject = $postedSubject;
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
	}


	/**
	 * Returns a 'success' response.
	 *
	 * @return void
	 */
	protected function returnSuccess()
	{
		if (craft()->request->isAjaxRequest())
		{
			$this->returnJson(array('success' => true));
		}
		else
		{
			// Deprecated. Use 'redirect' instead.
			$successRedirectUrl = craft()->request->getPost('successRedirectUrl');

			if ($successRedirectUrl)
			{
				$_POST['redirect'] = $successRedirectUrl;
			}

			$this->redirectToPostedUrl();
		}
	}


	/**
	 * Checks that the 'honeypot' field has not been filled out (assuming one has been set).
	 *
	 * @param string $fieldName The honeypot field name.
	 * @return bool
	 */
	protected function validateHoneypot($fieldName)
	{
		if (!$fieldName)
		{
			return true;
		}

		$honey = craft()->request->getPost($fieldName);
		return $honey == '';
	}
}
