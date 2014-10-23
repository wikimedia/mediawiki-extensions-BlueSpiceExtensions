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
		var fieldDefs = mw.config.get('bsNamespaceManagerMetaFields');
		var fields = [];
		var columns = [];

		//TODO: the "fieldDefs" should contain a "config" property that allows
		//for more settings than just the few ones we process here
		for( var i = 0; i < fieldDefs.length; i++ ) {
			var fieldDef = fieldDefs[i];
			fields.push( fieldDef.name );
			var column = {
				id: 'ns-'+ fieldDef.name,
				dataIndex: fieldDef.name,
				header: fieldDef.label,
				sortable: fieldDef.sortable || true
			};
			if( fieldDef.type === 'boolean' ) {
				column.renderer = this.renderIcon;
				column.flex = 0.5;
			}
			if( i === 0 ){ //Typically the ID column
				column.flex = 0;
				column.width = 50;
			}
			columns.push( column );
		}

		$(document).trigger('BSNamespaceManagerInitCompontent', [this, fields, columns]);

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
			fields: fields,
			sortInfo: {
				field: 'id',
				direction: 'ASC'
			}
		});

		this.colMainConf.columns = columns;
		this.callParent( arguments );
	},
	renderIcon: function( value ) {
		//TODO: make CSS class icon
		var icon = '<img src="' + wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/{0}"/>';
		if ( value === false ) {
			return icon.format( 'bs-cross.png');
		}
		return icon.format( 'bs-tick.png');
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgNamespaceAdd ) {
			this.dlgNamespaceAdd = Ext.create( 'BS.NamespaceManager.NamespaceDialog', {id:"bs-namespacemanager-add-dlg"} );
			this.dlgNamespaceAdd.on( 'ok', this.onDlgNamespaceAddOk, this );
		}

		//TODO: not nice. Decision on wether is "add" or "edit" shold be made
		//by the dialog depending on the provided ID. I.e. -1 for "add"
		this.active = 'add';
		this.dlgNamespaceAdd.setTitle( mw.message( 'bs-namespacemanager-tipadd' ).plain() );
		this.dlgNamespaceAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		if ( !this.dlgNamespaceEdit ) {
			this.dlgNamespaceEdit = Ext.create( 'BS.NamespaceManager.NamespaceDialog', {id:"bs-namespacemanager-edit-dlg"} );
			this.dlgNamespaceEdit.on( 'ok', this.onDlgNamespaceEditOk, this );
		}

		this.active = 'edit';
		this.dlgNamespaceEdit.setTitle( mw.message( 'bs-namespacemanager-tipedit' ).plain() );
		this.dlgNamespaceEdit.setData( selectedRow[0].getData() );
		this.dlgNamespaceEdit.show();
		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var editable = selectedRow[0].get( 'editable' );
		if ( editable === false ) {
			bs.util.alert(
				'NMfail',
				{
					textMsg: 'bs-namespacemanager-msgnoteditabledelete',
					titleMsg: 'bs-extjs-title-warning'
				}
			);
			return;
		}
		if ( !this.dlgNamespaceRemove ) {
			this.dlgNamespaceRemove = Ext.create(
				'BS.NamespaceManager.NamespaceRemoveDialog',
				{
					id: "bs-namespacemanager-remove-dlg",
					nsName: selectedRow[0].get( 'name' )
				}
			);
			this.dlgNamespaceRemove.on( 'ok', this.onDlgNamespaceRemoveOk, this );
		}

		this.dlgNamespaceRemove.setTitle( mw.message( 'bs-namespacemanager-tipremove' ).plain() );
		this.dlgNamespaceRemove.setData( selectedRow[0].getData() );
		this.dlgNamespaceRemove.show();
		this.callParent( arguments );
	},
	onDlgNamespaceAddOk: function( sender, namespace ) {
		var additionalSettings = this.getAdditionalSettings( namespace );
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'NamespaceManager::addNamespace',
				[
					namespace.name,
					additionalSettings
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
	onDlgNamespaceEditOk: function( sender, namespace ) {
		var additionalSettings = this.getAdditionalSettings( namespace );
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'NamespaceManager::editNamespace',
				[
					namespace.id,
					namespace.name,
					additionalSettings
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
	getAdditionalSettings: function( data ) {
		var filteredData = {};
		for( prop in data ) {
			if( $.inArray(prop, ['id', 'name', 'editable']) !== -1 ) {
				continue;
			}
			filteredData[prop] = data[prop];
		}
		return Ext.encode( filteredData );
	},
	onDlgNamespaceRemoveOk: function( data, namespace ) {
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
			bs.util.alert(
				'bs-nm-suc',
				{
					text: responseObj.message,
					titleMsg: 'bs-extjs-title-success'
				},
				{
					ok: this.reloadStore,
					scope: this
				}
			);
		}
	},
	renderMsgFailure: function( responseObj ) {
		if ( responseObj.errors ) {
			var message = '';
			for ( i in responseObj.errors ) {
				if ( typeof( responseObj.errors[i].message ) !== 'string') continue;
				message = message + responseObj.errors[i].message + '<br />';
			}
			bs.util.alert(
				'bs-nm-fail',
				{
					text: message,
					titleMsg: 'bs-extjs-title-warning'
				},
				{
					ok: this.showDlgAgain,
					scope: this
				}
			);
			return;
		} else if ( responseObj.message.length ) {
			bs.util.alert(
				'bs-nm-fail',
				{
					text: responseObj.message,
					titleMsg: 'bs-extjs-title-warning'
				},
				{
					ok: this.showDlgAgain,
					scope: this
				}
			);
			return;
		}
	}
} );