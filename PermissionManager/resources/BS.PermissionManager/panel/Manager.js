Ext.define( 'BS.PermissionManager.panel.Manager', {
	extend: 'BS.panel.Maximizable',
	requires: [
		'Ext.state.Manager',
		'BS.PermissionManager.data.Manager',
		'BS.PermissionManager.grid.Permissions',
		'BS.PermissionManager.tree.Groups',
		'BS.PermissionManager.TemplateEditor',
		'BS.PermissionManager.data.Manager'
	],
	layout: 'border',
	initComponent: function() {
		var me = this;

		$(window).bind( 'beforeunload', function() {
			var dataManager = Ext.create('BS.PermissionManager.data.Manager');
			if(dataManager.isDirty()) {
				if(/chrome/.test(navigator.userAgent.toLowerCase())) { //chrome compatibility
					return mw.message('bs-PermissionManager-unsaved-changes').plain();
				}
				if(window.event) {
					window.event.returnValue = mw.message('bs-PermissionManager-unsaved-changes').plain();
				} else {
					return mw.message('bs-PermissionManager-unsaved-changes').plain();
				}
			}
		});

		me._templateEditor = false;

		me.btnOK = new Ext.Button({
			text: mw.message('bs-permissionmanager-btn-save-label').plain(),
			handler: function() {
				Ext.create('BS.PermissionManager.data.Manager').savePermissions( this );
			},
			scope: this
		});

		me.btnCancel = new Ext.Button({
			text: mw.message('htmlform-reset').plain(),
			handler: function() {
				var dataManager = Ext.create('BS.PermissionManager.data.Manager');
				dataManager.resetAllSettings();

				Ext.data.StoreManager
					.lookup('bs-permissionmanager-permission-store')
					.loadRawData(dataManager.buildPermissionData().permissions);
			}
		});

		me.btnTemplateEditor = new Ext.Button({
			text: mw.message('bs-permissionmanager-btn-template-editor'),
			handler: function() {
				if(!me._templateEditor) {
					me._templateEditor = Ext.create('BS.PermissionManager.TemplateEditor', {});
				}
				me._templateEditor.show();
			}
		});

		me.title = mw.message('bs-permissionmanager-btn-group-label').plain() + ' user';

		me.items = [
			new BS.PermissionManager.grid.Permissions({
				region: 'center'
			}),
			new BS.PermissionManager.tree.Groups({
				region: 'west',
				collapsed: true,
				collapsible: true,
				width: 200
			})
		];
		me.buttons = [
			me.btnTemplateEditor,
			me.btnOK,
			me.btnCancel
		];
		me.callParent(arguments);
	}
});