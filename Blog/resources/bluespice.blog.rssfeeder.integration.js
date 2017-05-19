Ext.onReady( function() {
	var button = Ext.get( 'btnFeedNsBlog' );
	var combo = Ext.get( 'selFeedNsBlog' );

	if( button && combo ) {
		button.addListener( 'click', function() {
			location.href = combo.getValue();
		});
	}
});


