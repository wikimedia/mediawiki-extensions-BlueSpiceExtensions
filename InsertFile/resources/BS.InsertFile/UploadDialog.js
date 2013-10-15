Ext.define( 'BS.InsertFile.UploadDialog', {
	extend: 'BS.Window',
	requires:[
		'Ext.Button'
	],
	minHeight:null,
	padding: 0,
	layout: 'fit',

	afterInitComponent: function() {
		this.upMain = Ext.create( 'BS.InsertFile.UploadPanel', {
			id: this.getId()+'-upload-panel',
			allowedFileExtensions: this.allowedFileExtensions
		});
		this.upMain.on( 'uploadcomplete', this.onUpMainUploadComplete, this );
		
		this.items = [
			this.upMain
		];

		this.callParent(arguments);
	},
	
	onBtnOKClick: function() {
		this.upMain.uploadFile();
	},
	
	onUpMainUploadComplete: function( panel, upload ) {
		this.fireEvent( 'ok', this, upload );
		this.close();
	},
	
	resetData: function() {
		this.upMain.getForm().reset();

		this.callParent(arguments);
	}
});