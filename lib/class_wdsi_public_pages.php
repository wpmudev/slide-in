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
		$is_excerpt = array_reduce(
			$wp_current_filter,
			function( $ret, $val ) {
				return $ret ? true : preg_match( "/excerpt/", $val );
			},
			false
		);
		$is_head    = array_reduce(
			$wp_current_filter,
			function( $ret, $val ) { 
				return $ret ? true : preg_match( "/head\b|head[^w]/", $val );
			},
			false
		);
		$is_title    = array_reduce(
			$wp_current_filter,
			function( $ret, $val ) {
				return $ret ? true : preg_match( "/title/", $val );
			},
			false
		);
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
		
		global $post;
		
		// if is selected as no show, also return false
		if (!empty($post->ID)) {
			$do_not_show = get_post_meta($post->ID, 'wdsi_do_not_show', true);
			if ($do_not_show) return false;
		}
		$opts = get_option('wdsi');
		
		$message = $this->_wdsi->get_message_data($post);
		if (!$message) return false;

		Wdsi_SlideIn::message_markup($message, $opts);
		define ('WDSI_BOX_RENDERED', true);
	}
}