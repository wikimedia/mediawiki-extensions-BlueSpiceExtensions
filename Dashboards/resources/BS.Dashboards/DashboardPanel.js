Ext.define('BS.Dashboards.DashboardPanel', {
	extend: 'BS.portal.PortalPanel',

	//Custom Settings
	saveConfigBackend: {
		rs: mw.config.get( 'bsPortalConfigSavebackend' ),
		additionalArgs: []
	},
	cmPortlets: false,

	initComponent: function() {
		this.items = [];

		for ( var i = 0; i < this.portalConfig.length; i++ ) {
			var columnConfig = this.portalConfig[i];
			var portlets = [];

			for ( var j = 0; j < columnConfig.length; j++ ) {
				try {
					var portletConfig = columnConfig[j];
					var portlet = Ext.create(
						portletConfig.type,
						portletConfig.config
					);
				} catch ( e ) {
					//Workaround: Portlets of deactivated extensions are still
					//loaded from DB. This ends in a TypeError while "Ext.create".
					mw.log( e );
					continue;
				}
				//Listen to config changes to persist them
				portlet.on( 'configchange', this.onPortletConfigChange, this );
				portlet.on( 'destroy', this.onPortletDestroy, this );
				portlets.push(portlet);
			}

			var column = Ext.create('BS.portal.PortalColumn', {
				items: portlets
			});
			this.items.push( column );
		}

		//Listen to changes ot the arrangement to persist them
		this.on( 'drop', this.onDrop, this );

		//For event handler that need the rendered DOM Elements
		this.on( 'afterrender', this.onAfterRender, this );

		Ext.require( 'BS.Dashboards.PortletCatalog', function() {
			BS.Dashboards.PortletCatalog.on( 'ok', this.onPortletCatalogOk, this );
		}, this );

		this.callParent(arguments);
	},
	onPortletCatalogOk: function ( data, portlet ) {
		var portlet = Ext.create( portlet.type, portlet.config );
		portlet.on( 'configchange', this.onPortletConfigChange, this );
		portlet.on( 'close', this.onPortletClose, this );
		this.items.getAt(0).insert( 0, portlet );
		this.savePortalConfig();
	},
	onPortletConfigChange: function( portlet, cfg ) {
		this.savePortalConfig();
	},
	onPortletDestroy: function( portlet ) {
		this.items.remove( portlet );
		this.savePortalConfig();
	},
	onDrop: function() {
		this.savePortalConfig();
	},
	onAfterRender: function( panel, layout, eOpts ) {
		//Allow context menu
		this.getEl().on( 'contextmenu', this.onContextMenu, this );
	},
	onContextMenu: function( event, element, eOpts ) {
		event.preventDefault();
	},
	getPortalConfig: function() {
		var portletConfig = [];
		var numberOfColumns = this.items.length;
		for ( var i = 0; i < numberOfColumns; i++ ) {
			var column = this.items.getAt(i);
			var columnConfig = [];
			var numberOfPortlets = column.items.length;
			for( var j = 0; j < numberOfPortlets; j++ ) {
				var portlet = column.items.getAt(j);
				var className = Ext.getClassName( portlet );
				var cfg = {
					type: className,
					config: portlet.getPortletConfig()
				};
				columnConfig.push( cfg );
			}
			portletConfig.push(columnConfig);
		}

		return portletConfig;
	},
	savePortalConfig: function() {
		if ( typeof wgReadOnly !== "undefined" ) {
			if ( wgReadOnly ) return;
		}

		var portletConfig = this.getPortalConfig();

		bs.api.tasks.exec(
				"dashboards",
				mw.config.get( 'bsPortalConfigSavebackend' ),
				{
					portletConfig: Ext.Array.merge(
						[Ext.encode( portletConfig )],
						this.saveConfigBackend.additionalArgs
					)
				}
		);
	}
});