/**
 * Statistics portlet number of pages
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define('BS.Statistics.StatisticsPortletNumberOfPages', {
	extend: 'BS.Statistics.StatisticsPortlet',
	portletConfigClass : 'BS.Statistics.StatisticsPortletConfig',
	categoryLabel: 'BlueSpice',
	beforeInitCompontent: function() {
		this.ctMainConfig = {
			axes: [],
			series: [],
			yTitle: mw.message('bs-statistics-portlet-numberofpages').plain()
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
					inputDiagrams: 'BsDiagramNumberOfPages',
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
