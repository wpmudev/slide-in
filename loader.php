<?php
/*
Plugin Name: Slide-In
Plugin URI: http://premium.wpmudev.org/project/slide-in/
Description: Create and manage beautiful marketing messages, then convert your audience in a way that doesn’t annoy them.
Version: 1.1.4
Text Domain: wdsi
Author: WPMU DEV
Author URI: http://premium.wpmudev.org
WDP ID: 694503

Copyright 2009-2011 Incsub (http://incsub.com) 
Authors - Jeffri Hong (Incsub), Victor Ivanov (Incsub), Ve Bailovity (Incsub)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define ('WDSI_PLUGIN_SELF_DIRNAME', basename(dirname(__FILE__)), true);
define ('WDSI_PROTOCOL', (@$_SERVER["HTTPS"] == 'on' ? 'https://' : 'http://'), true);

//Setup proper paths/URLs and load text domains
if (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDSI_PLUGIN_LOCATION', 'mu-plugins', true);
	define ('WDSI_PLUGIN_BASE_DIR', WPMU_PLUGIN_DIR, true);
	define ('WDSI_PLUGIN_URL', str_replace('http://', WDSI_PROTOCOL, WPMU_PLUGIN_URL), true);
	$textdomain_handler = 'load_muplugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . WDSI_PLUGIN_SELF_DIRNAME . '/' . basename(__FILE__))) {
	define ('WDSI_PLUGIN_LOCATION', 'subfolder-plugins', true);
	define ('WDSI_PLUGIN_BASE_DIR', WP_PLUGIN_DIR . '/' . WDSI_PLUGIN_SELF_DIRNAME, true);
	define ('WDSI_PLUGIN_URL', str_replace('http://', WDSI_PROTOCOL, WP_PLUGIN_URL) . '/' . WDSI_PLUGIN_SELF_DIRNAME, true);
	$textdomain_handler = 'load_plugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDSI_PLUGIN_LOCATION', 'plugins', true);
	define ('WDSI_PLUGIN_BASE_DIR', WP_PLUGIN_DIR, true);
	define ('WDSI_PLUGIN_URL', str_replace('http://', WDSI_PROTOCOL, WP_PLUGIN_URL), true);
	$textdomain_handler = 'load_plugin_textdomain';
} else {
	// No textdomain is loaded because we can't determine the plugin location.
	// No point in trying to add textdomain to string and/or localizing it.
	wp_die(__('There was an issue determining where Post Voting plugin is installed. Please reinstall.'));
}
$textdomain_handler('wdsi', false, WDSI_PLUGIN_SELF_DIRNAME . '/languages/');

require_once WDSI_PLUGIN_BASE_DIR . '/lib/class_wdsi_mailchimp.php';
require_once WDSI_PLUGIN_BASE_DIR . '/lib/class_wdsi_options.php';
require_once WDSI_PLUGIN_BASE_DIR . '/lib/functions.php';
/*
Wdsi_Options::populate();
*/

require_once WDSI_PLUGIN_BASE_DIR . '/lib/class_wdsi_slide_in.php';
Wdsi_SlideIn::init();

if (is_admin()) {
	// Setup dashboard notices
	if (file_exists(WDSI_PLUGIN_BASE_DIR . '/lib/external/wpmudev-dash-notification.php')) {
		global $wpmudev_notices;
		if (!is_array($wpmudev_notices)) $wpmudev_notices = array();
		$wpmudev_notices[] = array(
			'id' => 694503,
			'name' => 'Slide-In',
			'screens' => array(
				'edit-slide_in',
				'slide_in_page_wdsi',
			),
		);
		require_once WDSI_PLUGIN_BASE_DIR . '/lib/external/wpmudev-dash-notification.php';
	}
	// End dash bootstrap
	require_once WDSI_PLUGIN_BASE_DIR . '/lib/class_wdsi_admin_form_renderer.php';
	require_once WDSI_PLUGIN_BASE_DIR . '/lib/class_wdsi_admin_pages.php';
	Wdsi_AdminPages::serve();
} else {
	require_once WDSI_PLUGIN_BASE_DIR . '/lib/class_wdsi_public_pages.php';
	Wdsi_PublicPages::serve();
}