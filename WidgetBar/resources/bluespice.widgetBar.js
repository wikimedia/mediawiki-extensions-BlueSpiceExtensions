mw.loader.using('ext.bluespice',function(){
	$(document).on('click', '#bs-widget-tab', function(){
		if ( $(document).triggerHandler('BSWidgetBarToggleList') !== false ) {
			$('#bs-flyout').toggleClass('hidden');
			$('#bs-widget-tab').toggleClass('hidden');
		}

		if ( $.cookie( 'bs-widget-container' ) == 'true' ) {
			$.cookie( 'bs-widget-container', 'null', {
				path: '/'
			} );
		} else {
			$.cookie( 'bs-widget-container', 'true', {
				path: '/',
				expires: 10
			} );
		}
	});

	$(document).on( 'click', '.bs-widget .bs-widget-head', function(){
		var oWidgetBody = $(this).parent().find('.bs-widget-body');
		var sCookieKey = $(this).parent().attr('id')+'-viewstate';
		if ( oWidgetBody.is( ":visible" ) == true ) {
			oWidgetBody.slideUp(500);
			$(this).parent().addClass('bs-widget-viewstate-collapsed');
			$.cookie(sCookieKey, 'collapsed', {
				path: '/',
				expires: 10
			});
		} else {
			oWidgetBody.slideDown(500);
			$(this).parent().removeClass('bs-widget-viewstate-collapsed');
			$.cookie(sCookieKey, null, {
				path: '/'
			});
		}
	}).each( function() {
		var oWidgetBody = $(this).parent().find('.bs-widget-body');
		var sCookieKey = $(this).parent().attr('id')+'-viewstate';
		if ( $.cookie( sCookieKey ) == 'collapsed' ) {
			oWidgetBody.hide();
			$(this).parent().addClass('bs-widget-viewstate-collapsed');
		}
	});
});

/**
 * #bs-widgetbar-edit => ANCHOR in normal view mode
 * #bs-widgetbar-settings => DIV on user's user page
 * #mw-htmlform-WidgetBar => TABLE ROW on Special:Preferences
 */
//Restored old functionality
/*$(document).on( 'click', '#bs-widgetbar-edit, #bs-widgetbar-settings, #mw-htmlform-WidgetBar', function(e) {

	Ext.require( 'BS.WidgetBar.dialog.Edit', function() {
		var dlg = new BS.WidgetBar.dialog.Edit();
		var animateFrom = null;
		if( $(this).attr('id') === 'bs-widgetbar-edit' ) {
			animateFrom = this;
		}
		dlg.show(animateFrom);
	}, this );

	e.preventDefault();
	return false;
});*/