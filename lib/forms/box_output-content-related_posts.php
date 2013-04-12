<?php
$post_id = is_singular() ? get_the_ID() : false;
$posts = wdsi_get_related_posts($post_id, $related_taxonomy, $related_posts_count);
$show = apply_filters('wdsi-services-related_posts', !empty($posts), $post_id);
if ($show) {
	$out = '';
	foreach ($posts as $related) {
		$image = $related_has_thumbnails
			? wdsi_get_related_post_thumbnail($related->ID)
			: false
		;
		$out .= '<div class="wdsi-slide-col ' . ($related_has_thumbnails ? 'wdsi-slide-col-thumb' : '') . '">' .
			($related_has_thumbnails ? '<img class="wdsi-slide-thumb" src="' . $image . '" />' : '') .
			'<h2><a href="' . get_permalink($related->ID) . '">' . $related->post_title . '</a></h2>' .
			'<p>' . wdsi_get_related_post_excerpt($related) . '</p>' .
		'</div>';
	}
	echo '<div class="wdsi-slide-columns">' . $out . '</div>';
}