/**
 * #bs-usersidebar-edit => ANCHOR in normal view mode
 * #bs-usersidebar-settings => DIV on user's user page
 * #mw-htmlform-UserSidebar => TABLE ROW on Special:Preferences
 */
$(document).on( 'click', '#bs-usersidebar-edit, #bs-usersidebar-settings, #mw-htmlform-UserSidebar', function(e) {

	Ext.require( 'BS.WidgetBar.dialog.Edit', function() {
		var dlg = new BS.WidgetBar.dialog.Edit({
			bsAvailableWidgetsUrl: '',
			bsCurrentWidgetsUrl: ''
		});
		var animateFrom = null;
		if( $(this).attr('id') === 'bs-usersidebar-edit' ) {
			animateFrom = this;
		}
		dlg.show(animateFrom);
	}, this );

	e.preventDefault();
	return false;
});