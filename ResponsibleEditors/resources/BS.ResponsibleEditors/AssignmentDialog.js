Ext.define( 'BS.ResponsibleEditors.AssignmentDialog', {
	extend: 'BS.Window',
	id:'bs-resped-assign-dlg',
	singleton: true,

	afterInitComponent: function() {
		this.setTitle( mw.message('bs-responsibleeditors-title').plain() );

		this.pnlMain = Ext.create( 'BS.ResponsibleEditors.AssignmentPanel' );
		this.items = [
			this.pnlMain
		];

		this.callParent();
	},

	getData: function(){
		this.currentData = this.pnlMain.getData();
		return this.callParent();
	},

	setData: function( obj ){
		this.callParent( arguments );
		this.pnlMain.setData( this.currentData );
	},

	onBtnOKClick: function() {
		this.showLoadMask();

		var me = this;
		//copy from bluespice.api.js, cause we need to set "loading" to false
		var cfg = {
			failure: function( response, module, task, $dfd, cfg ) {
				var message = response.message || '';
				if ( response.errors.length > 0 ) {
					for ( var i in response.errors ) {
						if ( typeof( response.errors[i].message ) !== 'string' ) continue;
						message = message + '<br />' + response.errors[i].message;
					}
				}
				bs.util.alert( module + '-' + task + '-fail', {
						titleMsg: 'bs-extjs-title-warning',
						text: message
					}, {
						ok: function() {
							me.hideLoadMask();
					}}
				);
			}
		};
		bs.api.tasks.exec(
			'responsibleeditors',
			'setResponsibleEditors',
			me.getData(),
			cfg
		).done( function() {
			me.fireEvent( 'ok', this, me.getData() );
			me.hideLoadMask();
			me.hide();
		});
	}
});