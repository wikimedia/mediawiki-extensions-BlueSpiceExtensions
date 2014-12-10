Ext.define( 'BS.ResponsibleEditors.AssignmentPanel', {
	extend: 'BS.Panel',
	layout: 'form',
	modal: true,
	id: 'bs-resped-assignment-panel',

	afterInitComponent: function() {
		this.strAvailableRespEds = Ext.create('Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'SpecialResponsibleEditors::ajaxGetPossibleEditors' ),
				reader: {
					type: 'json',
					root: 'users',
					idProperty: 'user_id'
				}
			},
			fields: [ 'user_id', 'user_displayname' ],
			sorters:['user_displayname'],
			remoteSort: false
		});
		this.strAvailableRespEds.on( 'load', this.onStrAvailableRespEdsLoad, this );

		this.isRespEds = Ext.create( 'Ext.ux.form.ItemSelector', {
			store: this.strAvailableRespEds,
			displayField: 'user_displayname',
			valueField: 'user_id',
			fromTitle: mw.message( 'bs-responsibleeditors-availableeditors' ).plain(),
			toTitle: mw.message( 'bs-responsibleeditors-assignededitors' ).plain(),
			height: 250
		});

		this.items = [
			this.isRespEds
		];

		this.callParent();
	},

	getData: function(){
		this.currentData.editorIds = this.isRespEds.getValue();
		return this.callParent();
	},

	setData: function( obj ){
		this.callParent( arguments );
		if( this.storeLoaded === false ) {
			this.strAvailableRespEds.load({
				params: {
					'rsargs[]': this.currentData.articleId
				}
			});
		}
		//TODO: We need this very often. Maybe add to base class
		if( !this.strAvailableRespEds.isLoading() ) {
			this.isRespEds.setValue(
				this.currentData.editorIds
			);
		}
	},

	storeLoaded: false,
	onStrAvailableRespEdsLoad: function( store, records, successful, eOpts ) {
		this.storeLoaded = true;
		if( this.currentData.editorIds ) {
			this.isRespEds.setValue(
				this.currentData.editorIds
			);
		}
	}
});