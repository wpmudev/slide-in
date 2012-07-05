jQuery(document).ready(function($){
	
	var slidein_obj = [];
	
	function calculate_vertical_side( obj ){
		var h = $(obj).innerHeight();
		if ( $(obj).hasClass('slidein-top') )
			$(obj).css('top', h*-1);
		else if ( $(obj).hasClass('slidein-bottom') )
			$(obj).css('bottom', h*-1);
	}
	
	function calculate_horizontal_side( obj ){
		var h = $(obj).innerHeight();
		$(obj).css('margin-top', Math.floor(h/2)*-1);
		var w = $(obj).innerWidth();
		if ( $(obj).hasClass('slidein-right') )
			$(obj).css('right', w*-1);
		else if ( $(obj).hasClass('slidein-left') )
			$(obj).css('left', w*-1);
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
			if ( ! start )
				continue;
			var start_pos = 0;
			if ( start.match(/^\d+%$/) )
				start_pos = Math.round( parseInt(start.replace(/%$/, ''))/100*height );
			else if ( $(start).length > 0 )
				start_pos = $(start).offset().top-$(window).height();
			start_pos = ( start_pos < 0 ) ? 0 : start_pos;
			var end_pos = height;
			if ( end ){
				if ( end.match(/^\d+%$/) )
					end_pos = Math.round( parseInt(end.replace(/%$/, ''))/100*height );
				else if ( $(end).length > 0 )
					end_pos = $(end).offset().top+$(end).height();
			}
			else if ( len && len.match(/^\d+%$/) ){
				end_pos = Math.round( parseInt(len.replace(/%$/, ''))/100*height + start_pos );
			}
			//console.log('current_pos: '+current_pos+', height: '+height+', start: '+start+', start_pos: '+start_pos+', for: '+len+', end: '+end+', end_pos:'+end_pos);
			if ( $(obj).hasClass('slidein-active') ){
				// Check if the end position is reached
				if ( current_pos > end_pos || current_pos < start_pos )
					$(obj).removeClass('slidein-active');
			}
			else {
				if ( current_pos >= start_pos && current_pos <= end_pos )
					$(obj).addClass('slidein-active');
			}
		}
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
			slidein_obj.push(this);
		});
		$(window).scroll(slidein_scroll);
		// Call the slidein_scroll first here, so we don't need to wait for scroll event before it show the slide in :))
		slidein_scroll();
	});
	
});
