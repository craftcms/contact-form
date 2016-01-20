<?php
namespace Craft;

class ContactFormPlugin extends BasePlugin
{
	/**
	 * @return mixed
	 */
	public function getName()
	{
		return Craft::t('Contact Form');
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return '1.7.0';
	}

	public function getSchemaVersion()
	{
		return '1.0.0';
	}

	/**
	 * @return string
	 */
	public function getDeveloper()
	{
		return 'Pixel & Tonic';
	}

	/**
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'http://pixelandtonic.com';
	}

	/**
	 * @return string
	 */
	public function getPluginUrl()
	{
		return 'https://github.com/pixelandtonic/ContactForm';
	}

	/**
	 * @return string
	 */
	public function getDocumentationUrl()
	{
		return $this->getPluginUrl().'/blob/master/README.md';
	}

	/**
	 * @return string
	 */
	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/pixelandtonic/ContactForm/master/releases.json';
	}

	/**
	 * @return mixed
	 */
	public function getSettingsHtml()
	{
		return craft()->templates->render('contactform/_settings', array(
			'settings' => $this->getSettings()
		));
	}

	/**
	 * @param array|BaseModel $values
	 */
	public function setSettings($values)
	{
		if (!$values)
		{
			$values = array();
		}

		if (is_array($values))
		{
			// Merge in any values that are stored in craft/config/contactform.php
			foreach ($this->getSettings() as $key => $value)
			{
				$configValue = craft()->config->get($key, 'contactform');

				if ($configValue !== null)
				{
					$values[$key] = $configValue;
				}
			}
		}

		parent::setSettings($values);
	}

	/**
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'toEmail'               => array(AttributeType::String, 'required' => true),
			'prependSender'         => array(AttributeType::String, 'default' => Craft::t('On behalf of')),
			'prependSubject'        => array(AttributeType::String, 'default' => Craft::t('New message from {siteName}', array('siteName' => craft()->getSiteName()))),
			'allowAttachments'      => AttributeType::Bool,
			'honeypotField'         => AttributeType::String,
			'successFlashMessage'   => array(AttributeType::String, 'default' => Craft::t('Your message has been sent.'), 'required' => true),
		);
	}
}
