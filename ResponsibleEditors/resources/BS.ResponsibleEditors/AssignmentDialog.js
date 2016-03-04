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
		bs.api.tasks.exec(
			'responsibleeditors',
			'setResponsibleEditors',
			me.getData()
		).done( function() {
			me.fireEvent( 'ok', this, me.getData() );
			me.hideLoadMask();
			me.hide();
		});
	}
});