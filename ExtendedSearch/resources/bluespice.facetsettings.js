$(document).on( 'click', '.bs-es-facetsettings', function( e ) {
	e.preventDefault();
	var me = this;

	mw.loader.using( 'ext.bluespice.extjs', function() {
		me.settingsFlyout = me.settingsFlyout || Ext.create('BS.ExtendedSearch.tip.FacetSettings', {
			target: me,
			listeners: {
				'hide' : function() {
					//Disable ExtJS tooltip functionality to show up on mouse over
					//We want it only to show on click
					me.settingsFlyout.destroy();
					me.settingsFlyout = null;
				},
				'settingschange': function() {
					var fsets = {};
					$( '.bs-es-facetsettings' ).each( function(index, me){
						var fset = $(me).data( 'fset' );
						var fsetparam = $(me).data( 'fset-param' );
						fsets[fsetparam] = fset;
					});

					/* Unfortunately neither 'ExtendedSearchAjaxManager' in
					 * general nor 'changeRequestFacets' provide a way to
					 * _replace_ a url fragment part. Therefore we just clean
					 * it up manually */
					document.location.hash = document.location.hash.replace(/(&?)fset=.*?(&|$)/g, '$2');
					ExtendedSearchAjaxManager.changeRequestFacets( 'fset=' + JSON.stringify( fsets ), true );
				}
			}
		});
		me.settingsFlyout.setData( $(me).data( 'fset' ) );
		me.settingsFlyout.show();
	});

	return false;
});
