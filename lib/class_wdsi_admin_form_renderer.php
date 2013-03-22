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

	function _create_hint ($text) {
		return "<p class='info'><span class='info'></span>{$text}</p>";
	}

	function _create_radiobox ($name, $value, $value_as_class=false) {
		$opt = $this->_get_option($name);
		$checked = (@$opt == $value) ? true : false;
		$class = $value_as_class ? "class='{$value}'" : '';
		return "<input type='radio' name='wdsi[{$name}]' {$class} id='{$name}-{$value}' value='{$value}' " . ($checked ? 'checked="checked" ' : '') . " /> ";
	}

	function _create_color_radiobox ($name, $value, $label) {
		$color = esc_attr($value);
		$label= esc_attr($label);
		return "<label class='wdsi-color-container' for='{$name}-{$value}'>" .
			$this->_create_radiobox($name, $value) .
			"<div class='wdsi-color wdsi-{$color}' title='{$label}'></div>" .
		'</label>';
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
		
		$percentage_select = '<div class="wpmudev-ui-select"><select name="wdsi[show_after-rule]" ' . ($percentage ? '' : 'disabled="disabled"') . '>';
		for ($i=1; $i<100; $i++) {
			$selected = ($i == $value) ? 'selected="selected"' : '';
			$percentage_select .= "<option value='{$i}' {$selected}>{$i}&nbsp;</option>";
		}
		$percentage_select .= '</select></div>%';
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
			'<input type="text" size="8" class="medium" name="wdsi[show_after-rule]" id="" value="' . ($selector ? esc_attr($value) : '') . '" ' . ($selector ? '' : 'disabled="disabled"') . ' />' .
		'</div>';

		echo '<div>' .
			'<input type="radio" name="wdsi[show_after-condition]" value="timeout" id="wdsi-show_after-timeout" ' . $timeout . ' /> ' .
			'<label for="wdsi-show_after-timeout">' .
				__('Show message after this many seconds', 'wdsi') .
				': ' .
			'</label>' .
			'<input type="text" size="2" class="short" name="wdsi[show_after-rule]" id="" value="' . ($timeout ? esc_attr($value) : '') . '" ' . ($timeout ? '' : 'disabled="disabled"') . ' />' .
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
		echo "<div class='wpmudev-ui-select'><select name='wdsi[show_for-time]'>";
		foreach ($_times as $_time) {
			$selected = $_time == $time ? 'selected="selected"' : '';
			echo "<option value='{$_time}' {$selected}>{$_time}</option>";
		}
		echo "</select></div>";

		// Unit
		echo "<div class='wpmudev-ui-select'><select name='wdsi[show_for-unit]'>";
		foreach ($_units as $key => $_unit) {
			$selected = $key == $unit ? 'selected="selected"' : '';
			echo "<option value='{$key}' {$selected}>{$_unit}</option>";
		}
		echo "</select></div>";
	}
	
	function create_position_box () {
		echo '<div class="position-control">' .
			$this->_create_radiobox('position', 'left', true) .
			$this->_create_radiobox('position', 'top', true) .
			$this->_create_radiobox('position', 'right', true) .
			$this->_create_radiobox('position', 'bottom', true) .
		'</div>';
		echo '<br /><br />' .
			$this->_create_hint(__('This is where your message will appear.', 'wdsi'))
		;
	}

	function create_msg_width_box () {
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
			'<input type="text" size="8" class="medium" name="wdsi[width]" id="wdsi-width" value="' . (int)$width . '" ' . $disabled . ' />px' .
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
	}

	function create_color_scheme_box () {
		echo '<div class="wdsi-complex_element-container">';
		$_themes = Wdsi_SlideIn::get_variation_schemes();
		foreach ($_themes as $theme => $label) {
			echo $this->_create_color_radiobox('scheme', $theme, $label) .
				//'<label for="scheme-' . esc_attr($theme) . '">' . esc_html($label) . '</label><br />' .
			'';
		}
		echo '</div>';
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
			//'related_posts' => __('Related posts', 'wdsi'),
			//'mailchimp' => __('MailChimp subscription form', 'wdsi'),
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
				echo "<img src='" . WDSI_PLUGIN_URL . "/img/{$key}.png' width='50px' />" .
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

		echo '<h4>' . __('Add your own:', 'wdsi') . '</h4>';
		echo '' .
			'<input type="text" name="wdsi[new_service][name]" id="wdsi_new_custom_service-name" placeholder="' . esc_attr(__('Name', 'wdsi')) . '" class="medium" />' .
			'&nbsp;' .
			'<input type="text" name="wdsi[new_service][code]" id="wdsi_new_custom_service-code" placeholder="' . esc_attr(__('Code', 'wdsi')) . '" class="long" />' .
			'&nbsp;' .
			'<button type="submit">' . __('Add', 'wdsi') . '</button>' .
		'';
	}

	function create_mailchimp_box () {
		/*
		echo '<label for="mailchimp-enabled-yes">' . __('Enable MailChimp integration:', 'wdsi') . ' </label>' .
			$this->_create_checkbox('mailchimp-enabled') .
		'<br />';
		*/
		$api_key = $this->_get_option('mailchimp-api_key');
		echo '<label for="wdsi-mailchimp-api_key">' . __('MailChimp API key:') . '</label>' .
			'<input type="text" class="long" name="wdsi[mailchimp-api_key]" id="wdsi-mailchimp-api_key" value="' . esc_attr($api_key) . '" />' .
		'<br />';
		if (!$api_key) {
			echo $this->_create_hint(__('Enter your API key here, then save the settings to continue', 'wdsi'));
			return false;
		}

		$mailchimp = new Wdsi_Mailchimp($api_key);

		$lists = $mailchimp->get_lists();
		$current = $this->_get_option('mailchimp-default_list');

		echo '<label>' . __('Default subscription list:', 'wdsi') . ' </label>';
		echo '<div class="wpmudev-ui-select"><select name="wdsi[mailchimp-default_list]">';
		echo '<option></option>';
		foreach ($lists as $list) {
			$selected = $list['id'] == $current ? 'selected="selected"' : '';
			echo '<option value="' . esc_attr($list['id']) . '" ' . $selected . '>' . $list['name'] . '</option>';
		}
		echo '</select></div>';

		// We got this far, we have the API key
		echo '&nbsp;<a href="#mcls-refresh" id="wdcp-mcls-refresh">' . __('Refresh', 'wdsi') . '</a>';
		echo $this->_create_hint(__('Select a default list you wish to subscribe your visitors to.', 'wdsi'));

		$subscription_message = $this->_get_option('mailchimp-subscription_message');
		$subscription_message = $subscription_message ? $subscription_message : __('All good, thank you!', 'wdsi');
		$subscription_message = wp_strip_all_tags($subscription_message);
		echo '<br />' .
			'<label for="wdsi-mailchimp-subscription_message">' . __('Successful subscription message:', 'wdsi') . '</label>&nbsp;' .
			'<input type="text" class="long" name="wdsi[mailchimp-subscription_message]" id="wdsi-mailchimp-subscription_message" value="' . esc_attr($subscription_message) . '" />' .
		'';
	}

	function create_custom_css_box () {
		$css = esc_textarea(wp_strip_all_tags($this->_get_option('css-custom_styles')));
		$placeholder = esc_attr(__('Additional CSS styles', 'wdsi'));
		echo "<textarea class='widefat' rows='8' name='wdsi[css-custom_styles]' placeholder='{$placeholder}'>{$css}</textarea>";
		echo $this->_create_hint(__('Add the additional CSS rules you wish to include', 'wdsi'));
	}

	function create_advanced_box () {
		echo '' .
			'<input type="hidden" name="wdsi[allow_shortcodes]" value="" />' .
			'<input type="checkbox" name="wdsi[allow_shortcodes]" id="wdsi-allow_shortcodes" value="1" ' . ($this->_get_option('allow_shortcodes') ? 'checked="checked"' : '') . ' />' .
			'&nbsp;' .
			'<label for="wdsi-allow_shortcodes">' . __('Allow shortcodes', 'wdsi') . '</label>' . 
		'';
	}
}