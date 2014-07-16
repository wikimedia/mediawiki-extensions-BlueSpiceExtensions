Ext.define( 'BS.Review.Dialog', {
	extend: 'BS.Window',
	id:'bs-review-dlg',
	singleton: true,
	width: 600,
	height: 500,

	afterInitComponent: function() {
		this.setTitle( mw.message('bs-review-review').plain() );
		this.btnOK.setText( mw.message('bs-extjs-save').plain() );
		this.btnCancel.setText( mw.message('bs-extjs-delete').plain() );
		
		this.pnlMain = Ext.create( 'BS.Review.ReviewPanel' );
		this.items = [
			{
				layout: 'fit',
				items: [this.pnlMain]
			}
		];

		this.callParent();
	},
	
	
	getData: function(){
		this.currentData = this.pnlMain.getData();
		return this.callParent();
	},

	setData: function( obj ){
		this.callParent( arguments );
		
		if( this.currentData.userCanEdit == false ) {
			this.btnOK.disable();
			this.btnCancel.disable();
		}
		
		this.pnlMain.setData( this.currentData );
	},
	
	onBtnOKClick: function() {
		this.pnlMain.saveReview();
	},
	
	onBtnCancelClick: function() {
		this.pnlMain.deleteReview();
	}
});