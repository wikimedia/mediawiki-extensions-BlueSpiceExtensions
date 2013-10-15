
$(document).ready(function(){
	
	var iMainContent =  $("#content").offset.left + $("#content").outerWidth(true) - $("#bs-widget-tab").outerWidth(true);

	if ($.cookie( 'bs-widget-container' ) == 'true') {
		$('#bs-floater-right').addClass('in');
		var iDefaultLeft = iMainContent - $("#bs-flyout").width();
		iDefaultLeft = $('#bs-floater-right').hasClass('left_flyout') ? iDefaultLeft : iDefaultLeft + $("#bs-flyout").width();
		$('#bs-floater-right').css('left', iDefaultLeft);
		$('#bs-floater-right').css('width', $("#bs-flyout").width() + $("#bs-widget-tab").width());
	} else{
		$('#bs-floater-right').css('left', iMainContent);
		$('#bs-floater-right').css('width', $("#bs-widget-tab").width());
	}

	$('#bs-widget-tab').click(function(){
		var classRemove = 'out';
		var classAdd = 'in';
		var iLeft = $("#content").offset().left + $("#content").outerWidth(true) - $("#bs-widget-tab").outerWidth(true);

		var iFlyoutWidth = $("#bs-flyout").width();
		
		if( $('#bs-floater-right').hasClass('in') ) {
			classRemove = 'in';
			classAdd = 'out';
			var iNewLeft = $('#bs-floater-right').hasClass('left_flyout') ? iLeft : iLeft;
			var iNewWidth = $("#bs-widget-tab").width();
		} else {
			$('#bs-floater-right').addClass( 'out' );
			var iNewLeft = $('#bs-floater-right').hasClass('left_flyout') ? iLeft - iFlyoutWidth : iLeft;
			var iNewWidth = $("#bs-flyout").width() + $("#bs-widget-tab").width();
		}
		$('#bs-floater-right').stop(true,true).removeClass(classRemove).addClass(classAdd);
		$('#bs-floater-right').stop(true,true).animate( {width : iNewWidth, left: iNewLeft}, "slow");
		
		if ($.cookie( 'bs-widget-container' ) == 'true') {
		$.cookie( 'bs-widget-container', 'null', {
			path: '/'
		} );
		}
		else {
		$.cookie( 'bs-widget-container', 'true', {
			path: '/', 
			expires: 10
		} );
		}
	});

	$('.bs-widget .bs-widget-head').click( function(){
		var oWidgetBody = $(this).parent().find('.bs-widget-body');
		var sCookieKey  = $(this).parent().attr('id')+'-viewstate';
		if( oWidgetBody.is(":visible") == true ) {
			oWidgetBody.slideUp(500);
			$(this).parent().addClass('bs-widget-viewstate-collapsed');
			$.cookie(sCookieKey, 'collapsed', {
				path: '/',
				expires: 10
			});
		}
		else {
			oWidgetBody.slideDown(500);
			$(this).parent().removeClass('bs-widget-viewstate-collapsed');
			$.cookie(sCookieKey, null, {
				path: '/'
			});
		}
	}).each( function(){
		var oWidgetBody = $(this).parent().find('.bs-widget-body');
		var sCookieKey = $(this).parent().attr('id')+'-viewstate';
		if( $.cookie( sCookieKey ) == 'collapsed') {
			oWidgetBody.hide();
			$(this).parent().addClass('bs-widget-viewstate-collapsed');
		}
	});
});

$(window).resize(function() {
	var iLeft =  $("#content").offset().left + $("#content").outerWidth(true) - $("#bs-widget-tab").outerWidth(true);
	var iFlyoutWidth = $("#bs-flyout").width();
		
	if( $('#bs-floater-right').hasClass('in') ) {
			var iNewLeft = $('#bs-floater-right').hasClass('left_flyout') ? iLeft - iFlyoutWidth : iLeft;
			var iNewWidth = $("#bs-flyout").width() + $("#bs-widget-tab").width();			
		} else {
			var iNewLeft = $('#bs-floater-right').hasClass('left_flyout') ? iLeft : iLeft;
			var iNewWidth = $("#bs-widget-tab").width();
		}
	$('#bs-floater-right').stop(true,true).css( {width : iNewWidth, left: iNewLeft} );
});