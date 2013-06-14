<?php
namespace Craft;

class ContactFormPlugin extends BasePlugin
{
	function getName()
	{
		return Craft::t('Contact Form');
	}

	function getVersion()
	{
		return '1.0';
	}

	function getDeveloper()
	{
		return 'Pixel & Tonic, Inc.';
	}

	function getDeveloperUrl()
	{
		return 'http://pixelandtonic.com';
	}

	protected function defineSettings()
	{
		return array(
			'toEmail' => array(AttributeType::Email, 'required' => true),
			'prependSubject' => array(AttributeType::String),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('contactform/_settings', array(
			'settings' => $this->getSettings()
		));
	}
}
