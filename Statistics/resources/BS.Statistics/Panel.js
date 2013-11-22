/**
 * Statistics panel
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

Ext.define( 'BS.Statistics.Panel', {
	extend: 'Ext.Panel',
	//id: 'bs-statistics-dlg-panel',
	layout: 'border',
	height: 600,

	initComponent: function() {
		this.pnlFilters = Ext.create('BS.Statistics.Filter');
		this.pnlFilters.on('saved', this.onPnlFiltersSaved, this);

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
			menu: this.muExport
		});

		this.tbarMain = Ext.create('Ext.toolbar.Toolbar', {
			items: [
				'->',
				this.btnExport
			]
		});

		this.pnlMain = Ext.create('Ext.Panel', {
			region: 'center',
			legend: {
				position: 'right'
			},
			layout: 'border',
			html: '',
			tbar: this.tbarMain,
			items: [
				this.crtMain
			]});

		this.items = [
			this.pnlFilters, 
			this.pnlMain
		];

		this.callParent();
	},
	onPnlFiltersSaved: function(sender, data, result) {
		this.crtMain.hide();
		this.pnlMain.update('');
		if( typeof data.list == 'undefined' ) {
			this.crtMain.setData(data);
			this.crtMain.setCategory(result.label);
			this.crtMain.show();
			return;
		}
		this.pnlMain.update(data.list);
	},
	onClickmuExport: function( menu, item, e, eOpts ) {
		if(item.value == 'image/png') {
			Ext.draw.engine.ImageExporter.defaultUrl = mw.util.wikiGetlink('Special:ExtendedStatistics/export-png');
			this.crtMain.save( {type:item.value} );
			return;
		}
		var form = Ext.getBody().createChild({
			tag: 'form',
			method: 'POST',
			action: mw.util.wikiGetlink('Special:ExtendedStatistics/export-svg'),
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
		form.dom.submit();
		form.remove();
	}
});