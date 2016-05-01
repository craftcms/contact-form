<?php
namespace Craft;

class ContactFormModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'fromName'          => array(AttributeType::String, 'label' => 'Your Name'),
			'fromEmail'         => array(AttributeType::Email,  'required' => true, 'label' => 'Your Email'),
			'message'           => array(AttributeType::String, 'required' => true, 'label' => 'Message'),
			'htmlMessage'       => array(AttributeType::String),
			'messageFields'     => array(AttributeType::Mixed),
			'subject'           => array(AttributeType::String, 'label' => 'Subject'),
			'attachment'        => AttributeType::Mixed,
		);
	}
}
