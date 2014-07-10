/**
 * GroupManager Panel
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.InterWikiLinks.Panel', {
	extend: 'BS.CRUDGridPanel',
	initComponent: function() {
		this.strMain = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'InterWikiLinks::getInterWikiLinks' ),
				reader: {
					type: 'json',
					root: 'iwlinks',
					idProperty: 'iwl_prefix',
					totalProperty: 'totalCount'
				}
			},
			autoLoad: true,
			fields: [ 'iwl_prefix', 'iwl_url' ],
			sortInfo: {
				field: 'id',
				direction: 'ASC'
			}
		});

		this.colIWLPrefix = Ext.create( 'Ext.grid.column.Column', {
			id: 'iwl_prefix',
			header: mw.message('bs-interwikilinks-headerprefix').plain(),
			sortable: true,
			dataIndex: 'iwl_prefix',
			flex: 1
		} );
		this.colIWLUrl = Ext.create( 'Ext.grid.column.Column', {
			id: 'iwl_url',
			header: mw.message('bs-interwikilinks-headerurl').plain(),
			sortable: true,
			dataIndex: 'iwl_url',
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
		this.editmode = false;
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
		this.editmode = true;
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
		var iwprefix = selectedRow[0].get( 'iwl_prefix' );

		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'InterWikiLinks::doDeleteInterWikiLink',
				[ iwprefix ]
			),
			method: 'post',
			scope: this,
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
			url: bs.util.getAjaxDispatcherUrl(
				'InterWikiLinks::doEditInterWikiLink',
				[ 
					this.editmode,
					iwl.iwl_prefix,
					iwl.iwl_url
				]
			),
			method: 'post',
			scope: this,
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
			url: bs.util.getAjaxDispatcherUrl(
				'InterWikiLinks::doEditInterWikiLink',
				[
					this.editmode,
					iwl.iwl_prefix,
					iwl.iwl_url,
					iwl.iwl_prefix_old
				]
			),
			method: 'post',
			scope: this,
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