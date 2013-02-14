<div id="wdsi-slide_in" 
	class="slidein <?php echo $full_width; ?> 
		slidein-<?php echo $position; ?> 
		slidein-<?php echo $theme; ?> slidein-<?php echo $theme; ?>-<?php echo $variation; ?> slidein-<?php echo $theme; ?>-<?php echo $scheme; ?>"
	data-slidein-start="<?php echo $selector ? $selector : $percentage; ?>" 
	data-slidein-end="100%" 
	data-slidein-after="<?php echo $timeout; ?>" 
	data-slidein-timeout="<?php echo $expire_timeout; ?>"
>

	<div class="slidein-wrap" <?php echo $width; ?> >
		<?php if ("rounded" != $theme) include dirname(__FILE__) . '/box_output-services.php'; ?>
		<div class="slidein-content">
			<h1 class="slidein-title slidein-bold slidein-italic"><?php echo $message->post_title;?></h1>
			<?php 
			if ('related' == $content_type) {
				include dirname(__FILE__) . '/box_output-content-related_posts.php';
			} else if ('mailchimp' == $content_type) {
				include dirname(__FILE__) . '/box_output-content-mailchimp.php';
			} else {
				echo $message->post_content;
			}
		?>
		</div>
		<?php if ("rounded" == $theme) include dirname(__FILE__) . '/box_output-services.php'; ?>
	</div>
</div>
