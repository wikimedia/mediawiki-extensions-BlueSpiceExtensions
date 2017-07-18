//This is just to have singleton access to the BaseDialog
Ext.define( 'BS.InsertFile.FileDialog', {
	extend: 'BS.InsertFile.BaseDialog',

	singleton: true,
	id: 'bs-InsertFile-dlg-window',
	title: mw.message('bs-insertfile-titlefile').plain(),

	storeFileType: 'file',
	allowedFileExtensions: mw.config.get( 'bsFileExtensions' ),

	initComponent: function() {
		//this is neccessary to avoid strange cross-referencing between the
		//two instances of BS.InsertFile.BaseDialog subclasses
		this.configPanel.height = 150;
		this.configPanel.items = [];
		this.callParent(arguments);
	},
	afterInitComponent: function() {
		this.callParent(arguments);
	},
	onPnlConfigExpand: function(panel, eOpts){
		this.callParent(arguments);
	},
	makeRgNsTextItems: function() {
		var items = this.callParent( arguments );
		items[0].checked = false;
		items[0].boxLabel = mw.message('bs-insertfile-nstextfile-file').plain();
		items[1].checked = true;
		return items;
	}
});