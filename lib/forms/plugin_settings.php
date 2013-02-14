<div class="wrap">
	<div class="icon32 icon32-settings-slide_in" id="icon-settings"><br></div>
	<h2><?php echo __('Global Settings', 'wdsi');?></h2>

	<form action="" method="post" class="wpmudev-ui">

		<?php settings_fields('wdsi_options_page'); ?>
		<?php do_settings_sections('wdsi_options_page'); ?>
		<p class="submit">
			<button name="Submit" type="submit" class="save"><?php esc_attr_e('Save Changes'); ?></button>
		</p>
	</form>

</div>