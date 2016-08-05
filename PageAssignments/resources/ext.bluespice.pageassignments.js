Ext.onReady( function() {
	Ext.Loader.setPath( 'BS.PageAssignments', mw.config.get('wgScriptPath') + '/extensions/BlueSpiceExtensions/PageAssignments/resources/BS.PageAssignments' );
});

(function( mw, $, d, bs, undefined ){
	$(d).on( 'click', '#ca-pageassignments a', function( e ) {
		e.preventDefault();

		var curPageId = mw.config.get( 'wgArticleId' );
		var me = this;

		var api = new mw.Api();
		api.postWithToken( 'edit', {
			'action': 'bs-pageassignment-tasks',
			'formatversion': 2,
			'task': 'getForPage',
			'taskData': JSON.stringify( {
				pageId: curPageId
			} )
		}).done(function( response, xhr ){
			if( response.success ) {
				var dlg = Ext.create( 'BS.PageAssignments.dialog.PageAssignment');
				dlg.setData({
					pageId: curPageId,
					pageAssignments: response.payload
				});
				dlg.show( me );
			}
		});


		return false;
	} );
})( mediaWiki, jQuery, document, blueSpice );