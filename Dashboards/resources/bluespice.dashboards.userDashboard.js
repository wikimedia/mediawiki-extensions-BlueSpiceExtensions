Ext.onReady( function(){
	Ext.create('BS.Dashboards.DashboardPanel', {
		renderTo: 'bs-dashboards-userdashboard',
		portalConfig: mw.config.get( 'bsPortalConfig' )
	} );
} );