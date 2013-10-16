Ext.define('BS.Dashboards.WeatherPortlet', {
	extend: 'BS.portal.Portlet',
	
	portletConfigClass : 'BS.Dashboards.WeatherPortletConfig',

	initComponent: function() {
		this.imgIcon = Ext.create('Ext.Img',{
			
		});
		
		this.items = [
			this.imgIcon
		];
		
		Ext.data.JSONP.request({
			url: '',
			callbackKey: '_bsweatherportlet',
			success: this.onDataLoad,
			scope: this
		});
		
		this.callParent(arguments);
	},
	
	onDataLoad: function() {
		//TODO: this.imgIcon.setSrc()
		console.log(arguments);
	}
});
