/**
 * Statistics filter panel
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

Ext.define( 'BS.Statistics.Filter', {
	extend: 'Ext.form.Panel',
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
	url: mw.util.wikiScript(),
	id: 'bs-statistics-filterpanel',
	baseParams: {
		action: 'ajax',
		rs: 'SpecialExtendedStatistics::ajaxSave'
	},
	initComponent: function() {
		this.setTitle( mw.message('bs-statistics-filters').plain() );

		this.storeAvailableDiagrams = new Ext.create('Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'Statistics::ajaxGetAvalableDiagrams' ),
				reader: {
					type: 'json',
					root: 'data'
				}
			},
			autoLoad: true,
			fields: ['key', 'displaytitle', 'listable', 'filters']
		});
		this.storeUserFilter = new Ext.create('Ext.data.JsonStore', {
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
		this.storeNamespaceFilter = new Ext.create('Ext.data.JsonStore', {
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
		this.storeCategoryFilter = new Ext.create('Ext.data.JsonStore', {
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
		this.storeSearchscopeFilter = new Ext.create('Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'Statistics::ajaxGetSearchscopeFilter' ),
				reader: {
					type: 'json',
					root: 'data'
				}
			},
			autoLoad: true,
			fields: ['key', 'displaytitle']
		});

		this.storeAvailableGrains = new Ext.create('Ext.data.ArrayStore', {
			fields: ['key', 'displaytitle'],
			data: [
				['Y', mw.message('bs-statistics-year').plain()],
				['m', mw.message('bs-statistics-month').plain()],
				['W', mw.message('bs-statistics-week').plain()],
				['d', mw.message('bs-statistics-day').plain()]
			]
		});

		this.cbInputDiagrams = new Ext.create('Ext.form.field.ComboBox',{
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
			forceSelection: true
		});

		this.cbInputDiagrams.addListener( 'select', this.cbInputDiagramsSelect, this);

		var lastMonth = new Date();
		with(lastMonth) { setMonth(getMonth()-1) }

		this.dfInputFrom = new Ext.create('Ext.form.field.Date',{
			fieldLabel: mw.message('bs-statistics-from').plain(),
			labelAlign: 'right',
			name: 'inputFrom',
			format: 'd.m.Y',
			maxValue: new Date(),
			value: lastMonth
		});

		this.dfInputTo = new Ext.create('Ext.form.field.Date',{
			fieldLabel: mw.message('bs-statistics-to').plain(),
			labelAlign: 'right',
			name: 'inputTo',
			format: 'd.m.Y',
			maxValue: new Date(),
			value: new Date()
		});

		this.msInputFilterUsers = new Ext.create('Ext.ux.form.MultiSelect',{
			store: this.storeUserFilter,
			fieldLabel: mw.message('bs-statistics-filter-user').plain(),
			labelAlign: 'right',
			name: 'hwpFilterBsFilterUsers[]',
			displayField: 'displaytitle',
			valueField: 'key',
			delimiter: null,
			height: 130
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

		this.msInputFilterSearchscope = new Ext.create('Ext.ux.form.MultiSelect',{
			store: this.storeSearchscopeFilter,
			fieldLabel: mw.message('bs-statistics-filter-searchscope').plain(),
			labelAlign: 'right',
			name: 'hwpFilterBsFilterSearchScope[]',
			displayField: 'displaytitle',
			valueField: 'key',
			delimiter: null,
			height: 130
		});

		this.rgInputDepictionMode = new Ext.create('Ext.form.RadioGroup', {
			fieldLabel: mw.message('bs-statistics-mode').plain(),
			labelAlign: 'right',
			columns: 1,
			vertical: false,
			allowBlank: false
		});
		this.rgInputDepictionMode.add({ 
			boxLabel: mw.message('bs-statistics-absolute').plain(),
			labelAlign: 'right',
			name: 'rgInputDepictionMode', 
			inputValue: 'absolute'
		});
		this.rgInputDepictionMode.add({ 
			boxLabel: mw.message('bs-statistics-aggregated').plain(),
			labelAlign: 'right',
			name: 'rgInputDepictionMode', 
			inputValue: 'aggregated'
		});

		this.cbInputDepictionGrain = new Ext.create('Ext.form.field.ComboBox',{
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
			forceSelection: true
		});
		this.cbInputDepictionGrain.select('W');

		this.btnOK = new Ext.create( 'Ext.Button', {
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
		this.fireEvent('btnOKBeforeSend', this)
		this.getForm().doAction('submit', {
			success: this.fpSuccess,
			failure: this.fpFailure,
			scope: this
		});
	},
	fpSuccess: function( form, action ) {
		this.fireEvent('saved', this, action.result.data, action.result);
		this.collapse();
		if( action.result.message === undefined || action.result.message == '') return;
		bs.util.alert( 'STAsuc', { text: action.result.message, titleMsg: 'bs-extjs-title-success' } );
	},
	fpFailure: function( form, action ) {
		if( action.result.message === undefined || action.result.message == '') return;
		bs.util.alert( 'STAfail', { text: action.result.message, titleMsg: 'bs-extjs-title-success' } );
	}
});