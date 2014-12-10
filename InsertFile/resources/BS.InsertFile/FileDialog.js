//This is just to have singleton access to the BaseDialog
Ext.define( 'BS.InsertFile.FileDialog', {
	extend: 'BS.InsertFile.BaseDialog',

	singleton: true,
	id: 'bs-InsertFile-dlg-window',
	title: mw.message('bs-insertfile-titlefile').plain(),

	storeFileType: 'file',

	initComponent: function() {
		//this is neccessary to avoid strange cross-referencing between the
		//two instances of BS.InsertFile.BaseDialog subclasses
		this.configPanel.items = [];
		this.callParent(arguments);
	},
	onPnlConfigExpand: function(panel, eOpts){
		this.callParent(arguments);
	},
});