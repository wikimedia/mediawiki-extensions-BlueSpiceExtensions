Ext.define('BS.Dashboards.LogEventsPortletConfig', {
	extend: 'BS.portal.PortletConfig',
	
	afterInitComponent: function() {
		this.cbAction = Ext.create('Ext.form.field.ComboBox',{
			fieldLabel: mw.message('bs-dashboards-logevents-action').plain()
		});

		this.items = [
			this.cbAction
		];
		
		this.callParent( arguments );
	},
	
	setConfigControlValues: function( cfg ) {
		this.callParent( arguments );
	},
	
	getConfigControlValues: function() {
		var cfg = this.callParent( arguments );
		cfg.mwapi = {
			leaction: this.cbAction.getValue()
		};
		return cfg;
	}
});