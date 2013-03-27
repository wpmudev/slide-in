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
							$count = in_array('google', $no_count)
								? 'annotation="none"'
								: 'annotation="bubble"'
							;
							echo '<g:plusone size="medium" ' . $count . '></g:plusone>';
							break;
						case "facebook":
							echo '<iframe src="http://www.facebook.com/plugins/like.php?href=' .
								rawurlencode($url) .
								'&amp;send=false&amp;layout=button_count&amp;width=120&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=20" ' .
								'scrolling="no" frameborder="0" style="border:none; width:120px; height:20px;" allowTransparency="true"></iframe>';
							break;
						case "twitter":
							$count = in_array('twitter', $no_count)
								? 'none'
								: 'horizontal'
							;
							if (!in_array('twitter', $skip_script)) echo '<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
							echo '<a href="http://twitter.com/share" class="twitter-share-button" data-count="' . $count . '">Tweet</a>';
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
							if (!in_array('linkedin', $skip_script)) echo '<script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script>';
							$count = in_array('pinterest', $no_count)
								? 'none'
								: 'beside'
							;
							echo '<a data-pin-config="' . $count . '" href="//pinterest.com/pin/create/button/" data-pin-do="buttonBookmark" ><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a>';
							break;
					}
				}
				?>
			</div>
		<?php } ?>
	</div>
	<div class="wdsi-slide-close"><a href="#"><?php _e('Close', 'wdsi'); ?></a></div>
</div>