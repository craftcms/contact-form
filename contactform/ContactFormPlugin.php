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
		return '1.4';
	}

	function getDeveloper()
	{
		return 'Pixel & Tonic';
	}

	function getDeveloperUrl()
	{
		return 'http://pixelandtonic.com';
	}

	protected function defineSettings()
	{
		return array(
			'toEmail'          => array(AttributeType::String, 'required' => true),
			'prependSender'    => AttributeType::String,
			'prependSubject'   => AttributeType::String,
			'allowAttachments' => AttributeType::Bool,
			'honeypotField'    => AttributeType::String,
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('contactform/_settings', array(
			'settings' => $this->getSettings()
		));
	}
}
