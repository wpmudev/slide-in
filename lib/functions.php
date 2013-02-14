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
function wdsi_get_related_posts ($post_id, $limit=3) {
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
		'posts_per_page' => $limit,
	));
	return apply_filters(
		'wdsi-media-related_posts', 
		apply_filters('wdsi-media-related_posts-posts', $query->posts)
	);
}

/**
 * Fetching related post excerpt without disturbing the loop.
 */
function wdsi_get_related_post_excerpt ($post) {
	if ($post->post_excerpt) return $post->post_excerpt;
	$string = $post->post_content;
	$string = trim(preg_replace('/\r|\n/', ' ', strip_shortcodes(htmlspecialchars(wp_strip_all_tags(strip_shortcodes($string)), ENT_QUOTES))));
	$string = (preg_match('/.{156,}/um', $string))
		? preg_replace('/(.{0,152}).*/um', '$1', $string) . '...'
		: $string
	;
	return $string . '&nbsp;' . '<a href="' . get_permalink($post->ID) . '">' . __('Read more', 'wdsi') . '<a>';
}

function wdsi_get_related_post_thumbnail ($post_id) {
	$thumb_id = function_exists('get_post_thumbnail_id')
		? get_post_thumbnail_id($post_id)
		: false
	;
	$image = $thumb_id
		? wp_get_attachment_image_src($thumb_id, 'thumbnail')
		: false
	;
	return !empty($image[0])
		? $image[0]
		: false
	;
}

/**
 * General purpose shorthand getter.
 */
function wdsi_getval ($from, $what, $default=false) {
	if (is_object($from) && isset($from->$what)) return $from->$what;
	else if (is_array($from) && isset($from[$what])) return $from[$what];
	else return $default;
}