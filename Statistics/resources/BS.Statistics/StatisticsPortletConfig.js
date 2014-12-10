/**
 * Statistics portlet config base
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

Ext.define('BS.Statistics.StatisticsPortletConfig', {
	extend: 'BS.portal.PortletConfig',
	filters: [],
	afterInitComponent: function() {

		this.strPeriod = new Ext.create('Ext.data.ArrayStore', {
			fields: ['key', 'displaytitle'],
			data: [
				['day', mw.message('bs-statistics-portletconfig-periodday').plain()],
				['week', mw.message('bs-statistics-week').plain()],
				['month', mw.message('bs-statistics-month').plain()],
			]
		});

		this.cbInputPeriod = new Ext.create('Ext.form.field.ComboBox',{
			store: this.strPeriod,
			fieldLabel: mw.message('bs-extjs-portal-timespan').plain(),
			labelAlign: 'right',
			name: 'inputPeriod',
			displayField: 'displaytitle',
			valueField: 'key',
			mode: 'local',
			triggerAction: 'all',
			lastQuery: '',
			forceSelection: true
		});
		this.items.push( this.cbInputPeriod );

		//TODO filter
		if( $.inArray('UserFilter', this.filters) > -1 ) {
			this.strUserFilter = new Ext.create('Ext.data.JsonStore', {
				proxy: {
					type: 'ajax',
					url: bs.util.getAjaxDispatcherUrl( 'Statistics::ajaxGetUserFilter' ),
					reader: {
						type: 'json',
						root: 'data'
					}
				},
				autoLoad: true,
				fields: ['key', 'displaytitle']
			});

			this.msInputFilterUsers = new Ext.create('Ext.ux.form.MultiSelect',{
				store: this.strUserFilter,
				fieldLabel: mw.message('bs-statistics-filter-user').plain(),
				labelAlign: 'right',
				name: 'hwpFilterBsFilterUsers[]',
				displayField: 'displaytitle',
				valueField: 'key',
				delimiter: null,
				height: 130
			});
			this.items.push( this.msInputFilterUsers );
		}

		if( $.inArray('NamespaceFilter', this.filters) > -1 ) {
			this.strNamespaceFilter = new Ext.create('Ext.data.JsonStore', {
				proxy: {
					type: 'ajax',
					url: bs.util.getAjaxDispatcherUrl( 'Statistics::ajaxGetNamespaceFilter' ),
					reader: {
						type: 'json',
						root: 'data'
					}
				},
				autoLoad: true,
				fields: ['key', 'displaytitle']
			});

			this.msInputFilterNamespace = new Ext.create('Ext.ux.form.MultiSelect',{
				store: this.storeNamespaceFilter,
				fieldLabel: mw.message('bs-ns').plain(),
				labelAlign: 'right',
				name: 'hwpFilterBsFilterNamespace[]',
				displayField: 'displaytitle',
				valueField: 'key',
				delimiter: null,
				height: 130
			});
			this.items.push( this.msInputFilterNamespace );
		}

		if( $.inArray('CategoryFilter', this.filters) > -1 ) {
			this.strCategoryFilter = new Ext.create('Ext.data.JsonStore', {
				proxy: {
					type: 'ajax',
					url: bs.util.getAjaxDispatcherUrl( 'Statistics::ajaxGetCategoryFilter' ),
					reader: {
						type: 'json',
						root: 'data'
					}
				},
				autoLoad: true,
				fields: ['key', 'displaytitle']
			});

			this.msInputFilterCategory = new Ext.create('Ext.ux.form.MultiSelect',{
				store: this.storeCategoryFilter,
				fieldLabel: mw.message('bs-statistics-filter-category').plain(),
				labelAlign: 'right',
				name: 'hwpFilterBsFilterCategory[]',
				displayField: 'displaytitle',
				valueField: 'key',
				delimiter: null,
				height: 130
			});
			this.items.push( this.msInputFilterCategory );
		}

		this.callParent( arguments );
	},

	setConfigControlValues: function( cfg ) {
		this.cbInputPeriod.setValue( cfg.inputPeriod );
		this.callParent( arguments );
	},

	getConfigControlValues: function() {
		var cfg = this.callParent( arguments );
		cfg.inputPeriod = this.cbInputPeriod.getValue();

		/*if( $.inArray('UserFilter', this.filters) > -1 ) {
			cfg.userFilter = this.msInputFilterUsers.getValue();
		}*/
		return cfg;
	}
});