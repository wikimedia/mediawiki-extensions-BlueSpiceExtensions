(function( mw, $, bs, d, undefined ){
	function _renderGrid() {
		Ext.onReady(function(){
			Ext.create( 'BS.PageAssignments.panel.Overview', {
				renderTo: 'bs-pageassignments-overview'
			});
		});
	}

	var deps = mw.config.get('bsPageAssignmentsOverviewDeps');
	if( deps ) {
		mw.loader.using( deps, _renderGrid );
	}
	else {
		_renderGrid();
	}

})( mediaWiki, jQuery, blueSpice, document );