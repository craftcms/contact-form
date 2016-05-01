<?php
namespace Craft;

/**
 * Contact Form Message event
 */
class ContactFormMessageEvent extends Event
{
  public $message = null;
  public $htmlMessage = null;
  public $messageFields = null;
}
