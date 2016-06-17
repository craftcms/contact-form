<?php
namespace Craft;

class ContactFormVariable
{

  /**
   * Get the Plugin's name.
   *
   * @example {{ craft.amNav.name }}
   * @return string
   */
  public function getName()
  {
    $plugin = craft()->plugins->getPlugin('contactform');
    return $plugin->getName();
  }

  /**
   * Get mailing lists field as HTML.
   *
   * @param array  $params
   *
   * Params possibilities:
   * - class
   *
   * @return string
   */
  public function mailingListsSelect($attributes = array())
  {
    return craft()->contactForm_field->mailingListsSelect($attributes);
  }
}
