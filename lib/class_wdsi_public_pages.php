<?php
/**
 * Handles public functionality.
 */
class Wdsi_PublicPages {
	private $_data;
	
	private $_wdsi;

	private function __construct () {
		$this->_wdsi = Wdsi_SlideIn::get_instance();
		/*
		$this->_data = new Wdsi_Options;
		*/
	}

	/**
	 * Main entry point.
	 *
	 * @static
	 */
	public static function serve () {
		$me = new Wdsi_PublicPages;
		$me->add_hooks();
	}
	
	function add_hooks () {
		add_action('wp_print_scripts', array($this, 'js_load_scripts'));
		add_action('wp_print_styles', array($this, 'css_load_styles'));

		add_action('loop_end', array($this, 'add_message'));
		
		add_filter('wdsi_content', 'wpautop');
	}	

	function js_load_scripts () {
		wp_enqueue_script('jquery');
		wp_enqueue_script('wdsi', WDSI_PLUGIN_URL . '/js/wdsi.js', array('jquery'), '1.0.1');
	}
	
	function css_load_styles () {
		if (!current_theme_supports('wdsi')) {
			wp_enqueue_style('wdsi', WDSI_PLUGIN_URL . '/css/wdsi.css', array(), '1.0.1');
		}
		$opts = get_option('wdsi');
		if (empty($opts['css-custom_styles'])) return false;
		$style = wp_strip_all_tags($opts['css-custom_styles']);
		echo "<style type='text/css'>{$style}</style>";
	}
	
	function add_message () {
		//if (!is_singular()) return false;
		// if is selected as no show, also return false
		if (defined('WDSI_BOX_RENDERED')) return false;
		
		global $post, $current_user;
		$opts = get_option('wdsi');
		
		$message = $this->_wdsi->get_message_data($post);
		if (!$message) return false;
		$msg = get_post_meta($message->ID, 'wdsi', true);
		$type = get_post_meta($message->ID, 'wdsi-type', true);
		
		$services = wdsi_getval($msg, 'services');
		$services = $services ? $services : wdsi_getval($opts, 'services');
		$services = is_array($services) ? $services : array();

		$skip_script = wdsi_getval($opts, 'skip_script');
		$skip_script = is_array($skip_script) ? $skip_script : array();

		$content_type = wdsi_getval($type, 'content_type', 'text');
		$related_posts_count = wdsi_getval($type, 'related-posts_count', 3);
		$related_has_thumbnails = wdsi_getval($type, 'related-has_thumbnails');

		$mailchimp_placeholder = wdsi_getval($type, 'mailchimp-placeholder', 'you@yourdomain.com');
		$mailchimp_position = wdsi_getval($type, 'mailchimp-position', 'after');

		$position = wdsi_getval($msg, 'position') ? $msg['position'] : wdsi_getval($opts, 'position');
		$position = $position ? $position : 'left';

		$percentage = $selector = $timeout = false;
		$condition =  wdsi_getval($msg, 'show_after-condition') ? $msg['show_after-condition'] :wdsi_getval($opts, 'show_after-condition');
		$value = wdsi_getval($msg, 'show_after-rule') ? $msg['show_after-rule'] : wdsi_getval($opts, 'show_after-rule');
		switch ($condition) {
			case "selector":
				$selector = "#{$value}";
				$percentage = '0%';
				$timeout = '0s';
				break;
			case "timeout":
				$selector = false;
				$percentage = '0%';
				$timeout = sprintf('%ds', (int)$value);
				break;
			case "percentage":
			default:
				$selector = false;
				$percentage = sprintf('%d%%', (int)$value);
				$timeout = '0s';
				break;
		}

		$_theme = wdsi_getval($msg, 'theme') ? $msg['theme'] : wdsi_getval($opts, 'theme');
		$theme = $_theme && in_array($_theme, array_keys(Wdsi_SlideIn::get_appearance_themes())) ? $_theme : 'minimal';

		$_variation = wdsi_getval($msg, 'variation') ? $msg['variation'] : wdsi_getval($opts, 'variation');
		$variation = $_variation && in_array($_variation, array_keys(Wdsi_SlideIn::get_theme_variations())) ? $_variation : 'light';
		
		$_scheme = wdsi_getval($msg, 'scheme') ? $msg['scheme'] : wdsi_getval($opts, 'scheme');
		$scheme = $_scheme && in_array($_scheme, array_keys(Wdsi_SlideIn::get_variation_schemes())) ? $_scheme : 'red';

		$expire_after = wdsi_getval($msg, 'show_for-time') ? $msg['show_for-time'] : wdsi_getval($opts, 'show_for-time');
		$expire_after = $expire_after ? $expire_after : 10;
		$expire_unit = wdsi_getval($msg, 'show_for-unit') ? $msg['show_for-unit'] : wdsi_getval($opts, 'show_for-unit');
		$expire_unit = $expire_unit ? $expire_unit : 's';
		$expire_timeout = sprintf("%d%s", $expire_after, $expire_unit);

		$full_width = $width = false;
		$_width = wdsi_getval($msg, 'width') ? $msg['width'] : wdsi_getval($opts, 'width');
		if (!(int)$_width || 'full' == $width) {
			$full_width = 'slidein-full';
		} else {
			$width = 'style="width:' . (int)$_width . 'px;"';
		}
		
		require_once (WDSI_PLUGIN_BASE_DIR . '/lib/forms/box_output.php');
		//$this->_js_set_up_globals($message->ID);
		define ('WDSI_BOX_RENDERED', true);
	}
/*
	private function _js_set_up_globals ($post_id) {
		if (defined('WDSI_BOX_RENDERED')) return false;
		
		$opts = get_option('wdsi');
		$msg = get_post_meta($post_id, 'wdsi', true);

		$rule = @$msg['show_after-rule'] ? $msg['show_after-rule'] : @$opts['show_after-rule'];
		$condition = @$msg['show_after-condition'] ? $msg['show_after-condition'] : @$opts['show_after-condition'];

		printf(
			'<script type="text/javascript">var _wdsi_data={
				"root_url": "%s", 
				"ajax_url": "%s",
				"show_after": {
					"rule": "%s",
					"condition": "%s"
				}
			};</script>',
			WDSI_PLUGIN_URL, admin_url('admin-ajax.php'),
			$rule,
			$condition
		);
	}
*/
}