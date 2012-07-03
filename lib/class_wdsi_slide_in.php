<?php

class Wdsi_SlideIn {
	
	private static $_instance;
	
	private function __construct () {}
	
	/**
	 * Glues everything together and initialize singleton.
	 */
	public static function init () {
		if (!isset(self::$_instance)) self::$_instance = new self;

		add_action('init', array(self::$_instance, 'register_post_type'));
		add_action('admin_init', array(self::$_instance, 'add_meta_boxes'));
		//add_action('save_post', array(self::$_instance, 'save_meta'), 9); // Bind it a bit earlier, so we can kill Post Indexer actions.

		//add_filter("manage_edit-social_marketing_ad_columns", array(self::$_instance, "add_custom_columns"));
		//add_action("manage_posts_custom_column",  array(self::$_instance, "fill_custom_columns"));
	}

	/**
	 * Prepared singleton object getting routine.
	 */
	public static function get_instance () {
		return self::$_instance;
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
		
		register_post_type('slide_in', array(
			'labels' => array(
				'name' => __('Slide In', 'wdsi'),
				'singular_name' => __('Slide In Message', 'wdsi'),
				'add_new_item' => __('Add new Slide In Message', 'wdsi'),
				'edit_item' => __('Edit Slide In Message', 'wdsi'),
			),
			'menu_icon' => WDSM_PLUGIN_URL . '/img/menu_inactive.png',
			'public' => false,
			'show_ui' => true,
			'supports' => $supports,
		));
	}
	
	function add_meta_boxes () {
		add_meta_box(
			'wdsi_conditions',
			__('Conditions', 'wdsm'),
			array($this, 'render_conditions_box'),
			'slide_in',
			'normal',
			'high'
		);
	}
	
	function render_conditions_box () {
		echo '<p>This is where content conditions will go</p>';
	}
	
/* ----- Model procedures ----- */

	function get_message_data ($post) {
		$post_id = (is_object($post) && isset($post->ID)) ? $post->ID : (int)$post_id;
		
		// ...
		
		//$post_id = 2852;
		$query = new WP_Query(array(
			'post_type' => 'slide_in',
		));
		return $query->posts ? $query->posts[0] : false;
	}
}
