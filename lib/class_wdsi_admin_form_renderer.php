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
		$after = (int)$this->_get_option('show_after');
		$after = $after ? $after : 66;
		
		$percentage = '<select name="wdsi[show_after]" id="wdsi-show_after">';
		for ($i=1; $i<100; $i++) {
			$selected = ($i == $after) ? 'selected="selected"' : '';
			$percentage .= "<option value='{$i}' {$selected}>{$i}&nbsp;</option>";
		}
		$percentage .= '</select>';
		echo '<label for="wdsi-show_after">' . 
			sprintf(__('Show message after %s%% of my page has been viewed', 'wdsi'), $percentage) . 
		'</label>';
	}
	
	function create_position_box () {
		echo '' . 
		$this->_create_radiobox('position', 'left') . 
			'<label for="position-left">' . __('Left', 'wdsi') . '</label>' .
			'<br />' .
			$this->_create_radiobox('position', 'right') . 
			'<label for="position-right">' . __('Right', 'wdsi') . '</label>' .
		'';
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
}