/**
 * Statistics portlet config base
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define('BS.Statistics.StatisticsPortletConfig', {
	extend: 'BS.portal.PortletConfig',
	requires: [
		'BS.store.BSApi', 'BS.store.ApiUser', 'BS.store.LocalNamespaces',
		'BS.store.ApiCategory', 'Ext.ux.form.MultiSelect'
	],
	filters: [],
	afterInitComponent: function() {

		this.strPeriod = new Ext.data.ArrayStore({
			fields: ['key', 'displaytitle'],
			data: [
				['day', mw.message('bs-statistics-portletconfig-periodday').plain()],
				['week', mw.message('bs-statistics-week').plain()],
				['month', mw.message('bs-statistics-month').plain()],
			]
		});

		this.cbInputPeriod = new Ext.form.field.ComboBox({
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
			this.strUserFilter = new BS.store.ApiUser();

			this.msInputFilterUsers = new Ext.ux.form.MultiSelect({
				store: this.strUserFilter,
				fieldLabel: mw.message('bs-statistics-filter-user').plain(),
				labelAlign: 'right',
				name: 'hwpFilterBsFilterUsers[]',
				displayField: 'user_name',
				valueField: 'user_name',
				delimiter: null,
				height: 130
			});
			this.items.push( this.msInputFilterUsers );
		}

		if( $.inArray('NamespaceFilter', this.filters) > -1 ) {
			this.strNamespaceFilter = new BS.store.LocalNamespaces();

			this.msInputFilterNamespace = new Ext.ux.form.MultiSelect({
				store: this.storeNamespaceFilter,
				fieldLabel: mw.message('bs-ns').plain(),
				labelAlign: 'right',
				name: 'hwpFilterBsFilterNamespace[]',
				displayField: 'namespace',
				valueField: 'id',
				delimiter: null,
				height: 130
			});
			this.items.push( this.msInputFilterNamespace );
		}

		if( $.inArray('CategoryFilter', this.filters) > -1 ) {
			this.strCategoryFilter = new BS.store.ApiCategory();

			this.msInputFilterCategory = new Ext.ux.form.MultiSelect({
				store: this.storeCategoryFilter,
				fieldLabel: mw.message('bs-statistics-filter-category').plain(),
				labelAlign: 'right',
				name: 'hwpFilterBsFilterCategory[]',
				displayField: 'cat_title',
				valueField: 'cat_title',
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