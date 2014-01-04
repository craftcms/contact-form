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

		$settings = craft()->plugins->getPlugin('contactform')->getSettings();

		// Check whether the form passes the 'honeypot' test. If not, we pretend everything worked just fine,
		// so the evil spammer is none the wiser.
		if (!$this->validateHoneypot($settings->honeypotField))
		{
			$this->returnSuccess();
		}

		$message = new ContactFormModel();
		$savedBody = false;

		$message->fromEmail  = craft()->request->getPost('fromEmail');
		$message->fromName	 = craft()->request->getPost('fromName');
		$message->subject    = craft()->request->getPost('subject');
		$message->attachment = \CUploadedFile::getInstanceByName('attachment');

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

		// Send
		if (craft()->contactForm->sendMessage($message))
		{
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

			craft()->userSession->setNotice('Your message has been sent.');
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
