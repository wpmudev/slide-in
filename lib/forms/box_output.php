<div id="wdsi-slide_in" style="display:none;" class="wdsi-slide <?php echo $full_width; ?> wdsi-slide-<?php echo $position; ?> wdsi-slide-<?php echo $theme; ?> wdsi-slide-<?php echo $theme; ?>-<?php echo $variation; ?> wdsi-slide-<?php echo $theme; ?>-<?php echo $scheme; ?>" data-slidein-start="<?php echo $selector ? $selector : $percentage; ?>"  data-slidein-end="100%" data-slidein-after="<?php echo $timeout; ?>" data-slidein-timeout="<?php echo $expire_timeout; ?>" data-slidein-id="<?php echo $message->ID; ?>" >

	<div class="wdsi-slide-wrap" <?php echo $width; ?> >
		<?php if ("rounded" != $theme) include dirname(__FILE__) . '/box_output-services.php'; ?>
		<div class="wdsi-slide-content">
			<h1 class="wdsi-slide-title wdsi-slide-bold wdsi-slide-italic"><?php echo apply_filters('wdsi_title', $message->post_title);?></h1>
			<?php 
			if ('related' == $content_type) {
				include dirname(__FILE__) . '/box_output-content-related_posts.php';
			} else if ('mailchimp' == $content_type) {
				include dirname(__FILE__) . '/box_output-content-mailchimp.php';
			} else if ('widgets' == $content_type) {
				include dirname(__FILE__) . '/box_output-content-widgets.php';
			} else {
				echo apply_filters('wdsi_content', $message->post_content);
			}
		?>
		</div>
		<?php if ("rounded" == $theme) include dirname(__FILE__) . '/box_output-services.php'; ?>
	</div>
</div>
