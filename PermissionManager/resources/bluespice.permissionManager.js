Ext.Loader.setPath(
	'BS.panel.Maximizable',
	wgScriptPath + '/extensions/BlueSpiceExtensions/PermissionManager' +
	'/resources/BS.panel/Maximizable.js'
);

Ext.create('BS.PermissionManager.panel.Manager', {
	renderTo: 'panelPermissionManager'
});