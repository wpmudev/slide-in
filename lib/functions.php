<?php

/**
 * Attempt to find featured image.
 * If this fails, return filter hook output.
 */
function wdsi_get_image ($post_id=false, $size='medium') {
	// If we don't have post id, no reason to even try.
	$post_id = (int)$post_id;
	if (!$post_id) return apply_filters(
		'wdsi-media-image', '', $size
	);
	
	// Try to find featured image
	$thumb_id = function_exists('get_post_thumbnail_id') ? get_post_thumbnail_id($post_id) : false;
	if ($thumb_id) {
		$image = wp_get_attachment_image_src($thumb_id, $size);
		if ($image) return apply_filters(
			'wdsi-media-image',
			apply_filters('wdsi-media-image-featured_image', $image[0], $size), $size
		);
	}
	
	// Aw shucks, we're still here.
	return apply_filters(
		'wdsi-media-image', '', $size
	);
}

/**
 * Attempt to create link description.
 */
function wdsi_get_description ($post_id=false) {
	// If we don't have post id, no reason to even try.
	$post_id = (int)$post_id;
	if (!$post_id) return apply_filters(
		'wdsi-media-title', get_bloginfo('name')
	);
	
	return apply_filters(
		'wdsi-media-title', 
		apply_filters('wdsi-media-title-post_title', get_the_title($post_id))
	);
}

/**
 * Attempt to get fully qualified URL.
 */
function wdsi_get_url ($post_id=false) {
	$url = (@$_SERVER["HTTPS"] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	// If we don't have post id, no reason to even try.
	$post_id = (int)$post_id;
	if (!$post_id) return apply_filters(
		'wdsi-media-url', $url
	);
	
	return apply_filters(
		'wdsi-media-url',
		apply_filters('wdsi-media-url-post_url', get_permalink($post_id))
	);
}


/**
 * Attempt to find related posts (by tags)
 */
function wdsi_get_related_posts ($post_id) {
	$post_id = (int)$post_id;
	if (!$post_id) return apply_filters(
		'wdsi-media-related_posts', array()
	);
	
	$raw_tags = wp_get_post_tags($post_id, array('fields' => 'ids'));
	if (!$raw_tags) return apply_filters(
		'wdsi-media-related_posts', array()
	);
	
	$query = new WP_Query(array(
		'post__not_in' => array($post_id),
		'tag__in' => $raw_tags,
		'posts_per_page' => 3, // @TODO: expose in settings
	));
	return apply_filters(
		'wdsi-media-related_posts', 
		apply_filters('wdsi-media-related_posts-posts', $query->posts)
	);
}

/**
 * General purpose shorthand getter.
 */
function wdsi_getval ($from, $what, $default=false) {
	if (is_object($from) && isset($from->$what)) return $from->$what;
	else if (is_array($from) && isset($from[$what])) return $from[$what];
	else return $default;
}