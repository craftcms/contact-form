<?php
namespace Craft;

/**
 * Contact Form event
 */
class ContactFormEvent extends Event
{
	/**
	 * @var bool Whether we should pretend the submission went through, but it really didn't.
	 */
	public $fakeIt = false;
}
