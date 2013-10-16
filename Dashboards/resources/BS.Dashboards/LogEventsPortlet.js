Ext.define('BS.Dashboards.LogEventsPortlet', {
	extend: 'BS.portal.GridPortlet',
	height: 350,
	
	portletConfigClass : 'BS.Dashboards.LogEventsPortletConfig',

	beforeInitComponent: function() {
		this.gdMainConfig = {
			store: Ext.create('Ext.data.Store', {
				fields: [ 'logid', 'timestamp', 'action', 'user'],
				proxy: {
					type: 'ajax',
					url: mw.util.wikiScript('api'),
					reader: {
						type: 'json',
						root: 'query.logevents',
						idProperty: 'logid'
					},
					extraParams: {
						action: 'query',
						list: 'logevents',
						lelimit: 10,
						format: 'json'
					}
				},
				autoLoad: true
			}),
			columns: [{
				text : 'When?',
				dataIndex: 'timestamp'
			},{
				text : 'What?',
				dataIndex: 'action'
			},{
				text : 'Who?',
				dataIndex: 'user'
			}]
		}
	},

	afterInitComponent: function() {
		
	}
});
