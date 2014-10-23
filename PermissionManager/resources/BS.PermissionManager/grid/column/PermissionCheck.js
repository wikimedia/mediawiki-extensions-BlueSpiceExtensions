Ext.define('BS.PermissionManager.grid.column.PermissionCheck', {
	extend: 'Ext.grid.column.CheckColumn',
	alias: 'widget.bs-pm-permissioncheck',
	renderer: function(value, meta, record) {
		var me = this;
		var dataIndex = me.dataIndex;
		var cssPrefix = Ext.baseCSSPrefix;
		var cls = [cssPrefix + 'grid-checkcolumn'];

		if (record.get('type') > 1 && dataIndex !== 'userCan_Wiki') {
			cls = [];
			return;
		}
		if (this.disabled) {
			meta.tdCls += ' ' + this.disabledCls;
		}
		if (value === BS.PermissionManager.ALLOWED_EXPLICIT) {
			cls.push(cssPrefix + 'grid-checkcolumn-checked');
		}
		if (value) {
			meta.tdCls = 'allowed';
		}
		return '<img class="' + cls.join(' ') + '" src="' + Ext.BLANK_IMAGE_URL + '"/>';
	}
});