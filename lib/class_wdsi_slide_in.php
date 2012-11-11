<?php

class Wdsi_SlideIn {
	
	private static $_instance;

	const NOT_IN_POOL_STATUS = 'wdsi_not_in_pool';
	const POST_TYPE = 'slide_in';
	
	private function __construct () {}
	
	/**
	 * Glues everything together and initialize singleton.
	 */
	public static function init () {
		if (!isset(self::$_instance)) self::$_instance = new self;

		add_action('init', array(self::$_instance, 'register_post_type'));
		add_action('admin_init', array(self::$_instance, 'add_meta_boxes'));
		add_action('save_post', array(self::$_instance, 'save_meta'), 9); // Bind it a bit earlier, so we can kill Post Indexer actions.
		add_action('wp_insert_post_data', array(self::$_instance, 'set_up_post_status'));

		add_filter("manage_edit-" . self::POST_TYPE . "_columns", array(self::$_instance, "add_custom_columns"));
		add_action("manage_posts_custom_column",  array(self::$_instance, "fill_custom_columns"));
	}

	/**
	 * Prepared singleton object getting routine.
	 */
	public static function get_instance () {
		return self::$_instance;
	}


/* ----- Static info getters ----- */

	/**
	 * Get known themes.
	 */
	public static function get_appearance_themes () {
		return array(
			'minimal' => __('Minimal', 'wdsi'),
			'rounded' => __('Rounded', 'wdsi'),
		);
	}
	
	/**
	 * Get known theme variations.
	 */
	public static function get_theme_variations () {
		return array(
			'light' => __('Light', 'wdsi'),
			'dark' => __('Dark', 'wdsi'),
		);
	}

	/**
	 * Get known variation schemes.
	 */
	public static function get_variation_schemes () {
		return array(
			'red' => __('Red', 'wdsi'),
			'green' => __('Green', 'wdsi'),
			'blue' => __('Blue', 'wdsi'),
			'orange' => __('Orange', 'wdsi'),
		);
	}

	
/* ----- Handlers ----- */
	
	function register_post_type () {
		$supports = apply_filters(
			'wdsi-slide_in-post_type-supports',
			array('title', 'editor')
		);
		// Force required support
		if (!in_array('title', $supports)) $supports[] = 'title';
		if (!in_array('editor', $supports)) $supports[] = 'editor';
		
		register_post_type(self::POST_TYPE, array(
			'labels' => array(
				'name' => __('Slide In', 'wdsi'),
				'singular_name' => __('Slide In Message', 'wdsi'),
				'add_new_item' => __('Add new Slide In Message', 'wdsi'),
				'edit_item' => __('Edit Slide In Message', 'wdsi'),
			),
			'menu_icon' => WDSI_PLUGIN_URL . '/img/admin-menu-icon.png',
			'public' => false,
			'show_ui' => true,
			'supports' => $supports,
		));
		register_post_status(self::NOT_IN_POOL_STATUS, array('protected' => true));
	}
	
	function add_meta_boxes () {
		add_meta_box(
			'wdsi_conditions',
			__('Conditions', 'wdsm'),
			array($this, 'render_conditions_box'),
			self::POST_TYPE,
			'side',
			'high'
		);
		add_meta_box(
			'wdsi_show_override',
			__('Appearance overrides', 'wdsm'),
			array($this, 'render_show_after_box'),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}
	
	function render_conditions_box () {
		global $post;
		$show_options = get_post_meta($post->ID, 'wdsi_show_if', true);
		
		echo '<p>' .
			'<input type="checkbox" name="not_in_the_pool" id="wdsi-not_in_the_pool" value="1" ' .
				($post->post_status == self::NOT_IN_POOL_STATUS ? 'checked="checked"' : '') .
			' />' .
			'&nbsp;' .
			'<label for="wdsi-not_in_the_pool">' . __('Not in the pool', 'wdsi') . '</label>' .
		'</p>';

		echo '<div id="wdsi-conditions-container" class="wpmudev-ui" style="display:none">';
		
		echo '<h4>' . __('Show message if...', 'wdsi') . '</h4>';

		$show_if = wdsi_getval($show_options, 'user');
		echo '<fieldset id="wdsi-user_rules"><legend>' . __('User rules', 'wdsi') . '</legend>';
		echo '' .
			'<input type="radio" name="show_if[user]" value="show_if_logged_in" id="show_if_logged_in-yes" ' .
				('show_if_logged_in' == $show_if ? 'checked="checked"' : '') .
			'/ >' .
			' <label for="show_if_logged_in-yes">' . __('... the user is logged in', 'wdsi') . '</label>' .
		'<br />';
		echo '' .
			'<input type="radio" name="show_if[user]" value="show_if_not_logged_in" id="show_if_not_logged_in-yes" ' .
				('show_if_not_logged_in' == $show_if ? 'checked="checked"' : '') .
			'/ >' .
			' <label for="show_if_not_logged_in-yes">' . __('... the user is <b>NOT</b> logged in', 'wdsi') . '</label> ' .
		'<br />';
		echo '' .
			'<input type="radio" name="show_if[user]" value="show_if_never_commented" id="show_if_never_commented-yes" ' .
				('show_if_never_commented' == $show_if ? 'checked="checked"' : '') .
			'/ >' .
			' <label for="show_if_never_commented-yes">' . __('... the user never commented on your site before', 'wdsi') . '</label> ' .
		'<br />';
		echo '</fieldset>';

		$show_if = wdsi_getval($show_options, 'page');
		echo '<fieldset id="wdsi-page_rules"><legend>' . __('Page rules', 'wdsi') . '</legend>';
		echo '' .
			'<input type="radio" name="show_if[page]" value="show_if_singular" id="show_if_singular-yes" ' .
				('show_if_singular' == $show_if ? 'checked="checked"' : '') .
			'/ >' .
			' <label for="show_if_singular-yes">' . __('... on singular pages', 'wdsi') . '</label>' .
		'<br />';
		echo '' .
			'<input type="radio" name="show_if[page]" value="show_if_not_singular" id="show_if_not_singular-yes" ' .
				('show_if_not_singular' == $show_if ? 'checked="checked"' : '') .
			'/ >' .
			' <label for="show_if_not_singular-yes">' . __('... on archive pages', 'wdsi') . '</label>' .
		'<br />';
		echo '</fieldset>';
		
		echo '</div>';
	}

	function render_show_after_box () {
		global $post;
		$opts = get_post_meta($post->ID, 'wdsi', true);
		$condition = wdsi_getval($opts, 'show_after-condition');
		$value = wdsi_getval($opts, 'show_after-rule');
		
		switch ($condition) {
			case "selector":
				$selector = 'checked="checked"';
				break;
			case "timeout":
				$timeout = 'checked="checked"';
				$value = (int)$value;
				break;
			case "percentage":
				$percentage = 'checked="checked"';
				$value = (int)$value;
				break;
		}

		$override_checked = ($percentage || $timeout || $selector) ? 'checked="checked"' : '';
		echo '<input type="checkbox" id="wdsi-override_show_if" name="wsdi-appearance_override" value="1" ' . $override_checked . ' /> ' .
			'<label for="wdsi-override_show_if">' . __('Override message display rule', 'wdsi') . '</label>' .
		'';

		echo '<div id="wdsi-show_after_overrides-container" class="wpmudev-ui" style="display:none">';

		// Initial condition
		echo '<fieldset id="wdsi-show_after"><legend>' . __('Show after', 'wdsi') . '</legend>';
		
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
		echo '</fieldset>';

		// Timeout
		echo '<fieldset id="wdsi-show_for"><legend>' . __('Show for', 'wdsi') . '</legend>';
		$time = wdsi_getval($opts, 'show_for-time');
		$unit = wdsi_getval($opts, 'show_for-unit');

		$_times = array_combine(range(1,59), range(1,59));
		$_units = array(
			's' => __('Seconds', 'wdsi'),
			'm' => __('Minutes', 'wdsi'),
			'h' => __('Hours', 'wdsi'),
		);

		echo "<div class='wpmudev-ui-select'><select name='wdsi[show_for-time]'>";
		foreach ($_times as $_time) {
			$selected = $_time == $time ? 'selected="selected"' : '';
			echo "<option value='{$_time}' {$selected}>{$_time}</option>";
		}
		echo "</select></div>";

		echo "<div class='wpmudev-ui-select'><select name='wdsi[show_for-unit]'>";
		foreach ($_units as $key => $_unit) {
			$selected = $key == $unit ? 'selected="selected"' : '';
			echo "<option value='{$key}' {$selected}>{$_unit}</option>";
		}
		echo "</select></div>";
		echo '</fieldset>';

		// Position
		echo '<fieldset id="wdsi-position"><legend>' . __('Position', 'wdsi') . '</legend>';
		echo '<div  class="wpmudev-ui-element_container">';
		$pos = wdsi_getval($opts, 'position');
		echo '<div class="position-control">' .
			$this->_create_radiobox('position', 'left', $pos) .
			$this->_create_radiobox('position', 'top', $pos) .
			$this->_create_radiobox('position', 'right', $pos) .
			$this->_create_radiobox('position', 'bottom', $pos) .
		'</div>';
		echo '</div>';

		echo '<h4>' . __('Width', 'wdsi') . '</h4>';
		$width = wdsi_getval($opts, 'width');
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
		echo '</fieldset>';

		// Theme
		echo '<fieldset id="wdsi-appearance"><legend>' . __('Appearance', 'wdsi') . '</legend>';
		echo '<h4>' . __('Theme', 'wdsi') . '</h4>';
		$_themes = self::get_appearance_themes();
		foreach ($_themes as $theme => $label) {
			echo $this->_create_radiobox('theme', $theme, wdsi_getval($opts, 'theme')) .
				'<label for="theme-' . esc_attr($theme) . '">' . esc_html($label) . '</label><br />';
		}
		echo '<h4>' . __('Variation', 'wdsi') . '</h4>';
		$_themes = self::get_theme_variations();
		foreach ($_themes as $theme => $label) {
			echo $this->_create_radiobox('variation', $theme, wdsi_getval($opts, 'variation')) .
				'<label for="variation-' . esc_attr($theme) . '">' . esc_html($label) . '</label><br />';
		}
		echo '<h4>' . __('Color Scheme', 'wdsi') . '</h4>';
		echo '<div class="wdsi-complex_element-container">';
		$_themes = self::get_variation_schemes();
		foreach ($_themes as $theme => $label) {
			echo $this->_create_color_radiobox('scheme', $theme, $label, wdsi_getval($opts, 'scheme')) .
				//'<label for="scheme-' . esc_attr($theme) . '">' . esc_html($label) . '</label><br />' .
			'';
		}
		echo '</div>';
		echo '</fieldset>';

		echo '</div>';
	}

	/**
	 * Saves metabox data.
	 */
	function save_meta () {
		global $post;
		if (self::POST_TYPE != $post->post_type) return false;
		if (wdsi_getval($_POST, 'show_if')) {
			// If we have Post Indexer present, remove the post save action for the moment.
			if (function_exists('post_indexer_post_insert_update')) {
				remove_action('save_post', 'post_indexer_post_insert_update');
			}
			update_post_meta($post->ID, "wdsi_show_if", wdsi_getval($_POST, "show_if"));
		}

		if (wdsi_getval($_POST, 'wdsi')) {
			// If we have Post Indexer present, remove the post save action for the moment.
			if (function_exists('post_indexer_post_insert_update')) {
				remove_action('save_post', 'post_indexer_post_insert_update');
			}
			if (!empty($_POST['wsdi-appearance_override'])) update_post_meta($post->ID, "wdsi", wdsi_getval($_POST, "wdsi"));
			else update_post_meta($post->ID, "wdsi", false);
		}

	}

	/**
	 * Updates pool status.
	 */
	function set_up_post_status ($data) {
		if (self::POST_TYPE != $data['post_type']) return $data;
		if (wdsi_getval($_POST, 'not_in_the_pool')) {
			$data['post_status'] = self::NOT_IN_POOL_STATUS;
		}
		return $data;
	}

	function add_custom_columns ($cols) {
		return array_merge($cols, array(
			'wdsi_pool' => __('Status', 'wdsm'),
			'wdsi_conditions' => __('Conditions', 'wdsm'),
		));
	}

	function fill_custom_columns ($col) {
		global $post;
		if ('wdsi_pool' != $col && 'wdsi_conditions' != $col) return $col;
		
		switch ($col) {
			case 'wdsi_pool':
				echo ('publish' == $post->post_status ? __('In the pool', 'wdsi') : __('Not in pool', 'wdsi'));
				break;
			case 'wdsi_conditions':
				if (self::NOT_IN_POOL_STATUS == $post->post_status) {
					_e("Not applicable", 'wdsi');
					break;
				}
				$show = get_post_meta($post->ID, 'wdsi_show_if', true);
				switch (wdsi_getval($show, 'user')) {
					case "show_if_logged_in":
						_e("Shown for logged in users", 'wdsi');
						break;
					case "show_if_not_logged_in":
						_e("Shown for visitors", 'wdsi');
						break;
					case "show_if_never_commented":
						_e("Shown for non-commenters", 'wdsi');
						break;
					default:
						_e("Can appear for all users", 'wdsi');
				}
				echo '<br />';
				switch (wdsi_getval($show, 'page')) {
					case "show_if_singular":
						_e("Shown on singular pages", 'wdsi');
						break;
					case "show_if_not_singular":
						_e("Shown on archive pages", 'wdsi');
						break;
					default:
						_e("Can appear on all pages", 'wdsi');
				}

				break;
		}
	}

	
/* ----- Model procedures: message ----- */


	public function get_message_data ($post) {
		$post_id = (is_object($post) && isset($post->ID)) ? $post->ID : (int)$post_id;
		
		// ...
		
		//$post_id = 2852;
		$pool = $this->_get_active_messages_pool($post_id);
		return $pool ? $pool[0] : false;
	}


/* ----- Model procedures: pool ----- */


	/**
	 * Fetching out all the currently active messages.
	 */
	private function _get_active_messages_pool ($specific_post_id=false) {
		$pool = array();
		$query = new WP_Query(array(
			'post_type' => self::POST_TYPE,
		));
		$pool = $query->posts ? $query->posts : array();

		if ($specific_post_id) {
			$msg_id = get_post_meta($specific_post_id, 'wdsi_message_id', true);
			if ($msg_id) $pool = array(get_post($msg_id));
		}
		$pool = array_filter($pool, array($this, '_filter_active_messages_pool'));
		shuffle($pool);

		return $pool;
	}

	/**
	 * Filters messages in pool to active ones.
	 * `array_filter` callback.
	 */
	function _filter_active_messages_pool ($msg) {
		$use = true;
		$show = get_post_meta($msg->ID, 'wdsi_show_if', true);
		switch (wdsi_getval($show, 'user')) {
			case "show_if_logged_in":
				$use = is_user_logged_in(); break;
			case "show_if_not_logged_in":
				$use = !(is_user_logged_in()); break;
			case "show_if_never_commented":
				$use = isset($_COOKIE['comment_author_'.COOKIEHASH]); break;
		}
		if (!$use) return $use;
		switch (wdsi_getval($show, 'page')) {
			case "show_if_singular":
				$use = is_singular(); break;
			case "show_if_not_singular":
				$use = !(is_singular()); break;
		}
		return $use; // In the pool, by default
	}

	function _create_radiobox ($name, $value, $option) {
		$checked = (@$option == $value) ? true : false;
		$class = $value ? "class='{$value}'" : '';
		return "<input type='radio' name='wdsi[{$name}]' {$class} id='{$name}-{$value}' value='{$value}' " . ($checked ? 'checked="checked" ' : '') . " /> ";
	}

	function _create_color_radiobox ($name, $value, $label, $option) {
		$color = esc_attr($value);
		$label= esc_attr($label);
		return "<label class='wdsi-color-container' for='{$name}-{$value}'>" .
			$this->_create_radiobox($name, $value, $option) .
			"<div class='wdsi-color wdsi-{$color}' title='{$label}'></div>" .
		'</label>';
	}
}
