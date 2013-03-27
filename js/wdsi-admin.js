(function ($) {

function toggle_pool_conditions () {
	var $check = $("#wdsi-not_in_the_pool"),
		$target = $("#wdsi-conditions-container")
	;
	if (!$check.is(":checked")) $target.show();
	else $target.hide();
}

function toggle_show_after_overrides () {
	var $check = $("#wdsi-override_show_if"),
		$target = $("#wdsi-show_after_overrides-container")
	;
	if ($check.is(":checked")) $target.show();
	else $target.hide();
}

function toggle_content_types () {
	var $check = $(':radio[name="wdsi-type[content_type]"]');
	if (!$check.length) return false;

	var selected_raw = $check.filter(":checked").val(),
		selected = selected_raw || 'text',
		$item = $("#wdsi-content_type-options-" + selected),
		$editor = $(".postarea")
	;
	if (!$item.length) return false;

	$('.wdsi-content_type').hide();
	$item.show();

	if ('related' == selected || 'widgets' == selected) $editor.hide();
	else $editor.show();
}

function toggle_reshow_conditions () {
	var $check = $(':radio[name="wdsi[on_hide]"]');
	if (!$check.length) return false;

	var selected = $check.filter(":checked").val(),
		reshow = !!selected,
		$item = $(".wdsi-reshow_after")
	;
	if (reshow) $item.show('medium');
	else $item.hide('medium');
	return false;
}

function init_services () {
	/* ----- Sortables ----- */
	var $lis = $("#wdsi-services li"),
		$old = $("#wdsi-services").replaceWith("<ul id='wdsi-services' class='wdsi-services-service_hub' /><ul id='wdsi-disabled_services' class='wdsi-services-service_hub' />"),
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
		//$enabled.sortable("destroy").sortable({});
		if ($enabled.is(".ui-sortable")) $enabled.sortable("destroy");
		$enabled.sortable({});
	}
	init_sortables();

	$(".wdsi_remove_service").click(function() {
		$(this).parents('li.wdsi-service-item').remove();
		return false;
	});
}

$(function () {

	init_services();

	$(':radio[name="wdsi-type[content_type]"]').on("change", toggle_content_types);
	toggle_content_types();

	$("#wdsi-not_in_the_pool").on("change", toggle_pool_conditions);
	toggle_pool_conditions();

	$("#wdsi-override_show_if").on("change", toggle_show_after_overrides);
	$('[name="wdsi[show_after-condition]"]').on("change", function () {
		$('[name="wdsi[show_after-rule]"]').attr("disabled", true);
		$(this).parent("div").find('[name="wdsi[show_after-rule]"]').attr("disabled", false);
	});
	toggle_show_after_overrides();

	$(':radio[name="wdsi[on_hide]"]').on("change", toggle_reshow_conditions);
	toggle_reshow_conditions();

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