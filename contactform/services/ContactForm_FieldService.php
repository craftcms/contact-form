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
	 * @param array $attributes
	 * @return string
	 */
	public function mailingListsSelect($attributes = null)
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
	 * @param array $attributes
	 * @return string
	 */
	protected function _getAttributesString($attributes)
	{
		$attrs_str = '';
		$attributes = isset($attributes) ? (array) $attributes : array();
		$default_attributes = array(
			'id'    => 'mailingLists',
			'name'  => 'mailingLists[]'
		);

		$attrs = array_merge($default_attributes, $attributes);

		$attrs_str = implode(' ', array_map(function($v, $k)
		{
			$value = is_string($v) ? strtolower($v) : $v;
			$key = is_string($k) ? strtolower($k) : $k;
			$attr = '';

			if (is_bool($value) && $value === true)
			{
				$attr = $key;
			}
			elseif (is_int($key))
			{
				$attr = $value;
			}
			else
			{
				$attr = "{$key}=\"$value\"";
			}

			return $attr;
		}, $attrs, array_keys($attrs)));

		return $attrs_str;
	}
}
