/**
 * GroupManager Panel
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @author     Tobias Weichart <weichart@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.GroupManager.Panel', {
	extend: 'BS.CRUDGridPanel',
	initComponent: function() {
		this.strMain = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-group-store',
			fields: ['group_name', 'additional_group'],
			submitValue: false
		});

		this.colGroupName = Ext.create( 'Ext.grid.column.Column', {
			id: 'group_name',
			header: mw.message('bs-groupmanager-headergroup').plain(),
			sortable: true,
			dataIndex: 'group_name',
			flex: 1,
			filterable: true
		} );
		this.colAdditionalGroup = Ext.create( 'Ext.grid.column.Column', {
			id: 'additional_group',
			header: mw.message('bs-groupmanager-headergroup').plain(),
			sortable: true,
			dataIndex: 'additional_group',
			hidden: true,
			hideable: false
		} );

		this.colMainConf.columns = [
			this.colGroupName,
			this.colAdditionalGroup
		];
		this.callParent( arguments );
	},
	makeSelModel: function(){
		this.smModel = Ext.create( 'Ext.selection.CheckboxModel', {
			mode: "MULTI",
			selType: 'checkboxmodel'
		});
		return this.smModel;
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgGroupAdd ) {
			this.dlgGroupAdd = Ext.create( 'BS.GroupManager.GroupDialog', {
				id: 'bs-groupmanager-add-dlg'
			} );
			this.dlgGroupAdd.on( 'ok', this.onDlgGroupAddOk, this );
		}

		this.active = 'add';
		this.dlgGroupAdd.setTitle( mw.message( 'bs-groupmanager-titlenewgroup' ).plain() );
		this.dlgGroupAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		if ( !selectedRow[0].getData().additional_group ) {
			bs.util.alert( 'GMfail', { text: mw.message( 'bs-groupmanager-msgnoteditable' ).plain(), titleMsg: 'bs-extjs-title-warning' } );
			return;
		}
		if ( !this.dlgGroupEdit ) {
			this.dlgGroupEdit = Ext.create( 'BS.GroupManager.GroupDialog', {
				id: 'bs-groupmanager-edit-dlg'
			} );
			this.dlgGroupEdit.on( 'ok', this.onDlgUserEditOk, this );
		}

		this.active = 'edit';
		this.dlgGroupEdit.setTitle( mw.message( 'bs-groupmanager-titleeditgroup' ).plain() );
		this.dlgGroupEdit.setData( selectedRow[0].getData() );
		this.dlgGroupEdit.show();
		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var additionalGroup = false;
		for(var i=0;i<selectedRow.length;i++){
			if (selectedRow[i].get( 'additional_group' ) === true){
				additionalGroup = true;
				break;
			}
		}
		if ( !additionalGroup ) {
			bs.util.alert( 'GMfail', { text: mw.message( 'bs-groupmanager-msgnotremovable' ).plain(), titleMsg: 'bs-extjs-title-warning' } );
			return;
		}
		bs.util.confirm(
			'bs-groupmanager-remove-dlg',
			{
				text: mw.message( 'bs-groupmanager-removegroup', selectedRow.length).text(),
				title: mw.message( 'bs-groupmanager-tipremove', selectedRow.length ).text()
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
		var groupNames = [];
		for (var i = 0; i < selectedRow.length; i++){
			groupNames.push(selectedRow[i].get( 'group_name' ));
		}

		Ext.Ajax.request( {
			url: mw.util.wikiScript( 'api' ),
			method: 'post',
			scope: this,
			params: {
				action: 'bs-groupmanager',
				task: 'removeGroups',
				format: 'json',
				token: mw.user.tokens.get( 'editToken', '' ),
				taskData: Ext.encode({
					'groups': groupNames
				})
			},
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success ) {
					this.renderMsgSuccess( responseObj );
				} else {
					this.renderMsgFailure( responseObj );
				}
			}
		});
	},
	onDlgGroupAddOk: function( data, group ) {
		Ext.Ajax.request( {
			url: mw.util.wikiScript( 'api' ),
			method: 'post',
			scope: this,
			params: {
				action: 'bs-groupmanager',
				task: 'addGroup',
				format: 'json',
				token: mw.user.tokens.get( 'editToken', '' ),
				taskData: Ext.encode({
					'group': group.group_name
				})
			},
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
			url: mw.util.wikiScript( 'api' ),
			method: 'post',
			scope: this,
			params: {
				action: 'bs-groupmanager',
				task: 'editGroup',
				format: 'json',
				token: mw.user.tokens.get( 'editToken', '' ),
				taskData: Ext.encode({
					'group': group.group_name_old,
					'newGroup': group.group_name
				})
			},
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
		var successText = "";
		var success = "", failure = "", successCount = 0, failureCount = 0;
		if ( typeof(responseObj.message) !== "undefined" && typeof(responseObj.message.length) !== "undefined" && responseObj.message.length )
			successText = responseObj.message;
		else{
			$.each(responseObj, function(i, response){
				if (response.success === true){
					success += "<li>"+i+"</li>";
					successCount++;
				}
				else{
					failure += "<li>"+i+"</li>";
					failureCount++;
				}
			});
			successText = success.length > 0 ? (mw.message("bs-groupmanager-removegroup-message-success", successCount, "<ul>"+success+"</ul>").text() + "<br/>") : "";
			successText += failure.length > 0 ? (mw.message("bs-groupmanager-removegroup-message-failure", failureCount, "<ul>"+failure+"</ul>").text()) : "";
		}
		if ( !failureCount ) {
			mw.notify( successText, { title: mw.msg( 'bs-extjs-title-success' ) } );
			this.reloadStore();
		} else {
			bs.util.alert( 'UMsuc', { text: successText, titleMsg: 'bs-extjs-title-success' }, { ok: this.reloadStore, cancel: function() {}, scope: this } );
		}
	},
	renderMsgFailure: function( responseObj ) {
		if ( responseObj.message.length ) {
			bs.util.alert( 'UMfail', { text: responseObj.message, titleMsg: 'bs-extjs-title-warning' }, { ok: this.showDlgAgain, cancel: function() {}, scope: this } );
		}
	}
} );