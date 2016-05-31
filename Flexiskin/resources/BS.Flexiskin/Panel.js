/**
 * Flexiskin Panel
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Tobias Weichart <weichart@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage Flexiskin
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.Flexiskin.Panel', {
	extend: 'BS.CRUDGridPanel',
	id: 'bs-flexiskin-panel',
	requires: [ 'BS.store.BSApi' ],
	initComponent: function() {
		this.strMain = new BS.store.BSApi( {
			apiAction: 'bs-flexiskin-store',
			fields: [ 'flexiskin_id', 'flexiskin_name', 'flexiskin_desc', 'flexiskin_active', 'flexiskin_config' ],
			sortInfo: {
				field: 'flexiskin_name',
				direction: 'ASC'
			}
		} );
		this.colName = Ext.create('Ext.grid.column.Template', {
			id: 'flexiskin_name',
			header: mw.message( 'bs-flexiskin-labelname' ).plain(),
			sortable: true,
			dataIndex: 'flexiskin_name',
			tpl: '{flexiskin_name}'
		});
		this.colDesc = Ext.create( 'Ext.grid.column.Template', {
			id: 'flexiskin_desc',
			header: mw.message( 'bs-flexiskin-labeldesc' ).plain(),
			sortable: true,
			dataIndex: 'flexiskin_desc',
			tpl: '{flexiskin_desc}'
		});
		this.colActive = Ext.create( 'Ext.grid.column.CheckColumn', {
			id: 'flexiskin_active',
			header: mw.message( 'bs-flexiskin-headeractive' ).plain(),
			sortable: true,
			dataIndex: 'flexiskin_active'
		});
		this.colMainConf.columns = [
			this.colName,
			this.colDesc,
			this.colActive
		];
		this.colActive.on( 'checkchange', this.onCheckActiveChange, this );
		this.callParent( arguments );
	},
	onCheckActiveChange: function( oCheckBox, rowindex, checked ) {
		bs.api.tasks.exec( 'flexiskin', 'activate', {
			id: checked ? this.grdMain.getStore().getAt( rowindex ).getData().flexiskin_id : ""
		} )
		.done( function() {
			window.location.reload();
		});
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgSkinAdd ) {
			this.dlgSkinAdd = Ext.create( 'BS.Flexiskin.AddSkin' );
			this.dlgSkinAdd.on( 'ok', this.onDlgSkinAdd, this );
		}

		this.dlgSkinAdd.setTitle( mw.message('bs-flexiskin-titleaddskin' ).plain());
		this.dlgSkinAdd.tfName.enable();
		this.dlgSkinAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		this.selectedRow = this.grdMain.getSelectionModel().getSelection();

		Ext.require( 'BS.Flexiskin.PreviewWindow', function() {
			var config = this.selectedRow[0].get( 'flexiskin_config' );
			BS.Flexiskin.PreviewWindow.setData( {
				skinId: this.selectedRow[0].get( 'flexiskin_id' ),
				config: config
			});
			Ext.getCmp( 'bs-flexiskin-preview-menu' ).onItemStateChange();
			BS.Flexiskin.PreviewWindow.show();
			},
			this
		);
		this.callParent(arguments);
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		bs.util.confirm(
			'UMremove',
			{
				text: mw.message( 'bs-flexiskin-confirmdeleteskin' ).plain(),
				title: mw.message( 'bs-extjs-delete' ).plain()
			},
			{
				ok: this.onRemoveSkinOk,
				cancel: function() {
				},
				scope: this
			}
			);
	},
	onRemoveSkinOk: function(){
		var me = this;
		var selectedRow = me.grdMain.getSelectionModel().getSelection();
		var skinId = selectedRow[0].get( 'flexiskin_id' );

		bs.api.tasks.exec( 'flexiskin', 'delete', {
			id: skinId
		})
		.done( function( response, opts ) {
			me.reloadStore();
		});
	},
	reloadStore: function() {
		this.strMain.reload();
	},
	onDlgSkinAdd: function( data, user ) {
		var me = this;

		bs.api.tasks.exec( 'flexiskin', 'add', {
			data: this.getAddSkinData()
		})
		.done( function( response, opts ){
				me.dlgSkinAdd.resetData();
				me.reloadStore();
			});
	},
	getAddSkinData: function() {
		var data = [];
		data.push( {
			name: this.dlgSkinAdd.tfName.getValue(),
			desc: this.dlgSkinAdd.tfDesc.getValue(),
			template: this.dlgSkinAdd.cbSkins.getValue()
		} );
		return data;
	}
});
