/*
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

function check_page_scroll_position () {
	var top = $(window).scrollTop() + ($(window).height()/2);
	var height = $("body").height();
	
	var percent = (top / height) * 100;
	if (percent > parseInt(_wdsi_data.show_after.rule)) show_message();
	else hide_message();
}

function check_scroll_past_selector () {
	$element = $("#" + _wdsi_data.show_after.rule);
	if (!$element.length) return false;

	if ($(window).scrollTop() > $element.offset().top) show_message();
	else hide_message();
};

// Init
$(function () {
	switch (_wdsi_data.show_after.condition) {
		case "selector":
			$(window).on("scroll", check_scroll_past_selector);
			check_scroll_past_selector();
			break;
		case "timeout":
			$(window).on("load", function () {
				setTimeout(show_message, (parseInt(_wdsi_data.show_after.rule) * 1000));
			});
			break;
		case "percentage":
		default:
			$(window).on("scroll", check_page_scroll_position);
			check_page_scroll_position();
			break;
	}
	$("#wdsi-close_box").on("click", hide_message);
});

})(jQuery);
*/
jQuery(document).ready(function($){
	
	var slidein_obj = [];
	var legacy = false;
	
	function css_support( property )
	{
		var div = document.createElement('div');
		var reg = new RegExp("(khtml|moz|ms|webkit|)"+property, "i");
		for ( s in div.style ) {
			if ( s.match(reg) )
				return true;
		}
		return false;
	}
	
	function calculate_vertical_side( obj ){
		var h = $(obj).innerHeight();
		if ( $(obj).hasClass('slidein-top') )
			$(obj).css('top', h*-1).data('slidein-pos', h*-1);
		else if ( $(obj).hasClass('slidein-bottom') )
			$(obj).css('bottom', h*-1).data('slidein-pos', h*-1);
	}
	
	function calculate_horizontal_side( obj ){
		var h = $(obj).innerHeight();
		$(obj).css('margin-top', Math.floor(h/2)*-1);
		var w = $(obj).innerWidth();
		if ( $(obj).hasClass('slidein-right') )
			$(obj).css('right', w*-1).data('slidein-pos', w*-1);
		else if ( $(obj).hasClass('slidein-left') )
			$(obj).css('left', w*-1).data('slidein-pos', w*-1);
	}
	
	function slidein_scroll(){
		if ( slidein_obj.length == 0 )
			return; // No slide in element exists
		var current_pos = $(window).scrollTop();
		var height = $(document).height()-$(window).height();
		var percentage = current_pos/height*100;
		for ( i = 0; i < slidein_obj.length; i++ ) {
			var obj = slidein_obj[i];
			var start = $(obj).data('slidein-start');
			var end = $(obj).data('slidein-end');
			var len = $(obj).data('slidein-for');
			var start_after = $(obj).data('slidein-after');
			var timeout = $(obj).data('slidein-timeout');
			if ( ! start )
				continue;
			var start_pos = 0;
			var end_pos = height;
			var start_at = 0;
			var end_at = 0;
			// Get start position
			if ( start.match(/^\d+%$/) )
				start_pos = Math.round( parseInt(start.replace(/%$/, ''))/100*height );
			else if ( $(start).length > 0 )
				start_pos = $(start).offset().top-$(window).height();
			start_pos = ( start_pos < 0 ) ? 0 : start_pos;
			// Get end position
			if ( end ){
				if ( end.match(/^\d+%$/) )
					end_pos = Math.round( parseInt(end.replace(/%$/, ''))/100*height );
				else if ( $(end).length > 0 )
					end_pos = $(end).offset().top+$(end).height();
			}
			else if ( len && len.match(/^\d+%$/) ){
				end_pos = Math.round( parseInt(len.replace(/%$/, ''))/100*height + start_pos );
			}
			// Get start time
			if ( start_after ){
				if ( start_after.match(/^\d+s$/) )
					start_at = Math.round( parseInt(start_after.replace(/s$/, '')) );
				else if ( start_after.match(/^\d+m$/) )
					start_at = Math.round( parseInt(start_after.replace(/m$/, ''))*60 );
				else if ( start_after.match(/^\d+h$/) )
					start_at = Math.round( parseInt(start_after.replace(/h$/, ''))*3600 );
			}
			// Get end time
			if ( timeout ){
				if ( timeout.match(/^\d+s$/) )
					end_at = Math.round( parseInt(timeout.replace(/s$/, '')) );
				else if ( timeout.match(/^\d+m$/) )
					end_at = Math.round( parseInt(timeout.replace(/m$/, ''))*60 );
				else if ( timeout.match(/^\d+h$/) )
					end_at = Math.round( parseInt(timeout.replace(/h$/, ''))*3600 );
				end_at += start_at;
			}
			//console.log('current_pos: '+current_pos+', height: '+height+', start: '+start+', start_pos: '+start_pos+', for: '+len+', end: '+end+', end_pos:'+end_pos+', start_at: '+start_at+', end_at: '+end_at);
			if ( $(obj).hasClass('slidein-active') ){
				// Check if the end position is reached
				if ( current_pos > end_pos || current_pos < start_pos )
					slidein_hide(obj);
			}
			else {
				// Check if it is on position to show
				if ( current_pos >= start_pos && current_pos <= end_pos ){
					slidein_show(obj, start_at);
					if ( end_at > start_at )
						slidein_hide(obj, end_at);
				}
			}
		}
	}
	
	function slidein_hide(obj, timeout, closed) {
		if ( ! timeout )
			timeout = 0;
		if ( closed )
			$(obj).data('slidein-closed', '1');
		clearTimeout($(obj).data('slidein-temp-time-hide'));
		$(obj).data( 'slidein-temp-time-hide', setTimeout(function(){
			if ( legacy && $(obj).data('slidein-running') != '2' ){
				$(obj).data('slidein-running', '2');
				$(obj).removeClass('slidein-active');
				if ( $(obj).hasClass('slidein-top') )
					$(obj).stop(true).animate({top: $(obj).data('slidein-pos')}, 1000, legacy_hide_after);
				else if ( $(obj).hasClass('slidein-left') )
					$(obj).stop(true).animate({left: $(obj).data('slidein-pos')}, 1000, legacy_hide_after);
				else if ( $(obj).hasClass('slidein-right') )
					$(obj).stop(true).animate({right: $(obj).data('slidein-pos')}, 1000, legacy_hide_after);
				else if ( $(obj).hasClass('slidein-bottom') )
					$(obj).stop(true).animate({bottom: $(obj).data('slidein-pos')}, 1000, legacy_hide_after);
			}
			else {
				$(obj).removeClass('slidein-active');
			}
		}, timeout*1000) );
	}
	
	function slidein_show(obj, timeout) {
		if ( $(obj).data('slidein-closed') == '1' )
			return;
		if ( ! timeout )
			timeout = 0;
		clearTimeout($(obj).data('slidein-temp-time-show'));
		$(obj).data( 'slidein-temp-time-show', setTimeout(function(){
			if ( legacy && $(obj).data('slidein-running') != '1' ){
				$(obj).data('slidein-running', '1');
				$(obj).css('visibility', 'visible');
				if ( $(obj).hasClass('slidein-top') )
					$(obj).stop(true).animate({top: 0}, 1000, legacy_show_after);
				else if ( $(obj).hasClass('slidein-left') )
					$(obj).stop(true).animate({left: 0}, 1000, legacy_show_after);
				else if ( $(obj).hasClass('slidein-right') )
					$(obj).stop(true).animate({right: 0}, 1000, legacy_show_after);
				else if ( $(obj).hasClass('slidein-bottom') )
					$(obj).stop(true).animate({bottom: 0}, 1000, legacy_show_after);
			}
			else {
				$(obj).addClass('slidein-active');
			}
		}, timeout*1000) );
	}
	
	function legacy_hide_after() {
		$(this).css('visibility', 'hidden');
		$(this).data('slidein-running', '0');
	}
	
	function legacy_show_after() {
		$(this).addClass('slidein-active');
		$(this).data('slidein-running', '0');
	}

	$(window).load(function(){
		// Initiate
		$('.slidein').each(function(){
			if ( $(this).is('.slidein-top, .slidein-bottom') ) {
				calculate_vertical_side(this);
			}
			if ( $(this).is('.slidein-right, .slidein-left') ) {
				calculate_horizontal_side(this);
			}
			$(this).data('slidein-running', '0');
			slidein_obj.push(this);
		});
		if ( ! css_support('transition') )
			legacy = true;
		$(window).scroll(slidein_scroll);
		// Call the slidein_scroll first here, so we don't need to wait for scroll event before it show the slide in :))
		slidein_scroll();
	});
	
	$('.slidein').on('click', '.slidein-close a', function(e){
		e.preventDefault();
		var obj = $(this).closest('.slidein');
		slidein_hide(obj, 0, true);
	});
	
});
