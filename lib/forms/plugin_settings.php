<div class="wrap">
	<div class="icon32 icon32-settings-slide_in" id="icon-settings"><br></div>
	<h2><?php echo __('Settings', 'wdsi');?></h2>

	<form action="" method="post" class="wpmudev-ui">

		<?php settings_fields('wdsi_options_page'); ?>
		<?php do_settings_sections('wdsi_options_page'); ?>
		<p class="submit">
			<button name="Submit" type="submit" class="save"><?php esc_attr_e('Save Changes'); ?></button>
		</p>
	</form>

</div>

<script type="text/javascript">
(function ($) {
$(function () {

/* ----- Sortables ----- */
var $lis = $("#wdsi-services li"),
	$old = $("#wdsi-services").replaceWith("<ul id='wdsi-services' class='wdsi-services-service_hub' /><ul id='wdsi-disabled_services' class='wdsi-services-service_hub' />")
	$enabled = $("#wdsi-services"),
	$disabled = $("#wdsi-disabled_services")
;
function init_sortables () {
	$(".wdsi-services-service_hub").empty();
	$lis.each(function () {
		var $me = $(this),
			$hub = $me.is(".wdsi-disabled") ? $disabled : $enabled
		;
		$hub.append($me);
		$me.find('input[name*="services"]').off("change").on("change", function () {
			var $in = $(this);
			if ($in.is(":checked")) $me.removeClass("wdsi-disabled");
			else $me.addClass("wdsi-disabled");
			init_sortables();
		});
	});
	$enabled.sortable("destroy").sortable({});
}
init_sortables();


$(".wdsi_remove_service").click(function() {
	$(this).parents('li.wdsi-service-item').remove();
	return false;
});

/* ----- Toggleables ----- */
$('[name="wdsi[show_after-condition]"]').on("change", function () {
	$('[name="wdsi[show_after-rule]"]').attr("disabled", true);
	$(this).parent("div").find('[name="wdsi[show_after-rule]"]').attr("disabled", false);
});

// Width toggling
	$("#wdsi-full_width").on("change", function () {
		if (!$("#wdsi-full_width").is(":checked")) {
			$("#wdsi-custom_width").show().find("input").attr("disabled", false);
			$('label[for="wdsi-full_width"]').addClass("wdsi-not_applicable");
		} else {
			$("#wdsi-custom_width").hide().find("input").attr("disabled", true);
			$('label[for="wdsi-full_width"]').removeClass("wdsi-not_applicable");
		}
	});


});
})(jQuery);
</script>