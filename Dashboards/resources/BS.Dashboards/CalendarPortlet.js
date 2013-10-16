Ext.define('BS.Dashboards.CalendarPortlet', {
	extend: 'BS.portal.Portlet',
	height: 300,
	
	portletConfigClass : 'BS.Dashboards.CalendarPortletConfig',

	initComponent: function() {
		this.dpCalendar = Ext.create('Ext.DatePicker',{});
		this.items = [this.dpCalendar];
		this.callParent();
	}
});
