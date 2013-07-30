// Last review MRG (01.07.11 15:36)
BsUserManager = {
	panel: false,
	store: new Ext.data.JsonStore({
		autoDestroy: true,
		url: BlueSpice.buildRemoteString('UserManager', 'getUsers'),
		storeId: 'BsUserManagerStore',
		root: 'users',
		remoteSort: true,
		fields: [ 'user_id', 'user_name', 'user_page', 'user_real_name', 'user_email', 'groups' ],
		totalProperty: 'totalCount'
	}),
	bbar: null, //Needed to access it in page size textfield listener
	pageSize: 25,
	show: function() {
		if(!this.panel) {
			this.bbar = new Ext.PagingToolbar({
				displayMsg: mw.message('bs-usermanager-showEntries').plain(),
				store: this.store,
				displayInfo: true,
				pageSize: this.pageSize,
				stateful: false,
				items: [
					'-',
					mw.message('bs-usermanager-pageSize').plain(),
					new Ext.form.TextField({
					width: 30,
					style: 'text-align: right',
					value: this.pageSize,
					enableKeyEvents: true,
					listeners: {
						keydown: function(t,e){
						//HINT: http://ssenblog.blogspot.de/2009/12/extjs-grid-dynamic-page-size.html
						if( e.getKey() != 13) return;

						BsUserManager.bbar.cursor = 0;
						BsUserManager.bbar.pageSize = parseInt(t.getValue());
						BsUserManager.store.load({
								params: {
								start: 0,
								limit: parseInt(t.getValue())
							}
						});
						}
					}
					})
				]
			}),
			this.panel = new Ext.grid.GridPanel({
				renderTo: 'UserManagerGrid',
				id: 'umGrid',
				plugins: (Ext.isIE ? [] : ['fittoparent']),
				loadMask: true,  
				store: this.store,
				tbar: new Ext.Toolbar({
					style: {
						backgroundColor: '#FFFFFF',
						backgroundImage: 'none'
					},
					items: [{
						tooltip: mw.message('bs-usermanager-titleAddUser').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_useradd.png',
						iconCls: 'btn44',
						id: 'btnUserAdd',
						handler: function(btn, ev) {
							this.showAddUser();
						},
						scope: this
					}, {
						xtype: 'tbseparator',
						height: 44,
						cls: 'sep44'
					}, {
						tooltip: mw.message('bs-usermanager-titleEditDetails').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png',
						iconCls: 'btn44',
						id: 'btnUserConfig',
						disabled: true,
						handler: function(btn, ev) {
							this.showEditUser(this.panel.getSelectionModel().getSelected());
						},
						scope: this
					}, {
						tooltip: mw.message('bs-usermanager-titleEditPassword').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_pass-0.png',
						iconCls: 'btn44',
						id: 'btnUserPass',
						disabled: true,
						handler: function(btn, ev) {
							this.showEditPassword(this.panel.getSelectionModel().getSelected());
						},
						scope: this
					}, {
						tooltip: mw.message('bs-usermanager-titleEditGroups').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_groups-0.png',
						iconCls: 'btn44',
						id: 'btnUserGroups',
						disabled: true,
						handler: function(btn, ev) {
							this.showEditGroups(this.panel.getSelectionModel().getSelected());
						},
						scope: this
					}, {
						xtype: 'tbseparator',
						height: 44,
						cls: 'sep44'
					}, {
						tooltip: mw.message('bs-usermanager-titleDeleteUser').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_userremove-0.png',
						iconCls: 'btn44',
						id: 'btnUserRemove',
						disabled: true,
						handler: function(btn, ev) {
							if (this.panel.getSelectionModel().getSelected() ) {
								this.showDeleteUser(this.panel.getSelectionModel().getSelected());
							}
						},
						scope: this
					}
					]
				}),
				bbar: this.bbar,
				colModel: new Ext.grid.ColumnModel({
					columns: [
					{
						id: 'username',
						header: mw.message('bs-usermanager-headerUsername').plain(),
						xtype: 'templatecolumn',
						sortable: true,
						dataIndex: 'user_name',
						tpl: '<a href="{user_page}">{user_name}</a>'
					}, {
						id: 'userrealname',
						header: mw.message('bs-usermanager-headerRealname').plain(),
						sortable: true,
						dataIndex: 'user_real_name'
					}, {
						id: 'useremail',
						header: mw.message('bs-usermanager-headerEmail').plain(),
						xtype: 'templatecolumn',
						sortable: true,
						dataIndex: 'user_email',
						tpl: '<a href="mailto:{user_email}">{user_email}</a>'
					}, {
						header: mw.message('bs-usermanager-headerGroups').plain(),
						xtype: 'templatecolumn',
						tpl: new Ext.XTemplate(
							'<ul style="list-style-type: none; list-style-image: none;">',
							'<tpl for="groups">',
							'<li>{.}</li>',
							'</tpl></ul>'
							),
						sortable: false
					}, {
						header: mw.message('bs-usermanager-headerActions').plain(),
						xtype: 'actioncolumn',
						width: 100,
						cls: 'hideAction',
						items: [
						{
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config_tn.png',
							tooltip: mw.message('bs-usermanager-tipEditDetails').plain(),
							handler: function(grid, rowIndex, colIndex) {
								this.showEditUser(this.store.getAt(rowIndex));
							},
							scope: this
						}, {
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_pass_tn.png',
							tooltip: mw.message('bs-usermanager-tipEditPass').plain(),
							handler: function(grid, rowIndex, colIndex) {
								this.showEditPassword(this.store.getAt(rowIndex));
							},
							scope: this
						}, {
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_groups_tn.png',
							tooltip: mw.message('bs-usermanager-tipEditGroups').plain(),
							handler: function(grid, rowIndex, colIndex) {
								this.showEditGroups(this.store.getAt(rowIndex));
							},
							scope: this
						}, {
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_userremove_tn.png',
							tooltip: mw.message('bs-usermanager-tipDeleteUser').plain(),
							handler: function(grid, rowIndex, colIndex) {
								this.showDeleteUser(this.store.getAt(rowIndex));
							},
							scope: this
						}
						],
						sortable: false
					}
					]
				}),
				viewConfig: {
					forceFit: true
				},
				sm: new Ext.grid.RowSelectionModel({
					singleSelect: true,
					listeners: {
						'selectionchange': function( sm ) {
							if (sm.hasSelection()) {
								Ext.getCmp('btnUserConfig').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config.png');
								Ext.getCmp('btnUserConfig').enable();
								Ext.getCmp('btnUserPass').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_pass.png');
								Ext.getCmp('btnUserPass').enable();
								Ext.getCmp('btnUserGroups').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_groups.png');
								Ext.getCmp('btnUserGroups').enable();
								Ext.getCmp('btnUserRemove').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_userremove.png');
								Ext.getCmp('btnUserRemove').enable();
							} else {
								Ext.getCmp('btnUserConfig').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png');
								Ext.getCmp('btnUserConfig').disable();
								Ext.getCmp('btnUserPass').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_pass-0.png');
								Ext.getCmp('btnUserPass').disable();
								Ext.getCmp('btnUserGroups').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_groups-0.png');
								Ext.getCmp('btnUserGroups').disable();
								Ext.getCmp('btnUserRemove').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_userremove-0.png');
								Ext.getCmp('btnUserRemove').disable();
							}
						}
					}

				}),
				border: false,
				columnLines: true,
				enableHdMenu: false,
				stripeRows: true,
				autoHeight: true
			});
			this.store.load();
		}
		this.panel.show();
	},
	window: false,
	showAddUser: function() {
		this.window = new Ext.Window({
			title: mw.message('bs-usermanager-titleAddUser').plain(),
			modal: true,
			width: 350,
			items: [
			{
				xtype: 'form',
				id: 'panelFormAddUser',
				labelWidth: 130,
				padding: 10,
				border: false,
				defaults: {
					msgTarget: 'under'
				},
				items: [
				{
					xtype: 'textfield',
					fieldLabel: mw.message('bs-usermanager-labelUsername').plain(),
					name: 'username',
					allowBlank: false
				}, {
					xtype: 'field',
					inputType: 'password',
					fieldLabel: mw.message('bs-usermanager-labelNewPassword').plain(),
					name: 'pass',
					allowBlank: false
				}, {
					xtype: 'field',
					inputType: 'password',
					fieldLabel: mw.message('bs-usermanager-labelPasswordCheck').plain(),
					name: 'repass',
					allowBlank: false
				}, {
					xtype: 'textfield',
					fieldLabel: mw.message('bs-usermanager-labelEmail').plain(),
					name: 'email'
				}, {
					xtype: 'textfield',
					fieldLabel: mw.message('bs-usermanager-labelRealname').plain(),
					name: 'realname'
				}
				]
			}
			],
			buttons: [
			{
				text: mw.message('bs-usermanager-btnOk').plain(),
				id: 'btnAddUserOK',
				handler: function(){
					Ext.getCmp('panelFormAddUser').getForm().doAction('submit', {
						clientValidation: false,
						submitEmptyText: false,
						method: 'post',
						url: BlueSpice.buildRemoteString('UserManager', 'doAddUser'),
						success: function(form, action) {
							this.store.reload();
							this.window.close();
							if(action.result.messages.length) {
								var tmp = '';
								for(var i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
							}
						},
						failure: function(form, action) {
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
							}
						},
						scope: this
					});
				},
				scope: this
			}, {
				text: mw.message('bs-usermanager-btnCancel').plain(),
				id: 'btnAddUserCancel',
				handler: function(){
					this.ownerCt.ownerCt.close();
				}
			}
			]
		});
		this.window.show();
	},
	showEditUser: function(record) {
		if(record.get('user_name') == wgUserName) {
			Ext.Msg.alert(mw.message('bs-usermanager-titleError').plain(), mw.message('bs-usermanager-textCannotEditOwn').plain());
			return;
		}
		this.window = new Ext.Window({
			title: mw.message('bs-usermanager-titleEditDetails').plain(),
			modal: true,
			width: 350,
			items: [
			{
				xtype: 'form',
				id: 'panelFormEditUser',
				labelWidth: 130,
				padding: 10,
				border: false,
				defaults: {
					msgTarget: 'under'
				},
				items: [
				    {
					    xtype: 'textfield',
					    fieldLabel: mw.message('bs-usermanager-labelUsername').plain(),
					    value: record.get('user_name'),
					    name: 'username',
					    allowBlank: false
				    }, {
					    xtype: 'textfield',
					    fieldLabel: mw.message('bs-usermanager-labelEmail').plain(),
					    value: record.get('user_email'),
					    name: 'email'
				    }, {
					    xtype: 'textfield',
					    fieldLabel: mw.message('bs-usermanager-labelRealname').plain(),
					    value: record.get('user_real_name'),
					    name: 'realname'
				    }, {
					    xtype: 'checkbox',
					    fieldLabel: mw.message('bs-usermanager-labelChangetext').plain(),
					    name: 'changetext',
					    value: 1
				    }, {
					    xtype: 'hidden',
					    name: 'user',
					    value: record.get('user_id')
				    }
				]
			}
			],
			buttons: [
			{
				text: mw.message('bs-usermanager-btnOk').plain(),
				id: 'btnEditUserOK',
				handler: function(){
					Ext.getCmp('panelFormEditUser').getForm().doAction('submit', {
						clientValidation: false,
						submitEmptyText: false,
						method: 'post',
						url: BlueSpice.buildRemoteString('UserManager', 'doEditUser'),
						success: function(form, action) {
							this.store.reload();
							this.window.close();
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
							}
						},
						failure: function(form, action) {
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
							}
						},
						scope: this
					});
				},
				scope: this
			}, {
				text: mw.message('bs-usermanager-btnCancel').plain(),
				id: 'btnEditUserCancel',
				handler: function(){
					this.ownerCt.ownerCt.close();
				}
			}
			]
		});
		this.window.show();
	},
	showEditPassword: function(record) {
		this.window = new Ext.Window({
			title: mw.message('bs-usermanager-titleEditPassword').plain(),
			modal: true,
			width: 350,
			items: [
			{
				xtype: 'form',
				id: 'panelFormEditPassword',
				labelWidth: 130,
				padding: 10,
				border: false,
				items: [
				{
					xtype: 'field',
					inputType: 'password',
					fieldLabel: mw.message('bs-usermanager-labelNewPassword').plain(),
					name: 'newpass',
					allowBlank: false
				}, {
					xtype: 'field',
					inputType: 'password',
					fieldLabel: mw.message('bs-usermanager-labelPasswordCheck').plain(),
					name: 'newrepass',
					allowBlank: false
				}, {
					xtype: 'hidden',
					name: 'user',
					value: record.get('user_id')
				}
				]
			}
			],
			buttons: [
			{
				text: mw.message('bs-usermanager-btnOk').plain(),
				handler: function(){
					Ext.getCmp('panelFormEditPassword').getForm().doAction('submit', {
						clientValidation: false,
						submitEmptyText: false,
						method: 'post',
						url: BlueSpice.buildRemoteString('UserManager', 'doEditPassword'),
						success: function(form, action) {
							this.window.close();
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
							}
						},
						failure: function(form, action) {
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
							}
						},
						scope: this
					});
				},
				scope: this
			}, {
				text: mw.message('bs-usermanager-btnCancel').plain(),
				handler: function(){
					this.ownerCt.ownerCt.close();
				}
			}
			]
		});
		this.window.show();
	},
	showEditGroups: function(record) {
		this.window = new Ext.Window({
			store: new Ext.data.JsonStore({
				autoDestroy: true,
				url: BlueSpice.buildRemoteString('UserManager', 'getUserGroups', {
					user: record.get('user_id')
				}),
				storeId: 'BsUserManagerGroupStore',
				root: 'groups',
				fields: [ 'groupname', 'group', 'member' ],
				listeners: {
					'load': {
						fn: function(store, records, options) {
							var panelForm = Ext.getCmp('panelFormEditGroups');
							for(i in records) {
								if(typeof(records[i]) != 'object') {
									continue;
								}
								panelForm.add(new Ext.form.Checkbox({
									boxLabel: records[i].data.groupname,
									name: 'groups[]',
									checked: records[i].data.member,
									inputValue: records[i].data.group
								}));
							}
							this.window.show();
						},
						scope: this
					},
					'exception': {
						fn: function( proxy, type, action, options, response ) {
							if(type == 'remote' && options.reader.jsonData.message != '') {
								Ext.Msg.alert(mw.message('bs-usermanager-titleError').plain(), options.reader.jsonData.message);
							}
							else {
								Ext.Msg.alert(mw.message('bs-usermanager-titleError').plain(), mw.message('bs-usermanager-unknownError').plain());
							}
						},
						scope: this
					}
				}
			}),
			title: mw.message('bs-usermanager-titleEditGroups').plain(),
			modal: true,
			width: 350,
			items: [
			{
				xtype: 'form',
				id: 'panelFormEditGroups',
				hideLabels: true,
				padding: 10,
				border: false,
				items: [
				{
					xtype: 'hidden',
					name: 'user',
					value: record.get('user_id')
				}
				]
			}
			],
			buttons: [
			{
				text: mw.message('bs-usermanager-btnOk').plain(),
				handler: function(){
					Ext.getCmp('panelFormEditGroups').getForm().doAction('submit', {
						clientValidation: false,
						submitEmptyText: false,
						method: 'post',
						url: BlueSpice.buildRemoteString('UserManager', 'doEditGroups'),
						success: function(form, action) {
							this.store.reload();
							this.window.close();
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
							}
						},
						failure: function(form, action) {
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
							}
						},
						scope: this
					});
				},
				scope: this
			}, {
				text: mw.message('bs-usermanager-btnCancel').plain(),
				handler: function(){
					this.ownerCt.ownerCt.close();
				}
			}
			]
		});
		this.window.store.load();
	},
	showDeleteUser: function(record) {
		if(record.get('user_name') == wgUserName) {
			Ext.Msg.alert(mw.message('bs-usermanager-titleError').plain(), mw.message('bs-usermanager-textCannotEditOwn').plain());
			return;
		}
		Ext.Msg.confirm(mw.message('bs-usermanager-titleDeleteUser').plain(), mw.message('bs-usermanager-confirmDeleteUser').plain(), function(btn) {
			if(btn == 'yes') {
				Ext.Ajax.request({
					method: 'post',
					params: {
						user: record.get('user_id')
					},
					url: BlueSpice.buildRemoteString('UserManager', 'doDeleteUser'),
					success: function(response, opts) {
						var obj = Ext.decode(response.responseText);
						BsUserManager.panel.store.reload();
						if(obj.messages.length) {
							var tmp = '';
							for(i in obj.messages) {
								if(typeof(obj.messages[i]) != 'string') {
									continue;
								}
								tmp = tmp + obj.messages[i] + '<br />';
							}
							Ext.Msg.alert('Status', tmp);
						}
					},
					failure: function(response, opts) {
						// CR MRG (01.07.11 15:38): Keine consolelogs, die gehen nicht im ie!
						//console.log('server-side failure with status code ' + response.status);
					},
					scope: this
				})
			}
		})
	}
}

Ext.onReady(function() {
	BsUserManager.show();
}, window, { delay: 500 });
