if ( mw.config.get('bsPortalConfigLocation') !== null ) {
	var image = '<img src="' + mw.config.get( "wgScriptPath" ) + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_add.png" />';
	var text = $( 'h1.firstHeading' ).html();
	var anchor = $( 'h1.firstHeading' ).html(
		text + '<a href="#" id="bs-dashboard-add">' + image + '</a>'
	);
	$(anchor).children('#bs-dashboard-add').on( 'click', function() {
		Ext.require( 'BS.Dashboards.PortletCatalog', function() {
			BS.Dashboards.PortletCatalog.show();
		} );
	} );
}