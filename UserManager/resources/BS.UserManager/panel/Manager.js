/**
 * UserManager Panel
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.UserManager.panel.Manager', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.UserManager.dialog.User', 'BS.UserManager.dialog.UserGroups' ],
	id: 'bs-usermanager-extgrid',
	features: [],
	initComponent: function() {
		this.strMain = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-adminuser-store',
			fields: [
				'user_id',
				'user_name',
				'user_page_link',
				'user_real_name',
				'user_email',
				'page_link',
				'groups'
			],
			proxy: {
				reader:{
					idProperty: 'user_id'
			}}
		});

		this.strGroups = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-group-store',
			fields: ['group_name', 'additional_group', 'displayname'],
			submitValue: false
		});

		this.strGroups.on( 'load', function( sender, records, successful, eOpts ){
			for( var i = 0; i < records.length; i++ ) {
				var record = records[i];
				record.set( 'id', record.get( 'group_name' ));
				record.set( 'text', record.get( 'displayname' ));
			}
		}, this );

		this.colUserName = Ext.create( 'Ext.grid.column.Template', {
			id: 'username',
			header: mw.message('bs-usermanager-headerusername').plain(),
			sortable: true,
			dataIndex: 'user_name',
			tpl: '{page_link}',
			flex: 1
		} );
		this.colRealName = Ext.create( 'Ext.grid.column.Template', {
			id: 'userrealname',
			header: mw.message('bs-usermanager-headerrealname').plain(),
			sortable: true,
			dataIndex: 'user_real_name',
			tpl: '{user_real_name}',
			flex: 1
		} );
		this.colEmail = Ext.create( 'Ext.grid.column.Column', {
			id: this.getId()+'-useremail',
			header: mw.message('bs-usermanager-headeremail').plain(),
			sortable: true,
			dataIndex: 'user_email',
			renderer: this.renderEmail,
			flex: 1
		} );
		this.colGroups = Ext.create( 'Ext.grid.column.Column', {
			header: mw.message('bs-usermanager-headergroups').plain(),
			dataIndex: 'groups',
			renderer: this.renderGroups,
			sortable: false,
			flex: 1
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

	makeSelModel: function(){
		this.smModel = Ext.create( 'Ext.selection.CheckboxModel', {
			mode: "MULTI",
			selType: 'checkboxmodel'
		});
		return this.smModel;
	},

	makeGridColumns: function() {
		var columns = this.callParent(arguments);
		return {
			items: columns.items
		};
	},

	renderGroups: function( value ) {
		if ( value.length === 0 ) return '';

		var html = '<ul class="bs-extjs-list">';
		for ( var i = 0; i < value.length; i++ ) {
			if( i === 2  ) {
				html += '<li>' + mw.html.element(
					'a',
					{
						href: '#',
						class: 'bs-um-more-groups'
					},
					mw.message('bs-usermanager-groups-more').plain()
				) + '</li>';
				html += '</ul>';
				html += '<ul class="bs-extjs-list bs-um-hidden-groups" style="display:none">';
			}
			//TODO: Get group display name from this.strGroups without crashing
			//or use messages instead
			html += '<li>' + value[i] + '</li>';
		}
		html += '</ul>';

		return html;
	},
	renderEmail: function( value ) {
		if ( value.length === 0 ) return '';

		return '<a href="mailto:' + value + '">' + value + '</a>';
	},
	onGrdMainRowClick: function( oSender, iRowIndex, oEvent ) {
		this.callParent(arguments);
		/*
		 * We override base class functionality which disables edit button on
		 * multi selection
		 */
		this.btnEdit.enable();
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgUserAdd ) {
			this.dlgUserAdd = new BS.UserManager.dialog.User({
				strGroups:this.strGroups
			});
			this.dlgUserAdd.on( 'ok', this.onDlgUserAddOk, this );
		}

		this.active = 'add';
		this.dlgUserAdd.setTitle( mw.message( 'bs-usermanager-titleadduser' ).plain() );
		this.dlgUserAdd.tfUserName.enable();
		this.dlgUserAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		var selectedRows = this.grdMain.getSelectionModel().getSelection();
		if( selectedRows.length > 1 ) { //Multiselect
			if ( !this.dlgUserGroups ) {
				this.dlgUserGroups = new BS.UserManager.dialog.UserGroups({
					strGroups:this.strGroups
				});
				this.dlgUserGroups.on( 'ok', this.onDlgUserGroupsOk, this );
			}

			this.active = 'edit-multi-groups';
			this.dlgUserGroups.setData( { groups: [] } );
			this.dlgUserGroups.show();
		}
		else { //Single select
			if ( !this.dlgUserEdit ) {
				this.dlgUserEdit = new BS.UserManager.dialog.User({
					strGroups:this.strGroups
				});
				this.dlgUserEdit.on( 'ok', this.onDlgUserEditOk, this );
			}

			this.active = 'edit';
			this.dlgUserEdit.setTitle( mw.message( 'bs-usermanager-titleeditdetails' ).plain() );
			this.dlgUserEdit.tfUserName.disable();
			this.dlgUserEdit.setData( selectedRows[0].getData() );
			this.dlgUserEdit.show();
		}

		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		bs.util.confirm(
			'UMremove',
			{
				text: mw.message(
					'bs-usermanager-confirmdeleteuser',
					this.grdMain.getSelectionModel().getSelection().length
				).text(),
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
		//TODO: This needs to be Changed to one simple call!
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		for (var i = 0; i < selectedRow.length; i++){
			var me = this;
			bs.api.tasks.exec( 'usermanager', 'deleteUser', {
				userName: selectedRow[i].get( 'user_name' )
			}).done( function( response ) {
				me.reloadStore();
			});
		}
	},
	onDlgUserAddOk: function( data, user ) {
		var data = {
			userName: user.user_name,
			password: user.user_password,
			rePassword: user.user_repassword,
			email: user.user_email,
			realname: user.user_real_name,
			groups: user.groups
		};
		var me = this;
		var cfg = {//copy from bluespice.api.js
			failure: function( response, module, task, $dfd, cfg ) {
				var message = response.message || '';
				if ( response.errors.length > 0 ) {
					for ( var i in response.errors ) {
						if ( typeof( response.errors[i].message ) !== 'string' ) continue;
						message = message + '<br />' + response.errors[i].message;
					}
				}
				bs.util.alert( module + '-' + task + '-fail', {
						titleMsg: 'bs-extjs-title-warning',
						text: message
					}, {
						ok: function() {
							me.showDlgAgain();
					}}
				);
			}
		};
		bs.api.tasks.exec(
			'usermanager',
			'addUser',
			data,
			cfg
		).done( function( response ) {
			me.dlgUserAdd.resetData();
			me.reloadStore();
		});
	},
	onDlgUserEditOk: function( data, user ) {
		var data = {
			userName: user.user_name,
			password: user.user_password,
			rePassword: user.user_repassword,
			email: user.user_email,
			realname: user.user_real_name,
			groups: user.groups
		};
		var me = this;
		var cfg = {//copy from bluespice.api.js
			failure: function( response, module, task, $dfd, cfg ) {
				var message = response.message || '';
				if ( response.errors.length > 0 ) {
					for ( var i in response.errors ) {
						if ( typeof( response.errors[i].message ) !== 'string' ) continue;
						message = message + '<br />' + response.errors[i].message;
					}
				}
				bs.util.alert( module + '-' + task + '-fail', {
						titleMsg: 'bs-extjs-title-warning',
						text: message
					}, {
						ok: function() {
							me.showDlgAgain();
					}}
				);
			}
		};
		bs.api.tasks.exec(
			'usermanager',
			'editUser',
			data,
			cfg
		).done( function( response ) {
			me.dlgUserEdit.resetData();
			me.reloadStore();
		});
	},
	onDlgUserGroupsOk: function( sender, data ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var userNames = [];
		var me = this;
		for (var i = 0; i < selectedRow.length; i++){
			userNames.push( selectedRow[i].get( 'user_name' ) );
		}
		bs.api.tasks.exec( 'usermanager', 'setUserGroups', {
			userNames: userNames,
			groups: data.groups
		}).done( function( response ) {
			me.reloadStore();
		});
	},
	reloadStore: function() {
		this.strMain.reload();
	},
	showDlgAgain: function() {
		if ( this.active === 'add' ) {
			this.dlgUserAdd.show();
		} else if ( this.active === 'edit' ) {
			this.dlgUserEdit.show();
		} else if ( this.active === 'edit-multi-groups' ) {
			this.dlgUserGroups.show();
		}
	}
} );