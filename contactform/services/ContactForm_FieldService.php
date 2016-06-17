<?php
namespace Craft;

/**
 * Contact Form service
 */
class ContactForm_FieldService extends BaseApplicationComponent
{
  /**
   * Get mailing lists field as HTML select.
   *
   * @param array  $params
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

  /**
   * Get attribute array as string.
   *
   * @param array  $params
   * @return string
   */
  protected function _getAttributesString($attributes)
  {
    $attrs_str = '';

    if (isset($attributes) && is_array($attributes))
    {
      $default_attributes = array(
        'id'    => 'mailingLists',
        'name'  => 'mailingLists'
      );

      $attrs = array_merge($default_attributes, $attributes);

      foreach ($attrs as $key => $value) {
        $attrs_str = "{$attrs_str} {$key}=\"$value\"";
      }
    }

    return trim($attrs_str);
  }
}
