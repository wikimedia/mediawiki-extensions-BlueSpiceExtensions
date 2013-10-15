Ext.define( 'BS.Dashboards.PortletCatalog', {
	extend: 'BS.Window',
	width: 400,
	singleton: true,
	title: mw.message( 'bs-dashboards-addportlet' ).plain(),
	currentData: {},

	afterInitComponent: function() {
		//this.buttons = []; //We don't need buttons as we use drag and drop
		if ( bsPortalConfigLocation === 'AdminDashboard' ) {
			this.ajaxDispatcherUrl = 'Dashboards::getAdminDashboardConfig';
		} else {
			this.ajaxDispatcherUrl = 'Dashboards::getUserDashboardConfig';
		}
		this.strPortlets = Ext.create( 'Ext.data.JsonStore', {
				proxy: {
					type: 'ajax',
					url: bs.util.getAjaxDispatcherUrl( this.ajaxDispatcherUrl ),
					reader: {
						type: 'json',
						root: 'portlets',
						idProperty: 'title'
					}
				},
				autoLoad: true,
				fields: [ 'title', 'type', 'config', 'description' ],
				sortInfo: {
					field: 'id',
					direction: 'ASC'
				}
		});
		this.gdPortlets = Ext.create( 'Ext.grid.Panel', {
			store: this.strPortlets,
			hideHeaders: true,
			columns: [{
				flex: 1,
				dataIndex: 'title',
				renderer: this.renderColumn
			}]
		});

		this.items = [
			this.gdPortlets
		];

		this.callParent(arguments);
	},
	renderColumn: function( value, meta, record ) {
		var html = value;
		if ( record.data.description ) {
			html += "<div class='bs-portlets-desc'>" + record.data.description + "</div>";
		}

		return html;
	},
	getData: function() {
		var selectedRow = this.gdPortlets.getSelectionModel().getSelection();
		this.currentData.type = selectedRow[0].get( 'type' );
		this.currentData.config = selectedRow[0].get( 'config' );

		return this.currentData;
	}
});