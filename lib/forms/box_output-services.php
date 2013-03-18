<?php
	global $wp;
	$url = (is_home() || is_front_page()) ? site_url() : get_permalink();
	$url = apply_filters('wdsi-url-current_url', ($url ? $url : site_url($wp->request))); // Fix for empty URLs
?>
<div class="wdsi-slide-control">
	<div class="wdsi-slide-share wdsi-clearfix">
		<?php if ($services) foreach ($services as $key=>$service) { ?>
			<?php $idx = is_array($service) ? strtolower(preg_replace('/[^-a-zA-Z0-9_]/', '', $service['name'])) : $key;?>
			<div class="wdsi-item" id="wdsi-service-<?php echo $idx;?>">
				<?php if (is_array($service)) {
					echo $service['code'];
				} else {
					switch ($key) {
						case "google":
							if (!in_array('google', $skip_script)) echo '<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>';
							echo '<g:plusone size="small"></g:plusone>';
							break;
						case "facebook":
							echo '<iframe src="http://www.facebook.com/plugins/like.php?href=' .
								rawurlencode($url) .
								'&amp;send=false&amp;layout=button_count&amp;width=120&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=20" ' .
								'scrolling="no" frameborder="0" style="border:none; width:120px; height:20px;" allowTransparency="true"></iframe>';
							break;
						case "twitter":
							if (!in_array('twitter', $skip_script)) echo '<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
							echo '<a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal">Tweet</a>';
							break;
						case "stumble_upon":
							echo '<script src="http://www.stumbleupon.com/hostedbadge.php?s=1"></script>';
							break;
						case "delicious":
							echo '<a href="http://www.delicious.com/save" onclick="window.open(' .
								"'http://www.delicious.com/save?v=5&amp;noui&amp;jump=close&amp;url='+encodeURIComponent(location.href)+'&amp;title='+encodeURIComponent(document.title), 'delicious','toolbar=no,width=550,height=550'); return false;".
								'">' .
									'<img src="' . WDSI_PLUGIN_URL . '/img/delicious.24px.gif" alt="Delicious" />' .
								'</a>';
							break;
						case "reddit":
							echo '<script type="text/javascript" src="http://www.reddit.com/static/button/button1.js"></script>';
							break;
						case "linkedin":
							if (!in_array('linkedin', $skip_script)) echo '<script src="http://platform.linkedin.com/in.js" type="text/javascript"></script>';
							echo '<script type="IN/Share" data-counter="right"></script>';
							break;
						case "post_voting":
							if (function_exists('wdpv_get_vote_up_ms') && is_singular()) {
								global $blog_id;
								$post_id = get_the_ID();
								if ($post_id) {
									echo wdpv_get_vote_up_ms(false, $blog_id, $post_id);
									echo wdpv_get_vote_result_ms(true, $blog_id, $post_id);
								}
							}
							break;
						case "pinterest":
							$post_id = is_singular() ? get_the_ID() : false;
							$atts = array();
							
							$url = wdsi_get_url($post_id);
							if ($url) $atts['url'] = 'url=' . rawurlencode($url);
							
							$image = wdsi_get_image($post_id);
							if ($image) $atts['media'] = 'media=' . rawurlencode($image);
							
							$description = rawurlencode(wdsi_get_description($post_id));
							if ($description) $atts['description'] = 'description=' . $description;

							$show = apply_filters('wdsi-services-pinterest', !empty($image), $atts);
							if ($show) {
								$atts = join('&', $atts); 
								echo '<a ' .
									'href="http://pinterest.com/pin/create/button/?' . $atts . '" ' . 
									'class="pin-it-button" count-layout="vertical">Pin It</a>' .
									'<script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script>' .
								'';	
							}
							break;
						/*
						case "related_posts":
							$post_id = is_singular() ? get_the_ID() : false;
							$posts = wdsi_get_related_posts($post_id);
							$show = apply_filters('wdsi-services-related_posts', !empty($posts), $post_id);
							if ($show) {
								$out = '';
								foreach ($posts as $related) {
									$out .= '<li>' .
										'<a href="' . get_permalink($related->ID) . '">' . $related->post_title . '</a>' .
									'</li>';
								}
								echo '<h3>' . __('Related posts', 'wdsi') . '</h3><ul class="related_posts">' . $out . '<ul>';
							}
							break;
						*/
						/*
						case "mailchimp":
							$id = md5(microtime() . rand());
							$admin_url = admin_url('admin-ajax.php');
							echo '<form id="wdsi-mailchimp-' . $id . '" class="wdsi-mailchimp-root">';
							echo '<label for="wdsi-mailchimp-' . $id . '-email">' . __('Email:', 'wdsi') . '</label>';
							echo '<input type="text" id="wdsi-mailchimp-' . $id . '-email" class="wdsi-mailchimp-email" placeholder="' . __('placeholder@test.com', 'wdsi') . '" />';
							echo '<button class="wdsi-mailchimp-subscribe">' . __('Subscribe', 'wdsi') . '</button>';
							echo '<div class="wdsi-mailchimp-result"></div>';
							echo '</form>';
							echo <<<EoMailChimpJs
<script>
(function ($) {

function mailchimp_subscribe (root) {
	var email = root.find(".wdsi-mailchimp-email"),
		result = root.find(".wdsi-mailchimp-result")
	;
	if (!email.val()) return false;
	$.post("{$admin_url}", {
		"action": "wdsi_mailchimp_subscribe",
		"email": email.val()
	}, function (data) {
		result.html(data);
	});
}

$(function () {
$(".wdsi-mailchimp-subscribe").click(function () {
	mailchimp_subscribe($(this).parents(".wdsi-mailchimp-root"));
	return false;
});
});
})(jQuery);
</script>
EoMailChimpJs;
							break;
							*/
					}
				}
				?>
			</div>
		<?php } ?>
	</div>
	<div class="wdsi-slide-close"><a href="#">Close</a></div>
</div>