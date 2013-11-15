Ext.define('BS.Statistics.StatisticsPortletNumberOfEdits', {
	extend: 'BS.Statistics.StatisticsPortlet',
	portletConfigClass : 'BS.Statistics.StatisticsPortletConfig',
	categoryLabel: 'Bluespice',
	beforeInitCompontent: function() {
		this.ctMainConfig = {
			axes: [],
			series: [],
			yTitle: mw.message('bs-statistics-portlet-NumberOfEdits').plain()
		};

		this.ctMainConfig.store = Ext.create('Ext.data.JsonStore', {
			method: 'post',
			fields: ['name', 'hits'],
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl('SpecialExtendedStatistics::ajaxSave'),
				reader: {
					type: 'json',
					root: 'data'
				},
				extraParams: {
					portletType: 'ExtendedStatistics',
					inputDiagrams: 'BsDiagramNumberOfEdits',
					rgInputDepictionMode: 'aggregated',
					inputTo: Ext.Date.format(new Date(),'d.m.Y'),
					inputFrom: Ext.Date.format(this.getPeriod(), 'd.m.Y'),
					InputDepictionGrain: this.getGrain()
				}
			},
			autoLoad: true
		});

		this.callParent();
	},

	getPeriod: function() {
		return this.callParent();
	},
	getGrain: function() {
		return this.callParent();
	}
});
