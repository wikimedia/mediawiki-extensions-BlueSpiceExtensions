(function(mw, $, bs, undefined){
	$('#ca-respeditors').find('a').on( 'click', function( e ) {
		e.preventDefault();
		var me = this;
		mw.loader.using('ext.bluespice.extjs').done(function(){
			Ext.require( 'BS.ResponsibleEditors.AssignmentDialog', function(){
				BS.ResponsibleEditors.AssignmentDialog.clearListeners();
				BS.ResponsibleEditors.AssignmentDialog.on( 'ok', function( btn, data ){
					window.location.reload();
				});
				BS.ResponsibleEditors.AssignmentDialog.setData(
					mw.config.get('bsResponsibleEditors')
				);
				BS.ResponsibleEditors.AssignmentDialog.show( me );
			});
		});

		return false;
	});
}( mediaWiki, jQuery, blueSpice ));