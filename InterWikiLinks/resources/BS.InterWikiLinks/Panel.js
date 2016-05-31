/**
 * GroupManager Panel
 *
 * Part of BlueSpice for MediaWiki
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
			flex: 1
		} );
		this.colIWLUrl = Ext.create( 'Ext.grid.column.Column', {
			id: 'iw_url',
			header: mw.message('bs-interwikilinks-headerurl').plain(),
			sortable: true,
			dataIndex: 'iw_url',
			flex: 1
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

		Ext.Ajax.request( {
			url: mw.util.wikiScript( 'api' ),
			method: 'post',
			scope: this,
			params: {
				action: 'bs-interwikilinks-tasks',
				task: 'removeInterWikiLink',
				format: 'json',
				token: mw.user.tokens.get( 'editToken', '' ),
				taskData: Ext.encode({
					prefix: iwprefix
				})
			},
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
				} else {
					this.renderMsgFailure( responseObj );
				}
			}
		});
	},
	onDlgIWLAddOk: function( data, iwl ) {
		Ext.Ajax.request( {
			url: mw.util.wikiScript( 'api' ),
			method: 'post',
			scope: this,
			params: {
				action: 'bs-interwikilinks-tasks',
				task: 'editInterWikiLink',
				format: 'json',
				token: mw.user.tokens.get( 'editToken', '' ),
				taskData: Ext.encode({
					prefix: iwl.iw_prefix,
					url: iwl.iw_url
				})
			},
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
					this.dlgIWLAdd.resetData();
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {}
		});
	},
	onDlgIWLEditOk: function( data, iwl ) {
		Ext.Ajax.request( {
			url: mw.util.wikiScript( 'api' ),
			method: 'post',
			scope: this,
			params: {
				action: 'bs-interwikilinks-tasks',
				task: 'editInterWikiLink',
				format: 'json',
				token: mw.user.tokens.get( 'editToken', '' ),
				taskData: Ext.encode({
					prefix: iwl.iw_prefix,
					url: iwl.iw_url,
					oldPrefix: iwl.iw_prefix_old
				})
			},
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
					this.dlgIWLEdit.resetData();
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {}
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
	},
	renderMsgSuccess: function( responseObj ) {
		if ( responseObj.message.length ) {
			bs.util.alert( 'UMsuc', { text: responseObj.message, titleMsg: 'bs-extjs-title-success' }, { ok: this.reloadStore, cancel: function() {}, scope: this } );
		}
	},
	renderMsgFailure: function( responseObj ) {
		if ( responseObj.errors ) {
			var message = '';
			for ( i in responseObj.errors ) {
				if ( typeof( responseObj.errors[i].message ) !== 'string') continue;
				message = message + responseObj.errors[i].message + '<br />';
			}
			bs.util.alert( 'UMfail', { text: message, titleMsg: 'bs-extjs-title-warning' }, { ok: this.showDlgAgain, cancel: function() {}, scope: this } );
		}
	}
} );