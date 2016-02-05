Ext.onReady( function(){
	Ext.create( 'BS.Dashboards.DashboardPanel', {
		renderTo: 'bs-dashboards-admindashboard',
		portalConfig: mw.config.get('bsPortalConfig')
	} );
} );