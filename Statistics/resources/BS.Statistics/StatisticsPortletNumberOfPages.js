Ext.define('BS.Statistics.StatisticsPortletNumberOfPages', {
	extend: 'BS.Statistics.StatisticsPortlet',
	portletConfigClass : 'BS.Statistics.StatisticsPortletConfig',
	categoryLabel: 'Bluespice',
	beforeInitCompontent: function() {
		this.ctMainConfig = {
			axes: [],
			series: [],
			yTitle: mw.message('bs-statistics-portlet-NumberOfPages').plain()
		};

		var oneWeekAgo = new Date();
		oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);

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
					inputDiagrams: 'BsDiagramNumberOfPages',
					rgInputDepictionMode: 'aggregated',
					inputTo: Ext.Date.format(new Date(),'d.m.Y'),
					inputFrom: Ext.Date.format(oneWeekAgo, 'd.m.Y'),
					InputDepictionGrain: 'd'
				}
			},
			autoLoad: true
		});

		this.callParent();
	}
});
