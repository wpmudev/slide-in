<div class="wrap">
	<div class="icon32 icon32-settings-slide_in" id="icon-settings"><br></div>
	<h2><?php echo __('Settings', 'wdsi');?></h2>

	<form action="" method="post">

	<?php settings_fields('wdsi_options_page'); ?>
	<?php do_settings_sections('wdsi_options_page'); ?>
	<p class="submit">
		<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
	</p>
	</form>

</div>

<script type="text/javascript">
(function ($) {
$(function () {

$("#wdsi-services").sortable({
	"items": "li:not(.wdsi-disabled)"
});
$('.wdsi-service-item input[name*="services"]').change(function () {
	var $me = $(this);
	var $parent = $me.parents('.wdsi-service-item');
	if ($me.is(":checked")) $parent.removeClass("wdsi-disabled");
	else if (!$me.is(":checked") && !$parent.is(".wdsi-disabled")) $parent.addClass("wdsi-disabled");
	$("#wdsi-services").sortable("destroy").sortable({
		"items": "li:not(.wdsi-disabled)"
	});
});

$(".wdsi_remove_service").click(function() {
	$(this).parents('li.wdsi-service-item').remove();
	return false;
});

/* ----- Toggleables ----- */
$('[name="wdsi[show_after-condition]"]').on("change", function () {
	$('[name="wdsi[show_after-rule]"]').attr("disabled", true);
	$(this).parent("div").find('[name="wdsi[show_after-rule]"]').attr("disabled", false);
});

$("#wdsi-full_width").on("change", function () {
	if (!$("#wdsi-full_width").is(":checked")) $("#wdsi-custom_width").show().find("input").attr("disabled", false);
	else $("#wdsi-custom_width").hide().find("input").attr("disabled", true);
});

});
})(jQuery);
</script>