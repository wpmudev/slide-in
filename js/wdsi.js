(function ($) {
	
function show_message () {
	var $msg = $("#wdsi-slide_in");
	if ($msg.is(":visible")) return false;
	
	$msg.animate({"width": "show"}, 500);
	return false;
}

function hide_message () {
	var $msg = $("#wdsi-slide_in");
	if (!$msg.is(":visible")) return false;
	
	$msg.animate({"width": "hide"}, 500);
	return false;
}

function check_vertical_location () {
	var top = $(window).scrollTop() + ($(window).height()/2);
	var height = $("body").height();
	
	var percent = (top / height) * 100;
	if (percent > _wdsi_data.after_percent) show_message();
	else hide_message();
}

// Init
$(function () {
	$(window).bind('scroll', check_vertical_location);
	$("#wdsi-close_box").bind('click', hide_message);
	check_vertical_location();
});

})(jQuery);
