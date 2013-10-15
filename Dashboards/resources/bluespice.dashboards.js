mw.loader.using('ext.bluespice.dashboards', function(){
	if ( mw.user.options.get( 'MW::Dashboards::UserDashboardOnLogo', false ) == true ) {
		//MediaWiki Skin
		var logoAnchor = $('#p-logo a').first();

		if( logoAnchor.length == 0 ) {
			//Maybe BlueSpice Skin
			logoAnchor = $('#bs-logo a').first();
		}

		if( logoAnchor.length == 0 ) {
			//Okay, now we're desperate
			logoAnchor = $('a[title="'+mw.message('tooltip-p-logo').plain()+'"]');
		}

		logoAnchor.attr(
			'href',
			mw.util.wikiGetlink('Special:UserDashboard')
		);
	}

	if ( typeof bsPortalConfigLocation !== 'undefined' ) {
		var image = '<img src="'+ wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_add.png" />';
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
});