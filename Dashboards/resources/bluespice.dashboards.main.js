$(function(){
	if ( mw.user.options.get( 'MW::Dashboards::UserDashboardOnLogo', false ) == true ) {

		var $logoAnchor = $('#p-logo a').first(); //MediaWiki Skin
		if( $logoAnchor.length == 0 ) {
			$logoAnchor = $('#bs-logo a').first(); //Maybe BlueSpice Skin
		}
		if( $logoAnchor.length == 0 ) { //Okay, now we're desperate
			$logoAnchor = $('a[title="'+mw.message('tooltip-p-logo').plain()+'"]');
		}

		$logoAnchor.attr(
			'href',
			mw.util.getUrl( 'Special:UserDashboard' )
		);
	}
});
