<?php
namespace Craft;

/**
 * Contact Form Message event
 */
class ContactFormMessageEvent extends Event
{
  public $message = true;
  public $messageFields = false;
}
