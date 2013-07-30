/**
 * GroupManager extension
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-network.hk>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    1.0.0
 * @version    $Id: GroupManager.js 9755 2013-06-17 07:45:23Z pwirth $
 * @package    Bluespice_Extensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
BsGroupManager = {
	i18n: {
		headerGroupname: 'Groups',
		headerActions: 'Actions',
		btnAddGroup: 'Add group',
		tipEdit: 'Rename group',
		tipRemove: 'Remove group',
		titleNewGroup: 'New group',
		titleEditGroup: 'Rename group',
		titleError: 'Error',
		removeGroup: 'Are you sure you want to remove this group?',
		lableName: 'Group name:',
		msgNotEditable: 'This group is a system group and cannot be renamed.'
	},
	panel: false,
	store: new Ext.data.ArrayStore({
		autoLoad: true,
		fields: [
		'name', {
			name: 'editable',
			type: 'boolean'
		}
		],
		url: BsCore.buildRemoteString('GroupManager', 'getData'),
		sortInfo: {
			field: 'name',
			direction: 'ASC'
		}
	}),
	show: function() {
		if(!this.panel) {
			this.panel = new Ext.grid.GridPanel({
				border: false,
				id: 'bs-groupmanager-table',
				renderTo: 'bs-groupmanager-grid',
				store: this.store,
				colModel: new Ext.grid.ColumnModel({
					columns: [
					{
						id: 'name',
						header: this.i18n.headerGroupname,
						sortable: true,
						dataIndex: 'name'
					}, {
						xtype: 'actioncolumn',
						header: this.i18n.headerActions,
						width: 100,
						dataIndex: 'editable',
						items: [
						{
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config_tn.png',
							tooltip: this.i18n.tipEdit,
							handler: function(grid, rowIndex, colIndex) {
								this.showEditGroup(this.store.getAt(rowIndex));
							},
							scope: this
						}, {
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete_tn.png',
							tooltip: this.i18n.tipRemove,
							handler: function(grid, rowIndex, colIndex) {
								this.showRemoveGroup(this.store.getAt(rowIndex));
							},
							scope: this
						}
						]
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
								Ext.getCmp('btnGroupEdit').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config.png');
								Ext.getCmp('btnGroupEdit').enable();
								Ext.getCmp('btnGroupRemove').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete.png');
								Ext.getCmp('btnGroupRemove').enable();
							} else {
								Ext.getCmp('btnGroupEdit').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png');
								Ext.getCmp('btnGroupEdit').disable();
								Ext.getCmp('btnGroupRemove').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete-0.png');
								Ext.getCmp('btnGroupRemove').disable();
							}
						}
					}
				}),
				columnLines: true,
				enableHdMenu: false,
				stripeRows: true,
				autoHeight: true,
				tbar: new Ext.Toolbar({
					style: {
						backgroundColor: '#FFFFFF',
						backgroundImage: 'none'
					},
					items: [{
						tooltip: this.i18n.btnAddGroup,
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_add.png',
						iconCls: 'btn44',
						id: 'btnGroupAdd',
						handler: this.showAddGroup,
						scope: this
					}, {
						xtype: 'tbseparator',
						height: 44,
						cls: 'sep44'
					}, {
						tooltip: this.i18n.tipEdit,
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png',
						iconCls: 'btn44',
						id: 'btnGroupEdit',
						disabled: true,
						handler: function(btn, ev) {
							this.showEditGroup(this.panel.getSelectionModel().getSelected());
						},
						scope: this
					}, {
						tooltip: this.i18n.tipRemove,
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete-0.png',
						iconCls: 'btn44',
						id: 'btnGroupRemove',
						disabled: true,
						handler: function(btn, ev) {
							this.showRemoveGroup(this.panel.getSelectionModel().getSelected());
						},
						scope: this
					}]
				})
			});
		}
		this.panel.show();
	},
	// TODO MRG (30.06.11 11:00): Merge showAddGroup and showEditGroup
	showAddGroup: function() {
		Ext.Msg.prompt(BsGroupManager.i18n.titleNewGroup, BsGroupManager.i18n.lableName, function(btn, text){
			if (btn == 'ok'){
				Ext.Ajax.request({
					url: BsCore.buildRemoteString('GroupManager', 'addGroup'),
					success: function(response, opts) {
						var obj = Ext.decode(response.responseText);
						if(obj.success) {
							BsGroupManager.panel.store.load();
						}
						else {
							Ext.Msg.alert(this.i18n.titleError, obj.msg);
						}
					},
					failure: function(response, opts) {
						// CR MRG (30.06.11 11:01): Keine Console.logs!!
						//console.log('server-side failure with status code ' + response.status);
					},
					params: {
						group: text
					},
					scope: this
				});
			}
		}, this);
	},
	showEditGroup: function(record) {
		if(!record.data.editable) {
			Ext.Msg.alert('', this.i18n.msgNotEditable);
		}
		else {
			Ext.Msg.prompt(this.i18n.titleEditGroup, this.i18n.lableName, function(btn, text){
				if (btn == 'ok'){
					Ext.Ajax.request({
						url: BsCore.buildRemoteString('GroupManager', 'editGroup'),
						success: function(response, opts) {
							var obj = Ext.decode(response.responseText);
							if(obj.success) {
								BsGroupManager.panel.store.load();
							}
							else {
								Ext.Msg.alert(this.i18n.titleError, obj.msg);
							}
						},
						failure: function(response, opts) {
							// CR MRG (30.06.11 11:01): Keine Console.logs!!
							//console.log('server-side failure with status code ' + response.status);
						},
						params: {
							group: record.data.name,
							newgroup: text
						},
						scope: this
					});
				}
			}, this);
		}
	},
	selectedRecord: false,
	showRemoveGroup: function(record) {
		this.selectedRecord = record;
		if(!record.data.editable) {
			Ext.Msg.alert('', this.i18n.msgNotEditable);
		}
		else {
			Ext.Msg.confirm(this.i18n.tipRemove, this.i18n.removeGroup, this.doRemoveGroup, this);
		}
	},
	doRemoveGroup: function(btnId) {
		if( btnId == 'no' ) return;
		Ext.Ajax.request({
				url: BsCore.buildRemoteString('GroupManager', 'removeGroup'),
				success: function(response, opts) {
					var obj = Ext.decode(response.responseText);
					if(obj.success) {
						BsGroupManager.panel.store.load();
					}
					else {
						Ext.Msg.alert(this.i18n.titleError, obj.msg);
					}
				},
				failure: function(response, opts) {
					// CR MRG (30.06.11 11:01): Keine Console.logs!!
					//console.log('server-side failure with status code ' + response.status);
				},
				params: {
					group: this.selectedRecord.data.name
				}
		});
	}
}

Ext.onReady(function() {
	BsGroupManager.show();
}, window, { delay: 500 });
