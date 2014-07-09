$(document).on('click', '.bs-re-superlist-editor', function(e){
	var me = this;
	var data = $(this).closest('ul').data();
	var data2 = {
		articleId: data.articleid,
		editorIds: data.editorids
	};
	
	Ext.require( 'BS.ResponsibleEditors.AssignmentDialog', function(){
		BS.ResponsibleEditors.AssignmentDialog.clearListeners();
		BS.ResponsibleEditors.AssignmentDialog.on( 'ok', function( btn, data ){
			//This is just so ugly...
			var superList = Ext.ComponentQuery.query('gridpanel[renderTo=superlist_grid]');
			superList[0].getStore().reload();
		}, me);
		BS.ResponsibleEditors.AssignmentDialog.setData( data2 );
		BS.ResponsibleEditors.AssignmentDialog.show( me );
	});
	
	e.preventDefault();
	return false;
});