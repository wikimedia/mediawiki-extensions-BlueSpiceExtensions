/**
 * InterWikiManager extension
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbrich
 * @version    0.1 beta
 * @version    $Id: bluespice.interWikiLinks.js 9907 2013-06-25 08:52:25Z rvogel $
 * @package    Bluespice_Extensions
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
 
 /* Changelog
  * v0.1
  * - initial commit
  */
 
/**
 * Base class for all InterWikiLinks related methods and properties
 */
BsInterWikiManager = {

	/**
	 * Basic canvas to be rendered to
	 * @var Ext.grid.GridPanel
	 */
	panel: false,
	/**
	 * Store for interwiki data, displayed in grid.
	 * @var Ext.data.JsonStore
	 */
	store: new Ext.data.JsonStore({
		autoDestroy: true,
		url: BlueSpice.buildRemoteString('InterWikiLinks', 'getInterWikiLinks'),
		storeId: 'BsInterWikiManagerStore',
		root: 'iwlinks',
		fields: [
			'prefix',
			'url',
		],
		totalProperty: 'totalCount'
	}),
	/**
	 * Renders the interwiki link grid and fills it with data
	 */
	show: function() {
		if(!this.panel) {
			this.panel = new Ext.grid.GridPanel({
				renderTo: 'InterWikiManagerGrid',
				id: 'imGrid',
				plugins: (Ext.isIE ? [] : ['fittoparent']),
				loadMask: true,  
				store: this.store,
				tbar: new Ext.Toolbar({
					style: {
						backgroundColor: '#FFFFFF',
						backgroundImage: 'none'
					},
					items: [{
						tooltip: mw.message('bs-interwikilinks-titleAddInterWikiLink').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_add.png',
						iconCls: 'btn44',
						id: 'btnInterWikiAdd',
						handler: function(btn, ev) {
							this.showEditInterWikiLink(false, false);
						},
						scope: this
					}, {
						xtype: 'tbseparator',
						height: 44,
						cls: 'sep44'
					}, {
						tooltip: mw.message('bs-interwikilinks-titleEditDetails').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png',
						iconCls: 'btn44',
						id: 'btnInterWikiEdit',
						disabled: true,
						handler: function(btn, ev) {
							this.showEditInterWikiLink(this.panel.getSelectionModel().getSelected(), true);
						},
						scope: this
					}, {
						xtype: 'tbseparator',
						height: 44,
						cls: 'sep44'
					}, {
						tooltip: mw.message('bs-interwikilinks-titleDeleteInterWikiLink').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete-0.png',
						iconCls: 'btn44',
						id: 'btnInterWikiRemove',
						disabled: true,
						handler: function(btn, ev) {
							if (this.panel.getSelectionModel().getSelected() ) {
								this.showDeleteInterWikiLink(this.panel.getSelectionModel().getSelected());
							}
						},
						scope: this
					}
					]
				}),
				bbar: new Ext.PagingToolbar({
					displayMsg: mw.message('bs-interwikilinks-showEntries').plain(),
					store: this.store,
					displayInfo: true,
					pageSize: 25,
					prependButtons: true
				}),
				colModel: new Ext.grid.ColumnModel({
					columns: [
					{
						id: 'prefix',
						header: mw.message('bs-interwikilinks-headerPrefix').plain(),
						sortable: true,
						width: 60,
						dataIndex: 'prefix'
					}, {
						header: mw.message('bs-interwikilinks-headerUrl').plain(),
						sortable: true,
						dataIndex: 'url'
					}, {
						header: mw.message('bs-interwikilinks-headerActions').plain(),
						xtype: 'actioncolumn',
						width: 40,
						cls: 'hideAction',
						items: [
						{
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config_tn.png',
							tooltip: mw.message('bs-interwikilinks-tipEditInterWikiLink').plain(),
							handler: function(grid, rowIndex, colIndex) {
								this.showEditInterWikiLink(this.store.getAt(rowIndex), true);
							},
							scope: this
						}, {
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete_tn.png',
							tooltip: mw.message('bs-interwikilinks-tipDeleteInterWikiLink').plain(),
							handler: function(grid, rowIndex, colIndex) {
								this.showDeleteInterWikiLink(this.store.getAt(rowIndex));
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
								Ext.getCmp('btnInterWikiEdit').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config.png');
								Ext.getCmp('btnInterWikiEdit').enable();
								Ext.getCmp('btnInterWikiRemove').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete.png');
								Ext.getCmp('btnInterWikiRemove').enable();
							} else {
								Ext.getCmp('btnInterWikiEdit').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png');
								Ext.getCmp('btnInterWikiEdit').disable();
								Ext.getCmp('btnInterWikiRemove').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete-0.png');
								Ext.getCmp('btnInterWikiRemove').disable();
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
	/**
	 * Reference to the InterWiki Links Dialogue Window
	 * @var Window Ext.Window object
	 */
	window: false,
	/**
	 * Displays the interwiki link dialoge
	 * @param record Ext.data.Record an entry in the interwiki link store
	 * @param bool editmode Create new link (false) or edit existing link (true)
	 */
	showEditInterWikiLink: function(record, editmode) {
		this.window = new Ext.Window({
			title: editmode ? mw.message('bs-interwikilinks-titleEditInterWikiLink').plain() : mw.message('bs-interwikilinks-titleAddInterWikiLink').plain(),
			modal: true,
			width: 350,
			oldprefix: record?record.get('prefix'):'',
			items: [
			{
				xtype: 'form',
				id: 'panelFormEditInterWikiLink',
				labelWidth: 50,
				padding: 10,
				border: false,
				items: [
				{
					xtype: 'field',
					inputType: 'text',
					fieldLabel: mw.message('bs-interwikilinks-labelPrefix').plain(),
					name: 'iweditprefix',
					id: 'iweditprefix',
					allowBlank: false,
					width: 260,
					value: record?record.get('prefix'):''
				}, {
					xtype: 'field',
					inputType: 'text',
					fieldLabel: mw.message('bs-interwikilinks-labelUrl').plain(),
					name: 'iwediturl',
					allowBlank: false,
					width: 260,
					value: record?record.get('url'):''
				}
				]
			}
			],
			buttons: [
			{
				text: mw.message('bs-interwikilinks-btnOk').plain(),
				handler: function(){
					Ext.getCmp('panelFormEditInterWikiLink').getForm().doAction('submit', {
						clientValidation: false,
						submitEmptyText: false,
						method: 'post',
						url: BlueSpice.buildRemoteString('InterWikiLinks', 'doEditInterWikiLink', {"iweditmode":editmode,"iweditoldprefix":this.window.oldprefix}),
						success: function(form, action) {
							this.window.close();
							if(action.result.messages.length) {
								BsInterWikiManager.panel.store.reload();
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
				text: mw.message('bs-interwikilinks-btnCancel').plain(),
				handler: function(){
					this.ownerCt.ownerCt.close();
				}
			}
			]
		});
		this.window.show();
	},
	/**
	 * Displays the interwiki link deletion dialoge
	 * @param record Ext.data.Record an entry in the interwiki link store
	 */
	showDeleteInterWikiLink: function(record) {
		confirmMessage = mw.message('bs-interwikilinks-confirmDeleteInterWikiLink').plain() 
				+ "<br>" + "<br>"
				+ "<b>" + mw.message('bs-interwikilinks-labelPrefix').plain() + ":</b> " + record.get('prefix')
				+ "<br>"
				+ "<b>" + mw.message('bs-interwikilinks-labelUrl').plain() + ":</b> " + record.get('url')
				+ "<br>";
		Ext.Msg.confirm(mw.message('bs-interwikilinks-titleDeleteInterWikiLink').plain(), confirmMessage, function(btn) {
			if(btn == 'yes') {
				Ext.Ajax.request({
					method: 'post',
					params: {
						deleteprefix: record.get('prefix')
					},
					url: BlueSpice.buildRemoteString('InterWikiLinks', 'doDeleteInterWikiLink'),
					success: function(response, opts) {
						var obj = Ext.decode(response.responseText);
						BsInterWikiManager.panel.store.reload();
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
						// TODO MRG (04.07.11 10:41): No console logs, since they break IE
						//console.log('server-side failure with status code ' + response.status);
					},
					scope: this
				})
			}
		})
	}

}

Ext.onReady(function() {
	BsInterWikiManager.show();
}, window, { delay: 500 });