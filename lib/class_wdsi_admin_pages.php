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

		add_action('admin_print_scripts', array($this, 'js_print_scripts'));
		add_action('admin_print_styles', array($this, 'css_print_styles'));
	}
	
	function register_settings () {
		$form = new Wdsi_AdminFormRenderer;
		
		register_setting('wdsi', 'wdsi');
		add_settings_section('wdsi_settings', __('General settings', 'wdsi'), create_function('', ''), 'wdsi_options_page');
		add_settings_field('wdsi_show_after', __('Show after', 'wdsi'), array($form, 'create_show_after_box'), 'wdsi_options_page', 'wdsi_settings');
		add_settings_field('wdsi_position', __('Position', 'wdsi'), array($form, 'create_position_box'), 'wdsi_options_page', 'wdsi_settings');
		add_settings_field('wdsi_services', __('Services', 'wdsi'), array($form, 'create_services_box'), 'wdsi_options_page', 'wdsi_settings');
		add_settings_field('wdsi_custom_service', __('Add new Custom Service', 'wdsi'), array($form, 'create_custom_service_box'), 'wdsi_options_page', 'wdsi_settings');

		add_settings_section('wdsi_conditions', __('Conditions', 'wdsi'), create_function('', ''), 'wdsi_options_page');
		add_settings_field('wdsi_postitive_conditions', __('Show message box if...', 'wdsi'), array($form, 'create_conditions_box'), 'wdsi_options_page', 'wdsi_conditions');
		
	}
	
	function create_admin_menu_entry () {
		if (@$_POST && isset($_POST['option_page'])) {
			$changed = false;
			if ('wdsi_options_page' == @$_POST['option_page']) {
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
		$page = "edit.php?post_type=slide_in";
		$perms = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page($page, __('Settings', 'wdsi'), __('Settings', 'wdsi'), $perms, 'wdsi', array($this, 'create_admin_page'));
	}
	
	function create_admin_page () {
		include(WDSI_PLUGIN_BASE_DIR . '/lib/forms/plugin_settings.php');
	}
	
	function js_print_scripts () {
		if (!isset($_GET['page']) || 'wdsi' != $_GET['page']) return false;
		wp_enqueue_script( array("jquery", "jquery-ui-core", "jquery-ui-sortable", 'jquery-ui-dialog') );
	}

	function css_print_styles () {
		if (!isset($_GET['page']) || 'wdsi' != $_GET['page']) return false;
		wp_enqueue_style('wdsi-admin', WDSI_PLUGIN_URL . '/css/wdsi-admin.css');
		//wp_enqueue_style('jquery-ui-dialog', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	}

}