/**
 * Statistics panel
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

Ext.define( 'BS.Statistics.Panel', {
	extend: 'Ext.Panel',
	//id: 'bs-statistics-dlg-panel',
	layout: 'border',
	height: 600,
	id: 'bs-statistics-mainpanel',

	initComponent: function() {
		this.pnlFilters = Ext.create('BS.Statistics.Filter');
		this.pnlFilters.on('saved', this.onPnlFiltersSaved, this);
		this.pnlFilters.on('btnOKBeforeSend', this.filtersBtnOKBeforeSend, this)

		this.crtMain = Ext.create('BS.Statistics.Chart');

		this.muExport = Ext.create('Ext.menu.Menu', {
			width: 100,
			margin: '0 0 10 0'
		});

		this.muExport.add({
			text: 'SVG',
			value: 'image/svg+xml'
		});
		if( mw.config.get('BsExtendedStatisticsAllowPNGExport', false) === true ) {
			this.muExport.add({
				text: 'PNG',
				value: 'image/png'
			});
		}
		this.muExport.on('click', this.onClickmuExport, this);

		this.btnExport = Ext.create( 'Ext.Button', {
			text: 'Export',
			menu: this.muExport,
			id: 'bs-statistics-mainpanel-exportmenu'
		});

		this.tbarMain = Ext.create('Ext.toolbar.Toolbar', {
			items: [
				'->',
				this.btnExport
			]
		});

		this.pnlStats = Ext.create('Ext.Panel', {
			region: 'center',
			legend: {
				position: 'right'
			},
			layout: 'border',
			html: '',
			items: [

			]});
		this.pnlStats.hide();

		this.pnlMain = Ext.create('Ext.Panel', {
			region: 'center',
			legend: {
				position: 'right'
			},
			layout: 'border',
			html: '',
			tbar: this.tbarMain,
			items: [
				this.crtMain,
				this.pnlStats
			]});

		this.items = [
			this.pnlFilters,
			this.pnlMain
		];

		this.callParent();
	},
	filtersBtnOKBeforeSend: function() {
		this.getEl().mask(
			mw.message('bs-extjs-loading').plain(),
			Ext.baseCSSPrefix + 'mask-loading'
		);
	},
	getListGrid: function( data, label, width, height ) {
		var listResultStore = Ext.create( "Ext.data.Store", {
			fields: data.fields,
			data: data.list,
			pageSize: 20,
			proxy: {
				type: 'memory',
				enablePaging: true,
				reader: {
					type: 'json',
					root: 'items'
				}
			}
		});

		var panel = Ext.create( "Ext.grid.Panel", {
			title: label,
			store: listResultStore,
			columns: data.columns,
			//NOTE: these substractions are needed to account for paging toolbar
			height: height - 30,
			width: width - 30,
			dockedItems: [{
				xtype: 'pagingtoolbar',
				store: listResultStore,
				dock: 'bottom',
				displayInfo: true
			}]
		});

		return panel;
	},
	onPnlFiltersSaved: function(sender, data, result) {
		this.getEl().unmask();
		//Quickfix: Sometimes label was not set
		this.crtMain.setCategory(result.label);
		if( typeof data.list == 'undefined' ) {
			this.muExport.enable();
			this.pnlStats.hide();
			var task = new Ext.util.DelayedTask(function(){
				this.crtMain.show();
				//this.pnlMain.doLayout();
				this.crtMain.setData(data);
				this.crtMain.setCategory(result.label);
			}, this);
			task.delay(100);
			return;
		}
		this.crtMain.hide();
		this.muExport.disable();
		this.pnlStats.update(data.list);
		this.pnlStats.removeAll();
		this.pnlStats.show();
		this.pnlStats.add( this.getListGrid( data, result.label, this.getWidth(), this.pnlMain.getHeight() ) );
		this.pnlStats.doLayout();
	},
	onClickmuExport: function( menu, item, e, eOpts ) {
		if(item.value == 'image/png') {
			Ext.draw.engine.ImageExporter.defaultUrl = mw.util.getUrl(
				'Special:ExtendedStatistics/export-png'
			);
			this.crtMain.save( {type:item.value} );
			return;
		}
		var form = Ext.getBody().createChild({
			tag: 'form',
			method: 'POST',
			action: mw.util.getUrl( 'Special:ExtendedStatistics/export-svg' ),
			target : '_blank',
			children: [{
				tag: 'input',
				type: 'hidden',
				name: 'width',
				value: this.crtMain.getWidth()
			}, {
				tag: 'input',
				type: 'hidden',
				name: 'height',
				value: this.crtMain.getHeight()
			}, {
				tag: 'input',
				type: 'hidden',
				name: 'type',
				value: 'image/svg+xml'
			}, {
				tag: 'input',
				type: 'hidden',
				name: 'svg'
			}]
		});
		form.last(null, true).value = this.crtMain.save( {type:'image/svg+xml'} );
		form.dom.submit({target : '_blank'});
		form.remove();
	}
});
