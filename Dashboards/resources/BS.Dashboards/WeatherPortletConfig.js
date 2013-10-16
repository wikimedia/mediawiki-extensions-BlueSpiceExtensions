Ext.define('BS.Dashboards.WeatherPortletConfig', {
	extend: 'BS.portal.PortletConfig',
	
	afterInitComponent: function() {
		this.cbLocation = Ext.create('Ext.form.field.ComboBox',{
			fieldLabel: 'Location'
		});

		this.items = [
			this.cbLocation
		];
		
		this.callParent( arguments );
	},
	
	setConfigControlValues: function( cfg ) {
		this.callParent( arguments );
	},
	
	getConfigControlValues: function() {
		var cfg = this.callParent( arguments );
		cfg.woeid = this.cbLocation.getValue();

		return cfg;
	}
});