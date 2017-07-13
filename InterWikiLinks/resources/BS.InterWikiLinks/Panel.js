/**
 * GroupManager Panel
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.InterWikiLinks.Panel', {
	extend: 'BS.CRUDGridPanel',
	features: [],

	initComponent: function() {
		this.strMain = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-interwiki-store',
			fields: [ 'iw_prefix', 'iw_url' ]
		});

		this.colIWLPrefix = Ext.create( 'Ext.grid.column.Column', {
			id: 'iw_prefix',
			header: mw.message('bs-interwikilinks-headerprefix').plain(),
			sortable: true,
			dataIndex: 'iw_prefix',
			flex: 1,
			filterable: true
		} );
		this.colIWLUrl = Ext.create( 'Ext.grid.column.Column', {
			id: 'iw_url',
			header: mw.message('bs-interwikilinks-headerurl').plain(),
			sortable: true,
			dataIndex: 'iw_url',
			flex: 1,
			filterable: true
		} );

		this.colMainConf.columns = [
			this.colIWLPrefix,
			this.colIWLUrl
		];

		this.callParent( arguments );
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgIWLAdd ) {
			this.dlgIWLAdd = Ext.create( 'BS.InterWikiLinks.InterWikiLinksDialog' );
			this.dlgIWLAdd.on( 'ok', this.onDlgIWLAddOk, this );
		}

		this.active = 'add';
		this.dlgIWLAdd.setTitle( mw.message( 'bs-interwikilinks-titleaddinterwikilink' ).plain() );
		this.dlgIWLAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		if ( !this.dlgIWLEdit ) {
			this.dlgIWLEdit = Ext.create( 'BS.InterWikiLinks.InterWikiLinksDialog' );
			this.dlgIWLEdit.on( 'ok', this.onDlgIWLEditOk, this );
		}

		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		this.active = 'edit';
		this.dlgIWLEdit.setTitle( mw.message( 'bs-interwikilinks-titleeditinterwikilink' ).plain() );
		this.dlgIWLEdit.setData( selectedRow[0].getData() );
		this.dlgIWLEdit.show();
		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		bs.util.confirm(
			'GMremove',
			{
				text: mw.message( 'bs-interwikilinks-confirmdeleteinterwikilink' ).plain(),
				title: mw.message( 'bs-interwikilinks-titledeleteinterwikilink' ).plain()
			},
			{
				ok: this.onRemoveIWLOk,
				cancel: function() {},
				scope: this
			}
		);
	},
	onRemoveIWLOk: function() {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var iwprefix = selectedRow[0].get( 'iw_prefix' );
		var me = this;
		bs.api.tasks.exec(
			'interwikilinks',
			'removeInterWikiLink',
			{
				prefix: iwprefix
			}
		).done( function( response ) {
			me.reloadStore();
		});
	},
	onDlgIWLAddOk: function( data, iwl ) {
		var me = this;
		bs.api.tasks.exec(
			'interwikilinks',
			'editInterWikiLink',
			{
				prefix: iwl.iw_prefix,
				url: iwl.iw_url
			}
		).done( function( response ) {
			me.dlgIWLAdd.resetData();
			me.reloadStore();
		});
	},
	onDlgIWLEditOk: function( data, iwl ) {
		var me = this;
		bs.api.tasks.exec(
			'interwikilinks',
			'editInterWikiLink',
			{
				prefix: iwl.iw_prefix,
				url: iwl.iw_url,
				oldPrefix: iwl.iw_prefix_old
			}
		).done( function( response ) {
			me.dlgIWLEdit.resetData();
			me.reloadStore();
		});
	},
	reloadStore: function() {
		this.strMain.reload();
	},
	showDlgAgain: function() {
		if ( this.active === 'add' ) {
			this.dlgIWLAdd.show();
		} else {
			this.dlgIWLEdit.show();
		}
	}
} );