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
		$event = new ContactFormEvent($this, array('message' => $message, 'settings' => $settings));
		$this->onBeforeSend($event);

		if ($event->isValid)
		{
			if (!$event->fakeIt)
			{
				// Grab any "to" emails set in the plugin settings.
				$toEmails = $this->getEmails($settings);

				foreach ($toEmails as $toEmail)
				{
					$variables = array();
					$email = new EmailModel();
					$emailSettings = craft()->email->getSettings();

					$email->fromEmail = $emailSettings['emailAddress'];
					$email->replyTo   = $message->fromEmail;
					$email->sender    = $emailSettings['emailAddress'];
					$email->fromName  = $settings->prependSender . ($settings->prependSender && $message->fromName ? ' ' : '') . $message->fromName;
					$email->toEmail   = $toEmail;
					$email->subject   = '{{ emailSubject }}';
					$email->body      = '{{ emailBody }}';

					$variables['emailSubject'] = $settings->prependSubject . ($settings->prependSubject && $message->subject ? ' - ' : '') . $message->subject;
					$variables['emailBody'] = $message->message;

					if (!empty($message->htmlMessage))
					{
						// Prevent Twig tags from getting parsed
						$email->htmlBody = str_replace(array('{', '}'), array('&lbrace;', '&rbrace;'), $message->htmlMessage);
					}

					if (!empty($message->attachment))
					{
						foreach ($message->attachment as $attachment)
						{
							if ($attachment)
							{
								$email->addAttachment($attachment->getTempName(), $attachment->getName(), 'base64', $attachment->getType());
							}
						}
					}

					craft()->email->sendEmail($email, $variables);
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

  /**
	 * Fires an 'onBeforeMessageCompile' event.
	 *
	 * @param ContactFormMessageEvent $event
	 */
	public function onBeforeMessageCompile(ContactFormMessageEvent $event)
	{
		$this->raiseEvent('onBeforeMessageCompile', $event);
	}


  /**
   * Get mailing lists as array for use in contact form variables
   *
   * @param array  $params
   * @return string
   */
  public function mailingLists($lists = null)
  {
    $settings = craft()->plugins->getPlugin('contactform')->getSettings();

    if (isset($lists)) {
      $mailingLists = array();
      foreach ($settings->mailingLists as $key => $value)
      {
        if (in_array($value['listName'], $lists))
        {
          $mailingLists[$key] = $list;
        }
      }
      return $mailingLists;
    }

    return $settings->mailingLists;
  }

  /**
	 * Get emails as array
	 *
   * @param array|settings
	 * @return array
	 */
	protected function getEmails($settings)
	{
		$toEmails = ArrayHelper::stringToArray($settings->toEmail);
    $postedMailingLists = craft()->request->getPost('mailingLists');

    if (isset($postedMailingLists))
    {
      if (!is_array($postedMailingLists))
      {
        $postedMailingLists = ArrayHelper::stringToArray($postedMailingLists);
      }

      foreach ($postedMailingLists as $listName)
      {
        $listEmails = $this->getMailingListEmails($listName, $settings->mailingLists);
        $toEmails = array_merge($toEmails, $listEmails);
      }
    }

    return array_unique($toEmails);
	}

  /**
	 * Get emails as array
	 *
   * @param mixed $listName
   * @param array $mailingLists
	 * @return array
	 */
  protected function getMailingListEmails($listName, $mailingLists)
  {
    $toEmails = array();

    foreach ($mailingLists as $key => $list)
    {
      if ($list['listName'] == $listName)
      {
        $toEmails = ArrayHelper::stringToArray($list['toEmails']);
      }
    }

    return $toEmails;
  }

}
