<?php
/**
 * Handles public functionality.
 */
class Wdsi_PublicPages {

	const COOKIE_HIDE_CONDITION = 'wdsi-on_hide';

	private $_data;
	private $_wdsi;

	private function __construct () {
		$this->_wdsi = Wdsi_SlideIn::get_instance();
		$this->_data = new Wdsi_Options;
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
		add_action('init', array($this, 'init_cookies'));

		add_action('wp_enqueue_scripts', array($this, 'css_load_styles'));
		add_action('wp_enqueue_scripts', array($this, 'js_load_scripts'));

		$hook = trim($this->_data->get_option('custom_injection_hook'));
		$hook = $hook ? $hook : Wdsi_SlideIn::get_default_injection_hook();
		add_action($hook, array($this, 'add_message'));
		
		add_filter('wdsi_content', 'wpautop');
		if ($this->_data->get_option('allow_shortcodes')) {
			add_filter('wdsi_content', 'do_shortcode');
		}
	}

	function init_cookies () {
		$on_hide = $this->_data->get_option('on_hide');
		$cookie_name = $this->_get_cookie_name();
		$request = preg_replace('/[^-_a-z0-9]/i', '_', $_SERVER['REQUEST_URI']);

		if (empty($on_hide)) {
			// First off, clear hiding cookie if needs be
			$_COOKIE[$cookie_name] = false;
			unset($_COOKIE[$cookie_name]);
			setcookie($cookie_name, '', time() - 86400, COOKIEPATH);
		
		} else if ('all' == $on_hide && !empty($_COOKIE[$cookie_name])) {
			// Next up, check sitewide policy
			define('WDSI_BOX_RENDERED', true, true); // Skip rendering

		} else if ('page' == $on_hide && !empty($_COOKIE["{$cookie_name}{$request}"])) {
			// Lastly, check individual URI path fragments
			define('WDSI_BOX_RENDERED', true, true); // Skip rendering
		}
		
		/*} else if ('page' == $on_hide && !empty($_COOKIE[$cookie_name])) {
			// Lastly, check individual URI path fragments
			$seen = json_decode(stripslashes($_COOKIE[$cookie_name]), true);
			if (is_array($seen) && in_array($_SERVER['REQUEST_URI'], $seen)) {
				define('WDSI_BOX_RENDERED', true, true); // Skip rendering
			}
		}*/
	}

	function js_load_scripts () {
		wp_enqueue_script('jquery');
		wp_enqueue_script('wdsi', WDSI_PLUGIN_URL . '/js/wdsi.js', array('jquery'), WDSI_CURRENT_VERSION);
		
		$on_hide = $this->_data->get_option('on_hide');
		$cookie_name = $this->_get_cookie_name();

		$valid_units = array('hours', 'days', 'weeks');
		$timeout_time = $this->_data->get_option('reshow_after-time');
		$timeout_units = $this->_data->get_option('reshow_after-units');
		if (empty($timeout_units) || !in_array($timeout_units, $valid_units)) $timeout_units = 'days';
		if (!empty($timeout_time)) {
			$now = time();
			$timeout_time = strtotime(sprintf("+%d %s", $timeout_time, $timeout_units), $now) - $now;
		}

		wp_localize_script('wdsi', '_wdsi_data', array(
			'reshow' => array(
				'timeout' => (int)$timeout_time,
				'name' => $cookie_name,
				'path' => COOKIEPATH,
				'all' => ('all' == $on_hide),
			),
		));
	}

	private function _get_cookie_name () {
		$hash = md5(
			$this->_data->get_option('on_hide') .
			$this->_data->get_option('reshow_after-time') .
			$this->_data->get_option('reshow_after-units')
		);
		return self::COOKIE_HIDE_CONDITION . $hash;
	}
	
	function css_load_styles () {
		if (!current_theme_supports('wdsi')) {
			wp_enqueue_style('wdsi', WDSI_PLUGIN_URL . '/css/wdsi.css', array(), WDSI_CURRENT_VERSION);
		}
		$opts = get_option('wdsi');
		if (empty($opts['css-custom_styles'])) return false;
		$style = wp_strip_all_tags($opts['css-custom_styles']);
		echo "<style type='text/css'>{$style}</style>";
	}

	private function _is_wrong_place () {
		global $wp_current_filter;

		if (is_feed()) return true; // Don't do this for feeds

		$is_excerpt = array_reduce($wp_current_filter, create_function('$ret,$val', 'return $ret ? true : preg_match("/excerpt/", $val);'), false);
		$is_head = array_reduce($wp_current_filter, create_function('$ret,$val', 'return $ret ? true : preg_match("/head\b|head[^w]/", $val);'), false);
		$is_title = array_reduce($wp_current_filter, create_function('$ret,$val', 'return $ret ? true : preg_match("/title/", $val);'), false);
		if ($is_excerpt || $is_head || $is_title) return true;
		
		// MarketPress virtual subpages
		if (class_exists('MarketPress') && !$this->_data->get_option('show_on_marketpress_pages')) {
			global $mp;
			if ($mp->is_shop_page && !is_singular('product')) return true;
		}

		return false;
	}
	
	function add_message () {
		if (defined('WDSI_BOX_RENDERED')) return false;
		if ($this->_is_wrong_place()) return false;
		
		global $post, $current_user;
		
		// if is selected as no show, also return false
		if (!empty($post->ID)) {
			$do_not_show = get_post_meta($post->ID, 'wdsi_do_not_show', true);
			if ($do_not_show) return false;
		}
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

		$no_count = wdsi_getval($opts, 'no_count');
		$no_count = is_array($no_count) ? $no_count : array();

		$content_type = wdsi_getval($type, 'content_type', 'text');
		if ('widgets' == $content_type && !$this->_data->get_option('allow_widgets')) return false; // Break on this

		$related_posts_count = wdsi_getval($type, 'related-posts_count', 3);
		$related_taxonomy = wdsi_getval($type, 'related-taxonomy', 'post_tag');
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
}