Ext.define('BS.Dashboards.CalendarPortletConfig', {
	extend: 'BS.portal.PortletConfig',
	
	afterInitComponent: function() {
		this.callParent( arguments );
	},
	setConfigControlValues: function( cfg ) {
		this.callParent( arguments );
	},
	getConfigControlValues: function() {
		var cfg = this.callParent( arguments );
		return cfg;
	}
});