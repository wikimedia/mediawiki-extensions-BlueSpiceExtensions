/**
 * NamespaceManager Panel
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.NamespaceManager.Panel', {
	extend: 'BS.CRUDGridPanel',
	initComponent: function() {
		this.strMain = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'NamespaceManager::getData' ),
				reader: {
					type: 'json',
					root: 'results',
					idProperty: 'id',
					totalProperty: 'totalCount'
				}
			},
			autoLoad: true,
			remoteSort: true,
			fields: [ 'id', 'name', 'editable', 'content', 'searchable', 'subpages' ],
			sortInfo: {
				field: 'id',
				direction: 'ASC'
			}
		});

		this.colId = Ext.create( 'Ext.grid.column.Template', {
			id: 'ns-id',
			header: mw.message( 'bs-namespacemanager-headerNamespaceId' ).plain(),
			sortable: true,
			dataIndex: 'id',
			tpl: '{id}'
		} );
		this.colName = Ext.create( 'Ext.grid.column.Template', {
			id: 'ns-name',
			header: mw.message( 'bs-namespacemanager-headerNamespaceName' ).plain(),
			sortable: true,
			dataIndex: 'name',
			tpl: '{name}'
		} );
		this.colEditable = Ext.create( 'Ext.grid.column.Column', {
			id: 'ns-editable',
			header: mw.message( 'bs-namespacemanager-label-editable' ).plain(),
			xtype: 'templatecolumn',
			sortable: true,
			dataIndex: 'editable',
			renderer: this.renderIcon
		} );
		this.colSubpages = Ext.create( 'Ext.grid.column.Column', {
			id: 'ns-subpages',
			header: mw.message( 'bs-namespacemanager-headerIsSubpagesNamespace' ).plain(),
			xtype: 'templatecolumn',
			sortable: true,
			dataIndex: 'subpages',
			renderer: this.renderIcon
		} );
		this.colSearchable = Ext.create( 'Ext.grid.column.Column', {
			id: 'ns-serachable',
			header: mw.message( 'bs-namespacemanager-headerIsSearchableNamespace' ).plain(),
			xtype: 'templatecolumn',
			sortable: true,
			dataIndex: 'searchable',
			renderer: this.renderIcon
		} );
		this.colContent = Ext.create( 'Ext.grid.column.Column', {
			id: 'ns-content',
			header: mw.message( 'bs-namespacemanager-headerIsContentNamespace' ).plain(),
			xtype: 'templatecolumn',
			sortable: true,
			dataIndex: 'content',
			renderer: this.renderIcon
		} );

		this.colMainConf.columns = [
			this.colId,
			this.colName,
			this.colEditable,
			this.colSubpages,
			this.colSearchable,
			this.colContent
		];
		this.callParent( arguments );
	},
	renderIcon: function( value ) {
		if ( value === false ) {
			return '<img src="' + wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-cross.png"/>';
		}
		return '<img src="' + wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-tick.png"/>';
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgNamespaceAdd ) {
			this.dlgNamespaceAdd = Ext.create( 'BS.NamespaceManager.NamespaceDialog' );
			this.dlgNamespaceAdd.on( 'ok', this.onDlgNamespaceAddOk, this );
		}

		this.active = 'add';
		this.dlgNamespaceAdd.setTitle( mw.message( 'bs-namespacemanager-btnAddNamespace' ).plain() );
		this.dlgNamespaceAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		if ( !this.dlgNamespaceEdit ) {
			this.dlgNamespaceEdit = Ext.create( 'BS.NamespaceManager.NamespaceDialog' );
			this.dlgNamespaceEdit.on( 'ok', this.onDlgNamespaceEditOk, this );
		}

		this.active = 'edit';

		var editable = selectedRow[0].get( 'editable' );
		if ( editable === false ) {
			this.dlgNamespaceEdit.tfNamespaceName.disable();
		}

		this.dlgNamespaceEdit.setTitle( mw.message( 'bs-namespacemanager-tipEdit' ).plain() );
		this.dlgNamespaceEdit.setData( selectedRow[0].getData() );
		this.dlgNamespaceEdit.show();
		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		if ( !this.dlgNamespaceRemove ) {
			this.dlgNamespaceRemove = Ext.create( 'BS.NamespaceManager.NamespaceRemoveDialog' );
			this.dlgNamespaceRemove.on( 'ok', this.onDlgNamespaceRemoveOk, this );
		}

		this.dlgNamespaceRemove.setTitle( mw.message( 'bs-namespacemanager-tipRemove' ).plain() );
		this.dlgNamespaceRemove.setData( selectedRow[0].getData() );
		this.dlgNamespaceRemove.show();
		this.callParent( arguments );
	},
	onDlgNamespaceAddOk: function( data, namespace ) {
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'NamespaceManager::addNamespace',
				[
					namespace.name,
					namespace.subpages,
					namespace.searchable,
					namespace.evaluable
				]
			),
			method: 'post',
			scope: this,
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
					this.dlgNamespaceAdd.resetData();
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {}
		});
	},
	onDlgNamespaceEditOk: function( data, namespace ) {
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'NamespaceManager::editNamespace',
				[
					namespace.id,
					namespace.name,
					namespace.subpages,
					namespace.searchable,
					namespace.evaluable
				]
			),
			method: 'post',
			scope: this,
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.dlgNamespaceEdit.resetData();
					this.renderMsgSuccess( responseObj );
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {}
		});
	},
	onDlgNamespaceRemoveOk: function( data, namespace ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var editable = selectedRow[0].get( 'editable' );
		if ( editable === false ) {
			bs.util.alert( 'NMfail', { text: message, title: 'Status' }, { ok: function() {}, cancel: function() {}, scope: this } );
		}
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var id = selectedRow[0].get( 'id' );
		var doArticle = namespace.doarticle.rb;

		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'NamespaceManager::deleteNamespace',
				[ id, doArticle ]
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
	reloadStore: function() {
		this.strMain.reload();
	},
	showDlgAgain: function() {
		if ( this.active === 'add' ) {
			this.dlgNamespaceAdd.show();
		} else {
			this.dlgNamespaceEdit.show();
		}
	},
	renderMsgSuccess: function( responseObj ) {
		if ( responseObj.message.length ) {

			bs.util.alert( 'UMsuc', { text: responseObj.message, title: 'Status' }, { ok: this.reloadStore, cancel: function() {}, scope: this } );
		}
	},
	renderMsgFailure: function( responseObj ) {
		if ( responseObj.errors ) {
			var message = '';
			for ( i in responseObj.errors ) {
				if ( typeof( responseObj.errors[i].message ) !== 'string') continue;
				message = message + responseObj.errors[i].message + '<br />';
			}
			bs.util.alert( 'UMfail', { text: message, title: 'Status' }, { ok: this.showDlgAgain, cancel: function() {}, scope: this } );
			return;
		} else if ( responseObj.message.length ) {
			bs.util.alert( 'UMfail', { text: responseObj.message, title: 'Status' }, { ok: this.showDlgAgain, cancel: function() {}, scope: this } );
			return;
		}
	}
} );