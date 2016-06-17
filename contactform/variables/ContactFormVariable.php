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
   * Get mailing lists as array.
   *
   * @param array  $params
   * @return string
   */
  public function mailingLists($lists = null)
  {
    return craft()->contactForm->mailingLists($lists);
  }

  /**
   * Get mailing lists field as HTML.
   *
   * @param array  $params
   * @return string
   */
  public function mailingListsSelect($attributes = null)
  {
    return craft()->contactForm_field->mailingListsSelect($attributes);
  }
}
