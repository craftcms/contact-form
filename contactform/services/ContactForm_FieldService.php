<?php
namespace Craft;

/**
 * Contact Form service
 */
class ContactForm_FieldService extends BaseApplicationComponent
{
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
    $settings = craft()->plugins->getPlugin('contactform')->getSettings();
    $tag = 'select';
    $html = '';

    foreach ($settings->mailingLists as $key => $list)
    {
      $value = $list['listName'];
      $text = StringHelper::uppercaseFirst($value);
      $html = "{$html}<option value=\"{$value}\">{$text}</option>";
    }

    $attrs = $this->_getAttributesString($attributes);
    $html = "<{$tag} {$attrs}>{$html}</{$tag}>";

    return TemplateHelper::getRaw($html);
  }

  protected function _getAttributesString($attributes)
  {
    $default_attributes = array(
      'id'    => 'mailingLists',
      'name'  => 'mailingLists'
    );

    $attrs = array_merge($default_attributes, $attributes);
    $attrs_str = '';

    foreach ($attrs as $key => $value) {
      $attrs_str = "{$attrs_str} {$key}=\"$value\"";
    }
    return trim($attrs_str);
  }
}
