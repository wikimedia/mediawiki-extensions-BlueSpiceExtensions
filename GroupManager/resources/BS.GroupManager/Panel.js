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

Ext.define( 'BS.GroupManager.Panel', {
	extend: 'BS.CRUDGridPanel',
	initComponent: function() {
		this.strMain = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'GroupManager::getData' ),
				reader: {
					type: 'json',
					root: 'groups',
					idProperty: 'group_name',
					totalProperty: 'totalCount'
				}
			},
			autoLoad: true,
			fields: [ 'group_name', 'additional_group' ],
			sortInfo: {
				field: 'id',
				direction: 'ASC'
			}
		});

		this.colGroupName = Ext.create( 'Ext.grid.column.Column', {
			id: 'group_name',
			header: mw.message('bs-groupmanager-headerGroupname').plain(),
			sortable: true,
			dataIndex: 'group_name',
			flex: 1
		} );
		this.colAdditionalGroup = Ext.create( 'Ext.grid.column.Column', {
			id: 'additional_group',
			header: mw.message('bs-groupmanager-headerGroupname').plain(),
			sortable: true,
			dataIndex: 'additional_group',
			hidden: true
		} );

		this.colMainConf.columns = [
			this.colGroupName,
			this.colAdditionalGroup
		];
		this.callParent( arguments );
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgGroupAdd ) {
			this.dlgGroupAdd = Ext.create( 'BS.GroupManager.GroupDialog', {
				id: 'bs-groupmanager-add-dlg'
			} );
			this.dlgGroupAdd.on( 'ok', this.onDlgGroupAddOk, this );
		}

		this.active = 'add';
		this.dlgGroupAdd.setTitle( mw.message( 'bs-groupmanager-titleNewGroup' ).plain() );
		this.dlgGroupAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		if ( !selectedRow[0].getData().additional_group ) {
			bs.util.alert( 'GMfail', { text: mw.message( 'bs-groupmanager-msgNotEditable' ).plain(), title: 'Status' } );
			return;
		}
		if ( !this.dlgGroupEdit ) {
			this.dlgGroupEdit = Ext.create( 'BS.GroupManager.GroupDialog', {
				id: 'bs-groupmanager-edit-dlg'
			} );
			this.dlgGroupEdit.on( 'ok', this.onDlgUserEditOk, this );
		}

		this.active = 'edit';
		this.dlgGroupEdit.setTitle( mw.message( 'bs-groupmanager-titleEditGroup' ).plain() );
		this.dlgGroupEdit.setData( selectedRow[0].getData() );
		this.dlgGroupEdit.show();
		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var additionalGroup = selectedRow[0].get( 'additional_group' );
		if ( !additionalGroup ) {
			bs.util.alert( 'GMfail', { text: mw.message( 'bs-groupmanager-msgNotEditable' ).plain(), title: 'Status' } );
			return;
		}
		bs.util.confirm(
			'bs-groupmanager-remove-dlg',
			{
				text: mw.message( 'bs-groupmanager-removeGroup' ).plain(),
				title: mw.message( 'bs-groupmanager-tipRemove' ).plain()
			},
			{
				ok: this.onRemoveGroupOk,
				cancel: function() {},
				scope: this
			}
		);
	},
	onRemoveGroupOk: function() {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var groupName = selectedRow[0].get( 'group_name' );

		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'GroupManager::removeGroup',
				[ groupName ]
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
	onDlgGroupAddOk: function( data, group ) {
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'GroupManager::addGroup',
				[ group.group_name ]
			),
			method: 'post',
			scope: this,
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
					this.dlgGroupAdd.resetData();
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {}
		});
	},
	onDlgUserEditOk: function( data, group ) {
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'GroupManager::editGroup',
				[
					group.group_name,
					group.group_name_old
				]
			),
			method: 'post',
			scope: this,
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
					this.dlgGroupEdit.resetData();
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
			this.dlgGroupAdd.show();
		} else {
			this.dlgGroupEdit.show();
		}
	},
	renderMsgSuccess: function( responseObj ) {
		if ( responseObj.message.length ) {
			bs.util.alert( 'UMsuc', { text: responseObj.message, title: 'Status' }, { ok: this.reloadStore, cancel: function() {}, scope: this } );
		}
	},
	renderMsgFailure: function( responseObj ) {
		if ( responseObj.message.length ) {
			bs.util.alert( 'UMfail', { text: responseObj.message, title: 'Status' }, { ok: this.showDlgAgain, cancel: function() {}, scope: this } );
		}
	}
} );