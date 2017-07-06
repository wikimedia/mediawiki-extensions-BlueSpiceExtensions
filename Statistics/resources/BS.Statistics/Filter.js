/**
 * Statistics filter panel
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

Ext.define( 'BS.Statistics.Filter', {
	extend: 'Ext.form.Panel',
	requires: [
		'BS.store.BSApi', 'BS.store.ApiUser', 'BS.store.LocalNamespaces',
		'BS.store.ApiCategory', 'Ext.ux.form.MultiSelect'
	],
	//id: 'bs-statistics-dlg-filter',
	layout: 'form',
	bodyPadding: '5 5 0',
	frame: true,
	region: 'west',
	width: 300,
	autoScroll: true,
	collapsible: true,
	clientValidation: true,
	submitEmptyText: false,
	method: 'post',
	id: 'bs-statistics-filterpanel',
	initComponent: function() {
		var me = this;
		this.setTitle( mw.message('bs-statistics-filters').plain() );

		this.storeAvailableDiagrams = new BS.store.BSApi({
			apiAction: 'bs-statistics-available-diagrams-store',
			fields: ['key', 'displaytitle', 'listable', 'filters']
		});
		this.storeUserFilter = new BS.store.ApiUser();
		this.storeNamespaceFilter = new BS.store.LocalNamespaces({});
		this.storeCategoryFilter = new BS.store.ApiCategory();
		this.storeSearchscopeFilter = new BS.store.BSApi({
			apiAction: 'bs-statistics-search-options-store',
			fields: ['key', 'displaytitle']
		});

		this.storeAvailableGrains = new Ext.data.ArrayStore({
			fields: ['key', 'displaytitle'],
			data: [
				['Y', mw.message('bs-statistics-year').plain()],
				['m', mw.message('bs-statistics-month').plain()],
				['W', mw.message('bs-statistics-week').plain()],
				['d', mw.message('bs-statistics-day').plain()]
			]
		});

		this.cbInputDiagrams = new Ext.form.field.ComboBox({
			store: this.storeAvailableDiagrams,
			fieldLabel: mw.message('bs-statistics-diagram').plain(),
			labelAlign: 'right',
			name: 'inputDiagrams',
			displayField: 'displaytitle',
			valueField: 'key',
			typeAhead: true,
			mode: 'local',
			triggerAction: 'all',
			lastQuery: '',
			forceSelection: true,
			msgTarget: 'under'
		});

		this.cbInputDiagrams.addListener( 'select', this.cbInputDiagramsSelect, this);
		this.cbInputDiagrams.addListener( 'select', this.cbInputDiagramsSelect, this);

		var lastMonth = new Date();
		with(lastMonth) { setMonth(getMonth()-1) }

		this.dfInputFrom = new Ext.form.field.Date({
			fieldLabel: mw.message('bs-statistics-from').plain(),
			labelAlign: 'right',
			name: 'inputFrom',
			format: 'd.m.Y',
			maxValue: new Date(),
			value: lastMonth,
			msgTarget: 'under'
		});

		this.dfInputTo = new Ext.form.field.Date({
			fieldLabel: mw.message('bs-statistics-to').plain(),
			labelAlign: 'right',
			name: 'inputTo',
			format: 'd.m.Y',
			maxValue: new Date(),
			value: new Date(),
			msgTarget: 'under'
		});

		this.msInputFilterUsers = new Ext.ux.form.MultiSelect({
			store: this.storeUserFilter,
			fieldLabel: mw.message('bs-statistics-filter-user').plain(),
			labelAlign: 'right',
			name: 'hwpFilterBsFilterUsers[]',
			displayField: 'user_name',
			valueField: 'user_name',
			delimiter: null,
			height: 130,
			msgTarget: 'under'
		});

		this.msInputFilterNamespace = new Ext.ux.form.MultiSelect({
			store: this.storeNamespaceFilter,
			fieldLabel: mw.message('bs-ns').plain(),
			labelAlign: 'right',
			name: 'hwpFilterBsFilterNamespace[]',
			displayField: 'namespace',
			valueField: 'id',
			delimiter: null,
			height: 130,
			msgTarget: 'under'
		});

		this.msInputFilterCategory = new Ext.ux.form.MultiSelect({
			store: this.storeCategoryFilter,
			fieldLabel: mw.message('bs-statistics-filter-category').plain(),
			labelAlign: 'right',
			name: 'hwpFilterBsFilterCategory[]',
			displayField: 'cat_title',
			valueField: 'cat_title',
			delimiter: null,
			height: 130,
			msgTarget: 'under'
		});

		this.msInputFilterSearchscope = new Ext.ux.form.MultiSelect({
			store: this.storeSearchscopeFilter,
			fieldLabel: mw.message('bs-statistics-filter-searchscope').plain(),
			labelAlign: 'right',
			name: 'hwpFilterBsFilterSearchScope[]',
			displayField: 'displaytitle',
			valueField: 'key',
			delimiter: null,
			height: 130,
			msgTarget: 'under'
		});

		this.rgInputDepictionMode = new Ext.form.RadioGroup({
			fieldLabel: mw.message('bs-statistics-mode').plain(),
			labelAlign: 'right',
			columns: 1,
			vertical: false,
			allowBlank: false,
			msgTarget: 'under',
			items: [{
				boxLabel: mw.message('bs-statistics-absolute').plain(),
				labelAlign: 'right',
				name: 'rgInputDepictionMode',
				inputValue: 'absolute'
			},{
				boxLabel: mw.message('bs-statistics-aggregated').plain(),
				labelAlign: 'right',
				name: 'rgInputDepictionMode',
				inputValue: 'aggregated',
				checked: true
			}]
		});

		this.cbInputDepictionGrain = new Ext.form.field.ComboBox({
			store: this.storeAvailableGrains,
			fieldLabel: mw.message('bs-statistics-grain').plain(),
			labelAlign: 'right',
			name: 'InputDepictionGrain',
			displayField: 'displaytitle',
			valueField: 'key',
			typeAhead: true,
			mode: 'local',
			triggerAction: 'all',
			lastQuery: '',
			forceSelection: true,
			msgTarget: 'under'
		});
		this.cbInputDepictionGrain.select('W');

		this.btnOK = new Ext.Button({
			text: mw.message('bs-statistics-finish').plain(),
			id: 'bs-statistics-filterpanel-submit'
		});
		this.btnOK.addListener( 'click', this.btnOKclicked, this);

		this.buttons = [
			this.btnOK,
		];

		this.items = [
			this.cbInputDiagrams,
			this.dfInputFrom,
			this.dfInputTo,
			this.cbInputDepictionGrain,
			this.rgInputDepictionMode,
			this.msInputFilterUsers,
			this.msInputFilterNamespace,
			this.msInputFilterCategory,
			this.msInputFilterSearchscope
		];

		this.deactivateFilters();

		this.callParent();
	},
	cbInputDiagramsSelect: function( field, record ) {
		this.deactivateFilters();
		this.activateFilterByKeys( record[0].get('filters') );

		if( record[0].get('listable') ) {
			this.rgInputDepictionMode.add({
				boxLabel: mw.message('bs-statistics-list').plain(),
				name: 'rgInputDepictionMode',
				inputValue: 'list'
			});
		}
	},
	deactivateFilters: function() {
		this.removeAdditionalModes();

		this.msInputFilterUsers.disable();
		this.msInputFilterUsers.hide();

		this.msInputFilterNamespace.disable();
		this.msInputFilterNamespace.hide();

		this.msInputFilterCategory.disable();
		this.msInputFilterCategory.hide();

		this.msInputFilterSearchscope.disable();
		this.msInputFilterSearchscope.hide();
	},
	removeAdditionalModes: function () {
		this.rgInputDepictionMode.removeAll();
		this.rgInputDepictionMode.add({
			boxLabel: mw.message('bs-statistics-absolute').plain(),
			name: 'rgInputDepictionMode',
			inputValue: 'absolute'
		});
		this.rgInputDepictionMode.add({
			boxLabel: mw.message('bs-statistics-aggregated').plain(),
			name: 'rgInputDepictionMode',
			inputValue: 'aggregated'
		});
	},
	activateFilterByKeys: function( keys ) {
		for( var i = 0; i < keys.length; i++ ) {
			if( keys[i] == 'hwpFilterBsFilterUsers' ) {
				this.msInputFilterUsers.enable();
				this.msInputFilterUsers.show();
			} else if( keys[i] == 'hwpFilterBsFilterCategory' ) {
				this.msInputFilterCategory.enable();
				this.msInputFilterCategory.show();
			} else if( keys[i] == 'hwpFilterBsFilterNamespace' ) {
				this.msInputFilterNamespace.enable();
				this.msInputFilterNamespace.show();
			} else if( keys[i] == 'hwpFilterBsFilterSearchScope' ) {
				this.msInputFilterSearchscope.enable();
				this.msInputFilterSearchscope.show();
			}
		}
	},
	btnOKclicked: function(button,event){
		if( this.getForm().isValid() == false ) return;
		this.fireEvent('btnOKBeforeSend', this);
		var me = this;
		bs.api.tasks.execSilent(
			"statistics",
			"getData",
			{
				diagram: this.cbInputDiagrams.getValue(),
				from: this.dfInputFrom.getSubmitValue(),
				to: this.dfInputTo.getSubmitValue(),
				grain: this.cbInputDepictionGrain.getValue(),
				mode: this.rgInputDepictionMode.getValue().rgInputDepictionMode,
				hwpFilterBsFilterUsers: this.msInputFilterUsers.getValue(),
				hwpFilterBsFilterNamespace: this.msInputFilterNamespace.getValue(),
				hwpFilterBsFilterCategory: this.msInputFilterCategory.getValue(),
				hwpFilterBsFilterSearchScope: this.msInputFilterSearchscope.getValue()
			}
		).done( function( result ) {
			me.getEl().unmask();
			me.fireEvent('saved', me, result.payload.data, result.payload);
			me.collapse();
		});
	}
});