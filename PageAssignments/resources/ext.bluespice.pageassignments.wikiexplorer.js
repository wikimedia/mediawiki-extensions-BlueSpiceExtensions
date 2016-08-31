$(document).on('click', '.bs-pa-wikiexplorer-users, .bs-pa-wikiexplorer-groups', function(e){
	var me = this;
	var data = $(this).closest('ul').data();
	e.preventDefault();

	var api = new mw.Api();
	api.postWithToken( 'edit', {
		'action': 'bs-pageassignment-tasks',
		'formatversion': 2,
		'task': 'getForPage',
		'taskData': JSON.stringify( {
			pageId: data.articleid
		} )
	}).done(function( response, xhr ){
		if( response.success ) {
			var dlg = Ext.create( 'BS.PageAssignments.dialog.PageAssignment' );
			dlg.setData({
				pageId: data.articleid,
				pageAssignments: response.payload
			});
			dlg.show( me );
			dlg.on( 'ok', function( btn, data ){
			//This is just so ugly...
			var superList = Ext.ComponentQuery.query(
				'gridpanel[renderTo=superlist_grid]'
			);
			superList[0].getStore().reload();
		}, me);
		}
	});
	return false;
});