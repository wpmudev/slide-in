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
		add_action('wp_head', array($this, 'js_set_up_globals'));
		add_action('wp_print_scripts', array($this, 'js_load_scripts'));
		add_action('wp_print_styles', array($this, 'css_load_styles'));

		add_action('loop_end', array($this, 'add_message'));
	}

	function js_set_up_globals () {
		$opts = get_option('wdsi');
		printf(
			'<script type="text/javascript">var _wdsi_data={
				"root_url": "%s", 
				"ajax_url": "%s",
				"after_percent": %d
			};</script>',
			WDSI_PLUGIN_URL, admin_url('admin-ajax.php'),
			((int)@$opts['show_after'] ? (int)@$opts['show_after'] : 66) 
		);
	}

	function js_load_scripts () {
		wp_enqueue_script('jquery');
		wp_enqueue_script('wdsi', WDSI_PLUGIN_URL . '/js/wdsi.js', array('jquery'));
	}
	
	function css_load_styles () {
		if (!current_theme_supports('wdsi')) {
			wp_enqueue_style('wdsi', WDSI_PLUGIN_URL . '/css/wdsi.css');
		}
	}
	
	function add_message () {
		if (!is_singular()) return false;
		// if is selected as no show, also return false
		if (defined('WDSI_BOX_RENDERED')) return false;
		
		global $post, $current_user;
		$opts = get_option('wdsi');
		
		// Check general settings
		if (@$opts['show_if_logged_in'] && !$current_user->id) return false;
		if (@$opts['show_if_not_logged_in'] && $current_user->id) return false;
		if (@$opts['show_if_never_commented'] && isset($_COOKIE['comment_author_'.COOKIEHASH])) return false;
		
		$message = $this->_wdsi->get_message_data($post);
		
		$services = @$opts['services'];
		$services = is_array($services) ? $services : array();

		$skip_script = @$opts['skip_script'];
		$skip_script = is_array($skip_script) ? $skip_script : array();
		
		require_once (WDSI_PLUGIN_BASE_DIR . '/lib/forms/box_output.php');
		define ('WDSI_BOX_RENDERED', true);
	}

}