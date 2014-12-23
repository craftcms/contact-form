<?php
namespace Craft;

use Twig_Extension;
use Twig_Filter_Method;

class ContactFormTwigExtension extends \Twig_Extension
{

	public function getGlobals()
	{
		$globals['contactEmailAddress'] = craft()->plugins->getPlugin('contactform')->getSettings()->toEmail;
		return $globals;
	}

	public function getName()
	{
		return Craft::t('Contact Form');
	}

}
