<?php
/**
 * Admin pages handler.
 */
class Wdsi_AdminPages {
	private $_data;
	
	private $_wdsi;

	private function __construct () {
		$this->_wdsi = Wdsi_SlideIn::get_instance();
		/*
		$this->_data = new Wdsi_Options;
		*/
	}

	public static function serve () {
		$me = new Wdsi_AdminPages;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action('admin_init', array($this, 'register_settings'));
		$hook = (defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN) ? 'network_admin_menu' : 'admin_menu';
		add_action($hook, array($this, 'create_admin_menu_entry'));

		// Post meta boxes
		add_action('admin_init', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_meta'));

		add_action('admin_print_scripts', array($this, 'js_print_scripts'));
		add_action('admin_print_styles', array($this, 'css_print_styles'));
	}

	function add_meta_boxes () {
		add_meta_box(
			'wdsi_message_override',
			__('Slide-In Message Override', 'wdsm'),
			array($this, 'render_message_override_box'),
			'post',
			'side',
			'low'
		);
	}

	function render_message_override_box () {
		global $post;
		$msg_id = get_post_meta($post->ID, 'wdsi_message_id', true);
		$query = new WP_Query(array(
			'post_type' => Wdsi_SlideIn::POST_TYPE,
			'post_status' => Wdsi_SlideIn::NOT_IN_POOL_STATUS,
		));
		$messages = $query->posts;

		_e('This post will not get a slide-in message from the pool - it will always use this message', 'wdsi');
		echo '<select name="wdsi-message_override">';
		echo '<option value=""></option>';
		foreach ($messages as $message) {
			$selected = ($message->ID == $msg_id) ? 'selected="selected"' : '';
			echo "<option value='{$message->ID}'>{$message->post_title}</option>";
		}
		echo '</select>';
	}

	function save_meta () {
		global $post;
		if ('post' != $post->post_type) return false;
		if (isset($_POST['wdsi-message_override'])) {
			if ($_POST['wdsi-message_override']) update_post_meta($post->ID, 'wdsi_message_id', $_POST['wdsi-message_override']);
		}
	}
	
	function register_settings () {
		$form = new Wdsi_AdminFormRenderer;
		
		register_setting('wdsi', 'wdsi');
		
		add_settings_section('wdsi_behavior', __('Behaviour settings', 'wdsi'), create_function('', ''), 'wdsi_options_page');
		add_settings_field('wdsi_show_after', __('Show message', 'wdsi'), array($form, 'create_show_after_box'), 'wdsi_options_page', 'wdsi_behavior');
		add_settings_field('wdsi_show_for', __('Hide message after', 'wdsi'), array($form, 'create_show_for_box'), 'wdsi_options_page', 'wdsi_behavior');


		add_settings_section('wdsi_appearance', __('Appearance settings', 'wdsi'), create_function('', ''), 'wdsi_options_page');
		add_settings_field('wdsi_position', __('Message position', 'wdsi'), array($form, 'create_position_box'), 'wdsi_options_page', 'wdsi_appearance');
		add_settings_field('wdsi_width', __('Message width', 'wdsi'), array($form, 'create_msg_width_box'), 'wdsi_options_page', 'wdsi_appearance');
		add_settings_field('wdsi_appearance', __('Message style', 'wdsi'), array($form, 'create_appearance_box'), 'wdsi_options_page', 'wdsi_appearance');
		add_settings_field('wdsi_color_scheme', __('Color scheme', 'wdsi'), array($form, 'create_color_scheme_box'), 'wdsi_options_page', 'wdsi_appearance');
		
		add_settings_field('wdsi_services', __('Social media services', 'wdsi'), array($form, 'create_services_box'), 'wdsi_options_page', 'wdsi_appearance');
		//add_settings_field('wdsi_custom_service', __('Add new Custom Service', 'wdsi'), array($form, 'create_custom_service_box'), 'wdsi_options_page', 'wdsi_appearance');

		//add_settings_section('wdsi_conditions', __('Conditions', 'wdsi'), create_function('', ''), 'wdsi_options_page');
		//add_settings_field('wdsi_postitive_conditions', __('Show message box if...', 'wdsi'), array($form, 'create_conditions_box'), 'wdsi_options_page', 'wdsi_conditions');
		
	}
	
	function create_admin_menu_entry () {
		if (@$_POST && isset($_POST['option_page'])) {
			$changed = false;
			if ('wdsi_options_page' == wdsi_getval($_POST, 'option_page')) {
				$services = $_POST['wdsi']['services'];
				$services = is_array($services) ? $services : array();
				if (@$_POST['wdsi']['new_service']['name'] && @$_POST['wdsi']['new_service']['code']) {
					$services[] = $_POST['wdsi']['new_service'];
					unset($_POST['wdsi']['new_service']);
				}
				foreach ($services as $key=>$service) {
					$services[$key]['code'] = stripslashes($service['code']);
				}
				$_POST['wdsi']['services'] = $services;
				update_option('wdsi', $_POST['wdsi']);
				$changed = true;
			}

			if ($changed) {
				$goback = add_query_arg('settings-updated', 'true',  wp_get_referer());
				wp_redirect($goback);
				die;
			}
		}
		$page = "edit.php?post_type=" . Wdsi_SlideIn::POST_TYPE;
		$perms = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page($page, __('Settings', 'wdsi'), __('Settings', 'wdsi'), $perms, 'wdsi', array($this, 'create_admin_page'));
	}
	
	function create_admin_page () {
		include(WDSI_PLUGIN_BASE_DIR . '/lib/forms/plugin_settings.php');
	}
	
	function js_print_scripts () {
		if (isset($_GET['page']) && 'wdsi' == $_GET['page']) {
			wp_enqueue_script( array("jquery", "jquery-ui-core", "jquery-ui-sortable", 'jquery-ui-dialog') );
		}
		global $post;
		if (is_object($post) && isset($post->post_type) && Wdsi_SlideIn::POST_TYPE == $post->post_type) {
			wp_enqueue_script('wdsi-admin', WDSI_PLUGIN_URL . '/js/wdsi-admin.js', array('jquery'));
			wp_localize_script('wdsi-admin', 'l10nWdsi', array(
				'clear_set' => __('Clear this set', 'wdsi'),
			));
		}
	}

	function css_print_styles () {
		// Menu icon hack goes into all admin pages, so add it inline instead of queueing up yet another stylehseet just for this
		$base_url = WDSI_PLUGIN_URL;
		echo <<<EoWdsiAdminCss
<style type="text/css">
li.menu-icon-slide_in div.wp-menu-image { background: url({$base_url}/img/admin-menu-icon.png) no-repeat bottom; }
li.menu-icon-slide_in:hover div.wp-menu-image, 
li.menu-icon-slide_in.wp-has-current-submenu div.wp-menu-image 
{ background-position: top; }
li.menu-icon-slide_in div.wp-menu-image img { display: none; }
</style>
EoWdsiAdminCss;
		// The rest is slide in specific, enqueue only when needed
		if (isset($_GET['page']) && 'wdsi' == $_GET['page']) {
			wp_enqueue_style('wdsi-admin', WDSI_PLUGIN_URL . '/css/wdsi-admin.css');
		}
		global $post;
		if (is_object($post) && isset($post->post_type) && Wdsi_SlideIn::POST_TYPE == $post->post_type) {
			wp_enqueue_style('wdsi-admin', WDSI_PLUGIN_URL . '/css/wdsi-admin.css');
		}
		//wp_enqueue_style('jquery-ui-dialog', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	}

}