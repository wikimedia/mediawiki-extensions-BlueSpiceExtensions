/**
 * UserManager Panel
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

Ext.define( 'BS.UserManager.Panel', {
	extend: 'BS.CRUDGridPanel',
	id: 'bs-usermanager-extgrid',
	features: [],
	initComponent: function() {
		this.smMain = this.smMain || Ext.create( 'Ext.selection.RowModel', {
			mode: "MULTI"
		});
		this.strMain = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'UserManager::getUsers' ),
				reader: {
					type: 'json',
					root: 'users',
					idProperty: 'user_id',
					totalProperty: 'totalCount'
				}
			},
			autoLoad: true,
			remoteSort: true,
			fields: [ 'user_id', 'user_name', 'user_page', 'user_real_name', 'user_email', 'groups' ],
			sortInfo: {
				field: 'id',
				direction: 'ASC'
			}
		});
		this.strGroups = Ext.create( 'Ext.data.JsonStore', {
			fields: [ 'group', 'displayname' ],
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'GroupManager::getGroups' ),
				reader: {
					type: 'json',
					root: 'groups',
					idProperty: 'group'
				}
			},
			autoLoad: true
		});

		this.strGroups.on( 'load', function( sender, records, successful, eOpts ){
			for( var i = 0; i < records.length; i++ ) {
				var record = records[i];
				record.set( 'id', record.get( 'group' ));
				record.set( 'text', record.get( 'displayname' ));
			}
		}, this );

		this.colUserName = Ext.create( 'Ext.grid.column.Template', {
			id: 'username',
			header: mw.message('bs-usermanager-headerusername').plain(),
			sortable: true,
			dataIndex: 'user_name',
			tpl: '<a href="{user_page}">{user_name}</a>'
		} );
		this.colRealName = Ext.create( 'Ext.grid.column.Template', {
			id: 'userrealname',
			header: mw.message('bs-usermanager-headerrealname').plain(),
			sortable: true,
			dataIndex: 'user_real_name',
			tpl: '{user_real_name}'
		} );
		this.colEmail = Ext.create( 'Ext.grid.column.Column', {
			id: this.getId()+'-useremail',
			header: mw.message('bs-usermanager-headeremail').plain(),
			sortable: true,
			dataIndex: 'user_email',
			renderer: this.renderEmail
		} );
		this.colGroups = Ext.create( 'Ext.grid.column.Column', {
			header: mw.message('bs-usermanager-headergroups').plain(),
			dataIndex: 'groups',
			renderer: this.renderGroups,
			sortable: false
		} );
		this.filters = Ext.create('Ext.ux.grid.FiltersFeature', {
			encode: true,
			local: false,
			filters: [{
				type: 'string',
				dataIndex: 'user_name',
				menuItems: ['ct']
			},{
				type: 'string',
				dataIndex: 'user_real_name',
				menuItems: ['ct']
			},{
				type: 'string',
				dataIndex: 'user_email',
				menuItems: ['ct']
			},{
				type: 'list',
				dataIndex: 'groups',
				store: this.strGroups
			}]
		});

		this.gpMainConf.features = [this.filters];

		this.colMainConf.columns = [
			this.colUserName,
			this.colRealName,
			this.colEmail,
			this.colGroups
		];
		this.callParent( arguments );
	},
	renderGroups: function( value ) {
		if ( value.length === 0 ) return '';

		var html = '<ul class="bs-extjs-list">';
		for ( var i = 0; i < value.length; i++ ) {
			html += '<li>' + value[i].displayname + '</li>';
		}
		html += '</ul>';
		return html;
	},
	renderEmail: function( value ) {
		if ( value.length === 0 ) return '';

		return '<a href="mailto:' + value + '">' + value + '</a>';
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgUserAdd ) {
			this.dlgUserAdd = Ext.create( 'BS.UserManager.UserDialog', {strGroups:this.strGroups} );
			this.dlgUserAdd.on( 'ok', this.onDlgUserAddOk, this );
		}

		this.active = 'add';
		this.dlgUserAdd.setTitle( mw.message( 'bs-usermanager-titleadduser' ).plain() );
		this.dlgUserAdd.tfUserName.enable();
		this.dlgUserAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		if ( !this.dlgUserEdit ) {
			this.dlgUserEdit = Ext.create( 'BS.UserManager.UserDialog', {strGroups:this.strGroups} );
			this.dlgUserEdit.on( 'ok', this.onDlgUserEditOk, this );
		}

		this.active = 'edit';
		this.dlgUserEdit.setTitle( mw.message( 'bs-usermanager-titleeditdetails' ).plain() );
		this.dlgUserEdit.tfUserName.disable();
		this.dlgUserEdit.setData( selectedRow[0].getData() );
		this.dlgUserEdit.show();
		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		bs.util.confirm(
			'UMremove',
			{
				text: mw.message( 'bs-usermanager-confirmdeleteuser', this.grdMain.getSelectionModel().getSelection().length ).text(),
				title: mw.message( 'bs-usermanager-titledeleteuser' ).plain()
			},
			{
				ok: this.onRemoveUserOk,
				cancel: function() {},
				scope: this
			}
		);
	},
	onRemoveUserOk: function() {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		for (var i = 0; i<selectedRow.length; i++){
			var userId = selectedRow[i].get( 'user_id' );

			Ext.Ajax.request( {
				url: bs.util.getAjaxDispatcherUrl(
					'UserManager::deleteUser',
					[ userId ]
				),
				scope: this,
				method: 'post',
				success: function( response, opts ) {
					var responseObj = Ext.decode( response.responseText );
					this.renderMsgSuccess( responseObj );
				}
			});
		}
	},
	onDlgUserAddOk: function( data, user ) {
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'UserManager::addUser',
				[
					user.user_name,
					user.user_password,
					user.user_repassword,
					user.user_email,
					user.user_real_name,
					user.groups
				]
			),
			method: 'post',
			scope: this,
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
					this.dlgUserAdd.resetData();
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {}
		});
	},
	onDlgUserEditOk: function( data, user ) {
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'UserManager::editUser',
				[
					user.user_name,
					user.user_password,
					user.user_repassword,
					user.user_email,
					user.user_real_name,
					user.groups
				]
			),
			method: 'post',
			scope: this,
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
					this.dlgUserEdit.resetData();
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
			this.dlgUserAdd.show();
		} else {
			this.dlgUserEdit.show();
		}
	},
	renderMsgSuccess: function( responseObj ) {
		if ( responseObj.message.length ) {
			var message = '';
			for ( var i in responseObj.message ) {
				if ( typeof( responseObj.message[i] ) !== 'string' ) continue;

				message = message + responseObj.message[i] + '<br />';
			}
			bs.util.alert( 'UMsuc', { text: message, titleMsg: 'bs-extjs-title-success' }, { ok: this.reloadStore, cancel: function() {}, scope: this } );
		}
	},
	renderMsgFailure: function( responseObj ) {
		if ( responseObj.errors.length ) {
			var message = '';
			for ( var i in responseObj.errors ) {
				if ( typeof( responseObj.errors[i].message ) !== 'string') continue;
				message = message + responseObj.errors[i].message + '<br />';
			}
			bs.util.alert( 'UMfail', { text: message, titleMsg: 'bs-extjs-title-warning' }, { ok: this.showDlgAgain, cancel: function() {}, scope: this } );
		}
	}
} );