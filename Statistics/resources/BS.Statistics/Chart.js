/**
 * Statistics Chart
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.Statistics.Chart', {
	extend: 'Ext.chart.Chart',
	//id: 'bs-statistics-dlg-chart',
	style: 'background:#fff',
	animate: true,
	shadow: true,
	theme: 'Blue',
	legend: {
		position: 'right'
	},
	axes: [],
	series: [],
	initComponent: function() {

		this.store = Ext.create('Ext.data.JsonStore', {
			fields: ['name', 'hits']
		});

		this.axes.push({
			type: 'Numeric',
			position: 'left',
			fields: ['hits'],
			title: mw.message('bs-statistics-label-count').plain(),
			grid: {
				odd: {
					opacity: 1,
					fill: '#ddd',
					stroke: '#bbb',
					'stroke-width': 0.5
				}
			}
		});

		this.axes.push({
			type: 'Category',
			position: 'bottom',
			fields: ['name'],
			title: 'Bluespice'
		});

		this.series.push({
			type: 'line',
			highlight: {
				size: 7,
				radius: 7
			},
			axis: 'left',
			smooth: false,
			fill: true,
			xField: 'name',
			yField: 'hits',
			markerConfig: {
				type: 'cross',
				size: 4,
				radius: 4,
				'stroke-width': 0
			}
		});

		this.callParent();
	},
	setData: function(data) {
		this.store.loadData(data);
	},
	setCategory: function(label) {
		this.axes.getAt(1).title = label;
	}
});