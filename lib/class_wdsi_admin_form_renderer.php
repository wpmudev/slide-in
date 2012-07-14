<?php
class Wdsi_AdminFormRenderer {

	function _get_option ($key=false, $pfx='wdsi') {
		$opts = get_option($pfx);
		if (!$key) return $opts;
		return @$opts[$key];
	}

	function _create_checkbox ($name, $pfx='wdsi') {
		$opt = $this->_get_option($name, $pfx);
		$value = @$opt[$name];
		return
			"<input type='radio' name='{$pfx}[{$name}]' id='{$name}-yes' value='1' " . ((int)$value ? 'checked="checked" ' : '') . " /> " .
				"<label for='{$name}-yes'>" . __('Yes', 'wdsi') . "</label>" .
			'&nbsp;' .
			"<input type='radio' name='{$pfx}[{$name}]' id='{$name}-no' value='0' " . (!(int)$value ? 'checked="checked" ' : '') . " /> " .
				"<label for='{$name}-no'>" . __('No', 'wdsi') . "</label>" .
		"";
	}

	function _create_radiobox ($name, $value) {
		$opt = $this->_get_option($name);
		$checked = (@$opt == $value) ? true : false;
		return "<input type='radio' name='wdsi[{$name}]' id='{$name}-{$value}' value='{$value}' " . ($checked ? 'checked="checked" ' : '') . " /> ";
	}
	
	function create_show_after_box () {
		$percentage = $selector = $timeout = false;
		$condition = $this->_get_option('show_after-condition');
		$value = $this->_get_option('show_after-rule');
		
		switch ($condition) {
			case "selector":
				$selector = 'checked="checked"';
				break;
			case "timeout":
				$timeout = 'checked="checked"';
				$value = (int)$value;
				break;
			case "percentage":
			default:
				$percentage = 'checked="checked"';
				$value = (int)$value;
				break;
		}
		
		$percentage_select = '<select name="wdsi[show_after-rule]" ' . ($percentage ? '' : 'disabled="disabled"') . '>';
		for ($i=1; $i<100; $i++) {
			$selected = ($i == $value) ? 'selected="selected"' : '';
			$percentage_select .= "<option value='{$i}' {$selected}>{$i}&nbsp;</option>";
		}
		$percentage_select .= '</select>%';
		echo '<div>' .
			'<input type="radio" name="wdsi[show_after-condition]" value="percentage" id="wdsi-show_after-percentage" ' . $percentage . ' /> ' .
			'<label for="wdsi-show_after-percentage">' . 
				__('Show message after this much of my page has been viewed', 'wdsi') .
				': ' .
			'</label>' .
			$percentage_select .
		'</div>';

		echo '<div>' .
			'<input type="radio" name="wdsi[show_after-condition]" value="selector" id="wdsi-show_after-selector" ' . $selector . ' /> ' .
			'<label for="wdsi-show_after-selector">' .
				__('Show message after scrolling past element with this ID', 'wdsi') .
				': #' .
			'</label>' .
			'<input type="text" size="8" name="wdsi[show_after-rule]" id="" value="' . ($selector ? esc_attr($value) : '') . '" ' . ($selector ? '' : 'disabled="disabled"') . ' />' .
		'</div>';

		echo '<div>' .
			'<input type="radio" name="wdsi[show_after-condition]" value="timeout" id="wdsi-show_after-timeout" ' . $timeout . ' /> ' .
			'<label for="wdsi-show_after-timeout">' .
				__('Show message after this many seconds', 'wdsi') .
				': ' .
			'</label>' .
			'<input type="text" size="2" name="wdsi[show_after-rule]" id="" value="' . ($timeout ? esc_attr($value) : '') . '" ' . ($timeout ? '' : 'disabled="disabled"') . ' />' .
		'</div>';
	}

	function create_show_for_box () {
		$time = $this->_get_option('show_for-time');
		$unit = $this->_get_option('show_for-unit');

		$_times = array_combine(range(1,59), range(1,59));
		$_units = array(
			's' => __('Seconds', 'wdsi'),
			'm' => __('Minutes', 'wdsi'),
			'h' => __('Hours', 'wdsi'),
		);

		// Time
		echo "<select name='wdsi[show_for-time]'>";
		foreach ($_times as $_time) {
			$selected = $_time == $time ? 'selected="selected"' : '';
			echo "<option value='{$_time}' {$selected}>{$_time}</option>";
		}
		echo "</select>";

		// Unit
		echo "<select name='wdsi[show_for-unit]'>";
		foreach ($_units as $key => $_unit) {
			$selected = $key == $unit ? 'selected="selected"' : '';
			echo "<option value='{$key}' {$selected}>{$_unit}</option>";
		}
		echo "</select>";
	}
	
	function create_position_box () {
		echo '' . 
			$this->_create_radiobox('position', 'left') . 
			'<label for="position-left">' . __('Left', 'wdsi') . '</label>' .
			'<br />' .
			$this->_create_radiobox('position', 'right') . 
			'<label for="position-right">' . __('Right', 'wdsi') . '</label>' .
			'<br />' .
			$this->_create_radiobox('position', 'top') . 
			'<label for="position-top">' . __('Top', 'wdsi') . '</label>' .
			'<br />' .
			$this->_create_radiobox('position', 'bottom') . 
			'<label for="position-bottom">' . __('Bottom', 'wdsi') . '</label>' .
		
		'';
		
		echo '<h4>' . __('Width', 'wdsi') . '</h4>';
		$width = $this->_get_option('width');
		$checked = (!(int)$width || 'full' == 'width') ? 'checked="checked"' : '';
		echo '' .
			'<input type="checkbox" name="wdsi[width]" value="full" id="wdsi-full_width" ' . $checked . ' autocomplete="off" />' .
			'&nbsp;' .
			'<label for="wdsi-full_width">' . __('Full width', 'wdsi') . '</label>' .
		'';
		$display = $checked ? 'style="display:none"' : '';
		echo '<div id="wdsi-custom_width" ' . $display . '>';
		$disabled = $checked ? 'disabled="disabled"' : '';
		echo '' .
			'<label for="wdsi-width">' . __('Message width', 'wdsi') . '</label>' .
			'&nbsp;' .
			'<input type="text" size="8" name="wdsi[width]" id="wdsi-width" value="' . (int)$width . '" ' . $disabled . ' />px' .
		'';
		echo '</div>';
	}

	function create_appearance_box () {
		echo '<h4>' . __('Theme', 'wdsi') . '</h4>';
		$_themes = Wdsi_SlideIn::get_appearance_themes();
		foreach ($_themes as $theme => $label) {
			echo $this->_create_radiobox('theme', $theme) .
				'<label for="theme-' . esc_attr($theme) . '">' . esc_html($label) . '</label><br />';
		}
		echo '<h4>' . __('Variation', 'wdsi') . '</h4>';
		$_themes = Wdsi_SlideIn::get_theme_variations();
		foreach ($_themes as $theme => $label) {
			echo $this->_create_radiobox('variation', $theme) .
				'<label for="variation-' . esc_attr($theme) . '">' . esc_html($label) . '</label><br />';
		}
		echo '<h4>' . __('Color Scheme', 'wdsi') . '</h4>';
		$_themes = Wdsi_SlideIn::get_variation_schemes();
		foreach ($_themes as $theme => $label) {
			echo $this->_create_radiobox('scheme', $theme) .
				'<label for="scheme-' . esc_attr($theme) . '">' . esc_html($label) . '</label><br />';
		}
	}
	
	function create_services_box () {
		$services = array (
			'google' => 'Google +1',
			'facebook' => 'Facebook Like',
			'twitter' => 'Tweet this',
			'stumble_upon' => 'Stumble upon',
			'delicious' => 'Del.icio.us',
			'reddit' => 'Reddit',
			'linkedin' => 'LinkedIn',
			'pinterest' => 'Pinterest',
			'related_posts' => __('Related posts', 'wdsi')
		);
		if (function_exists('wdpv_get_vote_up_ms')) $services['post_voting'] = 'Post Voting'; 
		$externals = array (
			'google',
			'twitter',
			'linkedin',
		);

		$load = $this->_get_option('services');
		$load = is_array($load) ? $load : array();

		$services = array_merge($load, $services);

		$skip = $this->_get_option('skip_script');
		$skip = is_array($skip) ? $skip : array();

		echo "<ul id='wdsi-services'>";
		foreach ($services as $key => $name) {
			$disabled = isset($load[$key]) ? '' : 'wdsi-disabled';
			if ('post_voting' === $key && !function_exists('wdpv_get_vote_up_ms')) continue;
			echo "<li class='wdsi-service-item {$disabled}'>";
			if (is_array($name)) {
				echo $name['name'] .
					"<br/><a href='#' class='wdsi_remove_service'>" . __('Remove this service', 'wdsi') . '</a>' .
					'<input type="hidden" name="wdsi[services][' . $key . '][name]" value="' . esc_attr($name['name']) . '" />' .
					'<input type="hidden" name="wdsi[services][' . $key . '][code]" value="' . esc_attr($name['code']) . '" />' .
				'</div>';
			} else {
				echo "<img src='" . wdsi_PLUGIN_URL . "/img/{$key}.png' width='50px' />" .
					"<input type='checkbox' name='wdsi[services][{$key}]' value='{$key}' " .
						"id='wdsi-services-{$key}' " .
						(in_array($key, $load) ? "checked='checked'" : "") .
					"/> " .
						"<label for='wdsi-services-{$key}'>{$name}</label>" .
					'<br />';
				if (in_array($key, $externals)) echo
					"<input type='checkbox' name='wdsi[skip_script][{$key}]' value='{$key}' " .
						"id='wdsi-skip_script-{$key}' " .
						(in_array($key, $skip) ? "checked='checked'" : "") .
					"/> " .
						"<label for='wdsi-skip_script-{$key}'>" .
							'<small>' . __('My page already uses scripts from this service', 'wdsi') . '</small>' .
						"</label>" .
					"";
			}

			echo "<div class='clear'></div></li>";
		}
		echo "</ul>";
	}
	
	function create_custom_service_box () {
		echo '<p>' .
			'<label for="wdsi_new_custom_service-name">' . __('Name', 'wdsi') . '</label>' .
			'<input type="text" name="wdsi[new_service][name]" id="wdsi_new_custom_service-name" class="widefat" />' .
		'</p>';
		echo '<p>' .
			'<label for="wdsi_new_custom_service-code">' . __('Code', 'wdsi') . '</label>' .
			'<textarea rows="1" name="wdsi[new_service][code]" id="wdsi_new_custom_service-code" class="widefat"></textarea>' .
		'</p>';
		echo '<p>' .
			'<input type="submit" class="button" value="' . __('Add', 'wdsi') . '" />' .
		'</p>';
		'';
	}
/*	
	function create_conditions_box () {
		echo '' .
			'<label for="show_if_logged_in-yes">' . __('... the user is logged in:', 'wdsi') . '</label> ' .
			$this->_create_checkbox('show_if_logged_in') .
		'<br />';
		echo '' .
			'<label for="show_if_not_logged_in-yes">' . __('... the user is <b>NOT</b> logged in:', 'wdsi') . '</label> ' .
			$this->_create_checkbox('show_if_not_logged_in') .
		'<br />';
		echo '' .
			'<label for="show_if_never_commented-yes">' . __('... the user never commented on your site before:', 'wdsi') . '</label> ' .
			$this->_create_checkbox('show_if_never_commented') .
		'<br />';
	}
*/
}