(function ($) {

function toggle_pool_conditions () {
	var $check = $("#wdsi-not_in_the_pool");
	var $target = $("#wdsi-conditions-container");
	if (!$check.is(":checked")) $target.show();
	else $target.hide();
}

function toggle_show_after_overrides () {
	var $check = $("#wdsi-override_show_if");
	var $target = $("#wdsi-show_after_overrides-container");
	if ($check.is(":checked")) $target.show();
	else $target.hide();

}

$(function () {
	$("#wdsi-not_in_the_pool").on("change", toggle_pool_conditions);
	toggle_pool_conditions();

	$("#wdsi-override_show_if").on("change", toggle_show_after_overrides);
	$('[name="wdsi[show_after-condition]"]').on("change", function () {
		$('[name="wdsi[show_after-rule]"]').attr("disabled", true);
		$(this).parent("div").find('[name="wdsi[show_after-rule]"]').attr("disabled", false);
	});
	toggle_show_after_overrides();

	// Add fieldset clearing links
	$("#wdsi-conditions-container fieldset").each(function () {
		$(this)
			.append('<a href="#clear-set" class="wdsi-clear_set">' + l10nWdsi.clear_set + '</a>')
			.find("a.wdsi-clear_set").on("click", function () {
				$(this).parents("fieldset").first().find(":radio").attr("checked", false);
				return false;
			});
		;
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