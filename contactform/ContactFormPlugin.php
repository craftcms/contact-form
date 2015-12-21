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
		return '1.5';
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
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'toEmail'          => array(AttributeType::String, 'required' => true),
			'prependSender'    => AttributeType::String,
			'prependSubject'   => AttributeType::String,
			'allowAttachments' => AttributeType::Bool,
			'honeypotField'    => AttributeType::String,
			'successMessage'   => array(AttributeType::String,
			                            'default' => 'Your message has been sent.',
			                            'required' => true),
		);
	}
}
