/**
 * PageTemplates extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: bluespice.pageTemplates.js 9900 2013-06-25 06:38:06Z rvogel $
 * @package    Bluespice_Extensions
 * @subpackage PageTemplates
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Currently, there is a dependency to InsertLink in order to get Page Namespaces
 */

/**
 * Base class for all page template related methods and properties
 */
BsPageTemplatesAdmin = {
	/**
	 * Basic canvas to be rendered to
	 * @var Ext.grid.GridPanel
	 */
	panel: false,
	/**
	 * Store for template data, displayed in grid.
	 * @var Ext.data.JsonStore
	 */
	store: new Ext.data.JsonStore({
		autoDestroy: true,
		url: BlueSpice.buildRemoteString('PageTemplatesAdmin', 'getTemplates'),
		storeId: 'BsPageTemplatesAdminStore',
		root: 'templates',
		fields: [
			'id',
			'label',
			'desc',
			'targetns',
			'targetnsid',
			'template',
			'templatename',
			'templatens'
		],
		totalProperty: 'totalCount',
		remoteSort: true,
		sortInfo: {
			field: 'label',
			direction: 'ASC' // or 'DESC' (case sensitive for local sorting)
		}
	}),
	// TODO RBV (18.05.11 09:32): Dependency on InsertLink!
	/**
	 * Store for pages, needed for template editing
	 * @var Ext.data.JsonStore
	 */
	storePages: new Ext.data.JsonStore({
		url: BlueSpice.buildRemoteString('InsertLink', 'getPage', {ns:10}),
		root: 'items',
		fields: ['name', 'label', 'ns']
	}),
	/**
	 * Store for namespaces, neede for template editing, target namespace dropdown field
	 * @var Ext.data.JsonStore
	 */
	storeTargetNamespaces: new Ext.data.JsonStore({
		url: BlueSpice.buildRemoteString('PageTemplatesAdmin', 'getNamespaces', {showAll:1}),
		root: 'items',
		fields: ['name', 'id']
	}),
	/**
	 * Store for namespaces, neede for template editing, template namespace dropdown field
	 * @var Ext.data.JsonStore
	 */
	storeTemplateNamespaces: new Ext.data.JsonStore({
		url: BlueSpice.buildRemoteString('PageTemplatesAdmin', 'getNamespaces'),
		root: 'items',
		fields: ['name', 'id']
	}),
	/**
	 * Renders the page templates grid and fills it with data
	 */
	show: function() {
		if(!this.panel) {
			this.panel = new Ext.grid.GridPanel({
				renderTo: 'bs-pagetemplates-admingrid',
				id: 'ptaGrid',
				plugins: (Ext.isIE ? [] : ['fittoparent']),
				loadMask: true,  
				store: this.store,
				tbar: new Ext.Toolbar({
					style: {
						backgroundColor: '#FFFFFF',
						backgroundImage: 'none'
					},
					
					items: [{
						tooltip: mw.message('bs-pagetemplates-tipAddTemplate').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_add.png',
						iconCls: 'btn44',
						handler: function(btn, ev) {
							this.showEditTemplate( null );
						},
						id: 'ptaAddTemplate',
						scope: this
					}, {
						xtype: 'tbseparator',
						height: 44,
						cls: 'sep44'
					}, {
						id: 'ptaEditTemplate',
						disabled: true,
						tooltip: mw.message('bs-pagetemplates-tipEditDetails').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config.png',
						iconCls: 'btn44',
						handler: function(btn, ev) {
							this.showEditTemplate(this.panel.getSelectionModel().getSelected());
						},
						scope: this
					}, {
						xtype: 'tbseparator',
						height: 44,
						cls: 'sep44'
					}, {
						id: 'ptaDeleteTemplate',
						disabled: true,
						tooltip: mw.message('bs-pagetemplates-tipDeleteTemplate').plain(),
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete.png',
						iconCls: 'btn44',
						handler: function(btn, ev) {
							if (this.panel.getSelectionModel().getSelected() ) {
								this.showDeleteTemplate(this.panel.getSelectionModel().getSelected());
							}
						},
						scope: this
					}
					]
				}),
				bbar: new Ext.PagingToolbar({
					displayMsg: mw.message('bs-pagetemplates-showEntries').plain(),
					store: this.store,
					displayInfo: true,
					pageSize: 25,
					prependButtons: true
				}),
				colModel: new Ext.grid.ColumnModel({
					columns: [
					{
						id: 'templatename',
						header: mw.message('bs-pagetemplates-headerLabel').plain(),
						sortable: true,
						width: 60,
						dataIndex: 'label'
					}, {
						header: mw.message('bs-pagetemplates-headerDescription').plain(),
						sortable: true,
						dataIndex: 'desc'
					}, {
						header: mw.message('bs-pagetemplates-headerTargetNamespace').plain(),
						sortable: true,
						width: 60,
						dataIndex: 'targetns'
					}, {
						header: mw.message('bs-pagetemplates-headerTemplate').plain(),
						sortable: true,
						width: 60,
						dataIndex: 'template'
					}, {
						header: mw.message('bs-pagetemplates-headerActions').plain(),
						xtype: 'actioncolumn',
						width: 40,
						cls: 'hideAction',
						items: [
						{
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config_tn.png',
							tooltip: mw.message('bs-pagetemplates-tipEditDetails').plain(),
							handler: function(grid, rowIndex, colIndex) {
								this.showEditTemplate(this.store.getAt(rowIndex));
							},
							scope: this
						}, {
							icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete_tn.png',
							tooltip: mw.message('bs-pagetemplates-tipDeleteUser').plain(),
							handler: function(grid, rowIndex, colIndex) {
								this.showDeleteTemplate(this.store.getAt(rowIndex));
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
								Ext.getCmp('ptaEditTemplate').enable();
								Ext.getCmp('ptaDeleteTemplate').enable();
							} else {
								Ext.getCmp('ptaEditTemplate').disable();
								Ext.getCmp('ptaDeleteTemplate').disable();
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
		this.storePages.load();
		this.storeTargetNamespaces.load();
		this.storeTemplateNamespaces.load();
	},
	/**
	 * Reference to the Page Templates Window
	 * @var Window Ext.Window object
	 */
	window: false,

	/**
	 * Displays the edit template dialoge
	 * @param record Ext.data.Record an entry in the template store
	 */
	showEditTemplate: function(record) {

		this.window = new Ext.Window({
			title: mw.message('bs-pagetemplates-titleEditDetails').plain(),
			modal: true,
			width: 350,
			items: [
			{
				xtype: 'form',
				id: 'panelFormEditTemplate',
				labelWidth: 120,
				padding: 10,
				border: false,
				defaults: {
					msgTarget: 'under'
				},
				items: [
				{
					xtype: 'textfield',
					fieldLabel: mw.message('bs-pagetemplates-labelLabel').plain(),
					value: (record==null?'':record.get('label')),
					width: 180,
					name: 'label'//,
					//allowBlank: false
				}, {
					xtype: 'textarea',
					fieldLabel: mw.message('bs-pagetemplates-labelDescription').plain(),
					value: (record==null?'':record.get('desc')),
					width: 180,
					name: 'desc'
				}, {
					xtype: 'combo',
					flex: 1,
					width: 180,
					store: this.storeTargetNamespaces,
					displayField:'name',
					valueField:'id',
					hiddenName: 'targetNs',
					typeAhead: true,
					mode: 'local',
					triggerAction: 'all',
					//emptyText:this.config.i18n.select_a_page,
					lastQuery: '',
					fieldLabel: mw.message('bs-pagetemplates-labelTargetNamespace').plain(),
					name: 'targetNamespaceName',
					hiddenValue: (record==null?-99:record.get('targetnsid')),
					value: (record==null?-99:record.get('targetnsid'))
				}, {
					xtype: 'combo',
					flex: 1,
					width: 180,
					store: this.storeTemplateNamespaces,
					displayField:'name',
					valueField:'id',
					hiddenName: 'templateNs',
					typeAhead: true,
					mode: 'local',
					triggerAction: 'all',
					//emptyText:this.config.i18n.select_a_page,
					lastQuery: '',
					fieldLabel: mw.message('bs-pagetemplates-labelTemplateNamespace').plain(),
					hiddenValue: (record==null?10:record.get('templatens')),
					value: (record==null?10:record.get('templatens')),
					name: 'templateNamespace',
					listeners: {
						'select': {
							fn:function(sel, value) {
								this.storePages.load({
									params:{
										ns: sel.getValue()
									}
								});
							},
							scope:this
						}
					}
				}, {
					xtype: 'combo',
					flex: 1,
					width: 180,
					store: this.storePages,
					displayField:'name',
					valueField:'label',
					//hiddenName: 'articleId',
					typeAhead: true,
					mode: 'local',
					triggerAction: 'all',
					//emptyText:this.config.i18n.select_a_page,
					lastQuery: '',
					fieldLabel: mw.message('bs-pagetemplates-labelArticle').plain(),
					name: 'templateName',
					//hiddenValue: (record==null?'':record.get('template')),
					value: (record==null?'':record.get('templatename'))
				}, {
					xtype: 'hidden',
					name: 'oldId',
					value: (record==null?'':record.get('id'))
				}
			    ]
			}
			],
			buttons: [
			{
				text: mw.message('bs-pagetemplates-btnOk').plain(),
				handler: function(){
					Ext.getCmp('panelFormEditTemplate').getForm().doAction('submit', {
						clientValidation: true,
						submitEmptyText: false,
						method: 'post',
						url: BlueSpice.buildRemoteString('PageTemplatesAdmin', 'doEditTemplate'),
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
							return true;
						},
						failure: function(form, action) {
							if(action.result.errors.length) {
								var tmp = '';
								for(i in action.result.errors) {
									if(typeof(action.result.errors[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.errors[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
							}
						},
						scope: this
					});
				},
				scope: this
			}, {
				text: mw.message('bs-pagetemplates-btnCancel').plain(),
				handler: function(){
					this.ownerCt.ownerCt.close();
				}
			}
			]
		});
		this.storePages.load();
		this.storeTargetNamespaces.load();
		this.storeTemplateNamespaces.load();
		this.window.show();
	},
	
	/**
	 * Displays the delete template dialoge
	 * @param record Ext.data.Record an entry in the template store
	 */
	showDeleteTemplate: function(record) {
		Ext.Msg.confirm(mw.message('bs-pagetemplates-titleDeleteTemplate').plain(), mw.message('bs-pagetemplates-confirmDeleteTemplate').plain(), function(btn) {
			if(btn == 'yes') {
				Ext.Ajax.request({
					method: 'post',
					params: {
						id: record.get('id')
					},
					url: BlueSpice.buildRemoteString('PageTemplatesAdmin', 'doDeleteTemplate'),
					success: function(response, opts) {
						var obj = Ext.decode(response.responseText);
						BsPageTemplatesAdmin.panel.store.reload();
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
					    // TODO RBV (18.05.11 09:46): Alert user?
					    //console.log('server-side failure with status code ' + response.status);
					},
					scope: this
				})
			}
		})
	}
}

setTimeout( "BsPageTemplatesAdmin.show()", 1 );