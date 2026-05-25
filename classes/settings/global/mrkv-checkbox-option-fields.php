<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

# Check if class exist
if (!class_exists('MRKV_CHECKBOX_OPTION_FILEDS'))
{
	/**
	 * Class for setup plugin global options fields
	 */
	class MRKV_CHECKBOX_OPTION_FILEDS
	{
		/**
		 * Constructor for plugin global options fields
		 * */
		public function __construct()
		{
			
		}

		/**
		 * Get text input data
		 * @var string Label
		 * @var string Name
		 * @var string Value
		 * @var string ID
		 * @var string Default value
		 * @var string Placeholder
		 * @var string Description
		 * 
		 * @return string HTML
		 * */
	    public function get_input_text($label, $name, $value = '', $id = '', $default_value = '', $placeholder = '', $description = '', $disabled = '')
	    {
	    	# Get all fields
	    	$label_content = $label ? '<label for="' . $id . '">' . $label . '</label>' : '';
	    	$value_content = $value ? $value : $default_value;
	    	$name_content = $name ? '<input ' . $disabled . ' id="' . $id . '" type="text" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value_content . '">' : '';
	    	$description_content = $description ? '<p class="mrkv-ua-ship-description">' . $description . '</p>' : '';

	    	# Create HTML
	    	$html = '' . $label_content . $name_content . $description_content;

	    	# Return data
	    	return $html;
	    }

		/**
		 * Get text input data
		 * @var string Label
		 * @var string Name
		 * @var string Value
		 * @var string ID
		 * @var string Default value
		 * @var string Placeholder
		 * @var string Description
		 * 
		 * @return string HTML
		 * */
	    public function get_input_password($label, $name, $value = '', $id = '', $default_value = '', $placeholder = '', $description = '', $disabled = '')
		{
			$label_content = $label ? '<label for="' . $id . '">' . $label . '</label>' : '';
			$value_content = $value ? $value : $default_value;
			$masked_value = '';
			if (!empty($value_content)) {
				$len = mb_strlen($value_content);
				if ($len > 4) {
					$masked_value = str_repeat('•', $len - 4) . mb_substr($value_content, -4);
				} else {
					$masked_value = $value_content;
				}
			}

			$html = '<div class="mrkv-password-mask-wrapper">';
			$html .= '<input type="text" ' . $disabled . ' id="' . $id . '" class="mrkv-fake-password" placeholder="' . $placeholder . '" value="' . $masked_value . '" autocomplete="off">';
			$html .= '<input type="hidden" name="' . $name . '" class="mrkv-real-password" value="' . $value_content . '">';
			$html .= '</div>';
			$description_content = $description ? '<p class="mrkv-ua-ship-description">' . $description . '</p>' : '';
			return $label_content . $html . $description_content;
		}

	    /**
		 * Get text input data
		 * @var string Label
		 * @var string Name
		 * @var string Value
		 * @var string ID
		 * @var string Default value
		 * @var string Placeholder
		 * @var string Description
		 * 
		 * @return string HTML
		 * */
	    public function get_input_number($label, $name, $value = '', $id = '', $default_value = '', $placeholder = '', $description = '', $readonly = '')
	    {
	    	# Get all fields
	    	$label_content = $label ? '<label for="' . $id . '">' . $label . '</label>' : '';
	    	$value_content = $value ? $value : $default_value;
	    	$name_content = $name ? '<input step="1" min="0" id="' . $id . '" type="number" name="' . $name . '" placeholder="' . $placeholder . '" value="' . $value_content . '" ' . $readonly . '>' : '';
	    	$description_content = $description ? '<p class="mrkv-ua-ship-description">' . $description . '</p>' : '';

	    	# Create HTML
	    	$html = '<div>' . $label_content . $description_content . '</div>' . $name_content;

	    	# Return data
	    	return $html;
	    }

	    /**
		 * Get select data
		 * @var string Label
		 * @var string Name
		 * @param array Options
		 * @var string Value
		 * @var string ID
		 * @var string Placeholder
		 * @var string Description
		 * 
		 * @return string HTML
		 * */
	    public function get_select_simple($label, $name, $options, $value = '', $id = '', $placeholder = '', $description = '', $multiple = '', $readonly = '')
	    {
	    	# Get all fields
	    	$label_content = $label ? '<label for="' . $id . '">' . $label . '</label>' : '';
	    	$description_content = $description ? '<p class="mrkv-ua-ship-description">' . $description . '</p>' : '';
	    	$value_content = $value ? $value : '';
	    	$options_content = $placeholder ? '<option value="">' . $placeholder . '</option>' : '';

	    	if(is_array($options))
	    	{
	    		foreach($options as $key => $value)
		    	{
		    		$checked = ($value_content == $key) ? 'selected' : '';
		    		$options_content .= '<option value="' . $key . '" ' . $checked . '>' . $value . '</option>';
		    	}
	    	}

	    	$name_content = $name ? '<select ' . $readonly . ' ' . $multiple . ' id="' . $id . '" type="text" name="' . $name . '" >' . $options_content . '</select>' : '';

	    	# Create HTML
	    	$html = '' . $label_content . $name_content . $description_content;

	    	# Return data
	    	return $html;
	    }

	    /**
		 * Get select data
		 * @var string Label
		 * @var string Name
		 * @param array Options
		 * @var string Value
		 * @var string ID
		 * @var string Placeholder
		 * @var string Description
		 * 
		 * @return string HTML
		 * */
	    public function get_select_multiple($label, $name, $options, $value = array(), $id = '', $placeholder = '', $description = '', $multiple = '')
	    {
	    	# Get all fields
	    	$label_content = $label ? '<label for="' . $id . '">' . $label . '</label>' : '';
	    	$description_content = $description ? '<p class="mrkv-ua-ship-description">' . $description . '</p>' : '';
	    	$options_content = $placeholder ? '<option value="">' . $placeholder . '</option>' : '';
	    	if(!is_array($value) && !$value)
	    	{
	    		$value = array();
	    	}
	    	elseif(!is_array($value))
	    	{
	    		$value = array($value);
	    	}

	    	if(is_array($options))
	    	{
	    		foreach($options as $key => $value_option)
		    	{
		    		$checked = (in_array($key, $value)) ? 'selected' : '';
		    		$options_content .= '<option value="' . $key . '" ' . $checked . '>' . $value_option . '</option>';
		    	}
	    	}

	    	$name_content = $name ? '<select ' . $multiple . ' id="' . $id . '" type="text" name="' . $name . '" >' . $options_content . '</select>' : '';

	    	# Create HTML
	    	$html = '' . $label_content . $name_content . $description_content;

	    	# Return data
	    	return $html;
	    }

	    /**
		 * Get select data
		 * @var string Label
		 * @var string Name
		 * @param array Options
		 * @var string Value
		 * @var string ID
		 * @var string Placeholder
		 * @var string Description
		 * 
		 * @return string HTML
		 * */
	    public function get_select_tag($label, $name, $options, $value = '', $id = '', $placeholder = '', $description = '')
	    {
	    	# Get all fields
	    	$label_content = $label ? '<label for="' . $id . '">' . $label . '</label>' : '';
	    	$description_content = $description ? '<p class="mrkv-ua-ship-description">' . $description . '</p>' : '';
	    	$value_content = $value ? $value : '';
	    	$options_content = '<option value="">' . $placeholder . '</option>';

	    	if(is_array($options))
	    	{
	    		foreach($options as $key => $value)
		    	{
		    		$attributes = '';

		    		if(!empty($value['attr']))
		    		{
		    			foreach($value['attr'] as $attr_key => $attr_value)
		    			{
		    				$attributes .= ' data-' . strtolower($attr_key) . '="' . $attr_value . '"';
		    			}
		    		}

		    		$checked = ($value_content == $value['data']) ? 'selected' : '';
		    		$options_content .= '<option ' . $attributes . ' value="' . $value['data'] . '" ' . $checked . '>' . $value['description'] . '</option>';
		    	}
	    	}

	    	$name_content = $name ? '<select id="' . $id . '" type="text" name="' . $name . '" >' . $options_content . '</select>' : '';

	    	# Create HTML
	    	$html = '' . $label_content . $name_content . $description_content;

	    	# Return data
	    	return $html;
	    }

	    /**
		 * Get select data
		 * @var string Name
		 * @var string Value
		 * @var string ID
		 * 
		 * @return string HTML
		 * */
	    public function get_input_hidden($name, $value = '', $id = '')
	    {
	    	# Create HTML
	    	$html = $name ? '<input id="' . $id . '" type="hidden" name="' . $name . '" value="' . $value . '">' : '';

	    	# Return data
	    	return $html;
	    }

	    public function get_input_radio($label, $name, $data, $value = '', $id = '', $default_value = '')
	    {
	    	# Get all fields
	    	$label_content = $label ? '<label for="' . $id . '">' . $label . '</label>' : '';
	    	$checked = ($value == $data) ? 'checked' : '';
	    	$checked = (!$value && ($default_value == $data)) ? 'checked' : $checked;
	    	$name_content = $name ? '<input id="' . $id . '" type="radio" name="' . $name . '" value="' . $data . '" ' . $checked . '>' : '';

	    	# Create HTML
	    	$html = $name_content . $label_content;

	    	# Return data
	    	return $html;
	    }

	    public function get_input_checkbox($label, $name, $value = '', $id = '', $default_value = '', $disabled = '')
	    {
	    	# Get all fields
	    	$label_content = $label ? '<label class="mrkv-checkbox-line" for="' . $id . '"><div class="admin_mrkv_ua_shipping__checkbox__input">
                    <span class="admin_mrkv_ua_shipping_slider"></span>
                </div>' . $label . '</label>' : '';
	    	$checked = ($value && ($value == 'on')) ? 'checked' : '';
	    	$name_content = $name ? '<input id="' . $id . '" type="checkbox" name="' . $name . '" ' . $checked . ' ' . $disabled . '>' : '';

	    	# Create HTML
	    	$html = $name_content . $label_content;

	    	# Return data
	    	return $html;
	    }

	    /**
		 * Get text input data
		 * @var string Label
		 * @var string Name
		 * @var string Value
		 * @var string ID
		 * @var string Default value
		 * @var string Placeholder
		 * @var string Description
		 * 
		 * @return string HTML
		 * */
	    public function get_textarea($label, $name, $value = '', $id = '', $default_value = '', $placeholder = '', $description = '')
	    {
	    	# Get all fields
	    	$label_content = $label ? '<label for="' . $id . '">' . $label . '</label>' : '';
	    	$value_content = $value ? $value : $default_value;
	    	$name_content = $name ? '<textarea id="' . $id . '" name="' . $name . '" placeholder="' . $placeholder . '">' . $value_content : '';
	    	$description_content = $description ? '<p class="mrkv-ua-ship-description">' . $description . '</p>' : '';

	    	$name_content_end = $name_content ? '</textarea>' : '';

	    	# Create HTML
	    	$html = '' . $label_content . $name_content . $name_content_end . $description_content;

	    	# Return data
	    	return $html;
	    }
	}
}