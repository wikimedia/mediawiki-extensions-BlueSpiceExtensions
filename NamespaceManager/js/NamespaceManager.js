BsNamespaceManager = {
	i18n: {
		headerNamespaceId: 'ID',
		headerNamespaceName: 'Namespaces',
		headerIsUserNamespace: 'System (non-editable)',
		headerIsContentNamespace: 'evaluable',
		headerIsSearchableNamespace: 'searchable',
		headerIsSubpagesNamespace: 'Subpages',
		headerActions: 'Actions',
		yes: 'yes',
		no: 'no',
		btnAddNamespace: 'Add namespace',
		tipEdit: 'Edit namespace',
		tipRemove: 'Remove namespace',
		msgNotEditable: 'This namespace is a systemnamespace and cannot be edited.',
		msgNotEditableDelete: 'This namespace is a systemnamespace and cannot be deleted.',
		titleNewNamespace: 'Add Namespace',
		labelNamespaceName: 'Namespace-Title',
		emptyMsgNamespaceName: 'Namespace title cannot be empty',
		labelContentNamespace: 'Evaluate namespace statistically',
		labelSearchedNamespace: 'Add namespace to standard search',
		labelSubpagesNamespace: 'Enable subpages',
		btnSave: 'Save',
		btnCancel: 'Cancel',
		titleError: 'Error',
		willDelete: '... will be deleted',
		willMove: '... will be moved to the Mainnamespace *',
		willMoveSuffix: '... will be moved to the Mainnamespace with the Suffix "(from <span class="removeWindowNamespaceName"></span>)"',
		sureDeletePt1: 'Are you sure that you want to delete namespace <b><span class="removeWindowNamespaceName"></span></b> ?',
		sureDeletePt2: 'Deleting a namespace can not be undone!',
		moveConflict: '*The namespace will be extended by the Suffix"(from <span class="removeWindowNamespaceName"></span>)" if a conflict occurs',
		articlesPresent: 'Other Articles present in this namespace ...',
		btnDelete: 'Delete',
		deleteNamespace: 'Delete Namespace',
		showEntries: 'Showing entries {0} - {1} of {2}',
		pageSize : 'Page size: '

	},
	panel: false,
	formElements: new Ext.util.MixedCollection(false, function(obj) {
		return obj.name;
	}),
	
	// TODO: ask if empty
	store: new Ext.data.JsonStore({
		autoLoad: false,
		url: BsCore.buildRemoteString('NamespaceManager', 'getData'),
		totalProperty:'total',
		remoteSort:true,
		root:'results',
		listeners: {
			'metachange': function(store, meta) {
				var grid = Ext.getCmp('bsNamespaceManagerGrid');
				var columns = [];
				var fields = new Ext.util.MixedCollection(false, function(obj) {
					return obj.name;
				});
				
				fields.addAll(meta.fields);
				fields.each(function(item, index, length) {
					if(item.name != 'id' && item.name != 'name' && item.name != 'editable') {
						BsNamespaceManager.formElements.add({
							boxLabel: item.label,
							hideLabel: true,
							name: item.name,
							inputValue: 1,
							checked: true
						});
					}
					var col = {
						header: item.label,
						sortable: false,
						dataIndex: item.name
					};
					if( item.sortable == true ) {
						col.sortable = true;
					}
					if(item.name == 'id') {
						col['width'] = 30;
					}
					if(item.name == 'name') {
						col['width'] = 140;
					}
					if(item.type == 'boolean') {
						col['renderer'] = function(value) {
							if(!value) {
								return '<img src="' + wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-cross.png' + '"/>';//BsNamespaceManager.i18n.no;
							}
							return '<img src="' + wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-tick.png' + '"/>';//BsNamespaceManager.i18n.yes;
						}
					}
					columns.push(col);
				});
				
				columns.push({
					xtype: 'actioncolumn',
					header: BsNamespaceManager.i18n.headerActions,
					width: 80,
					dataIndex: 'editable',
					items: [{
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config_tn.png',
						tooltip: BsNamespaceManager.i18n.tipEdit,
						handler: function(grid, rowIndex, colIndex) {
							BsNamespaceManager.showEditNamespace(BsNamespaceManager.store.getAt(rowIndex));
						}
					}, {
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete_tn.png',
						tooltip: BsNamespaceManager.i18n.tipRemove,
						handler: function(grid, rowIndex, colIndex) {
							var row = grid.store.getAt(rowIndex);
							if(row.data.editable == false) {
								return;
							}
							BsNamespaceManager.showRemoveNamespace(row);
						}
					}],
					renderer: function(value, metaData, record, rowIndex, colIndex, store) {
						if( record.data.editable == false ) {
							this.items[1].icon = wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete_tn-0.png';
							this.items[1].iconCls = 'no-pointer';
							this.items[1].tooltip = null;
						} else {
							this.items[1].icon = wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete_tn.png';
							this.items[1].tooltip = BsNamespaceManager.i18n.tipRemove;
							this.items[1].iconCls = '';
						}
					}
				});
				
				grid.reconfigure(store, new Ext.grid.ColumnModel(columns));
			}
		}
	}),
	cm: new Ext.grid.ColumnModel({}),
	bbar: null,
	pageSize: 25,
	show: function() {
		if(!this.panel) {
			this.bbar = new Ext.PagingToolbar({
				displayMsg: this.i18n.showEntries,
				store: this.store,
				displayInfo: true,
				pageSize: this.pageSize,
				stateful: false,
				items: [
					'-',
					this.i18n.pageSize,
					new Ext.form.TextField({
					width: 30,
					style: 'text-align: right',
					value: this.pageSize,
					enableKeyEvents: true,
					listeners: {
						keydown: function(t,e){
						//HINT: http://ssenblog.blogspot.de/2009/12/extjs-grid-dynamic-page-size.html
						if( e.getKey() != 13) return;

						BsNamespaceManager.bbar.cursor = 0;
						BsNamespaceManager.bbar.pageSize = parseInt(t.getValue());
						BsNamespaceManager.store.load({
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
				id: 'bsNamespaceManagerGrid',
				loadMask: true,
				border: false,
				renderTo: 'bs-namespacemanager-grid',
				store: this.store,
				colModel: this.cm,
				viewConfig: {
					forceFit: true
				},
				sm: new Ext.grid.RowSelectionModel({
					singleSelect: true,
					listeners: {
						'selectionchange': function( sm ) {
							if (sm.hasSelection()) {
								Ext.getCmp('btnNSEdit').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config.png');
								Ext.getCmp('btnNSEdit').enable();
								if( sm.getSelected().data.editable ) {
									Ext.getCmp('btnNSRemove').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete.png');
									Ext.getCmp('btnNSRemove').enable();
								} else {
									Ext.getCmp('btnNSRemove').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete-0.png');
									Ext.getCmp('btnNSRemove').disable();
								}
								
							} else {
								Ext.getCmp('btnNSEdit').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png');
								Ext.getCmp('btnNSEdit').disable();
								Ext.getCmp('btnNSRemove').setIcon(wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete-0.png');
								Ext.getCmp('btnNSRemove').disable();
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
						tooltip: this.i18n.btnAddNamespace,
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_add.png',
						iconCls: 'btn44',
						id: 'btnNSAdd',
						handler: this.showAddNamespace
					}, {
						xtype: 'tbseparator',
						height: 44,
						cls: 'sep44'
					}, {
						tooltip: this.i18n.tipEdit,
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png',
						iconCls: 'btn44',
						id: 'btnNSEdit',
						disabled: true,
						handler: function(btn, ev) {
							this.showEditNamespace(this.panel.getSelectionModel().getSelected());
						},
						scope: this
					}, {
						tooltip: this.i18n.tipRemove,
						icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-m_delete-0.png',
						iconCls: 'btn44',
						id: 'btnNSRemove',
						disabled: true,
						handler: function(btn, ev) {
							this.showRemoveNamespace(this.panel.getSelectionModel().getSelected());
						},
						scope: this
					}]
				}),
				bbar: this.bbar
			});
		}
		this.store.load();
		this.panel.show();
	},
	showAddWindow: false,
	showAddNamespace: function() {
		if(!BsNamespaceManager.showAddWindow) {
			BsNamespaceManager.form = new Ext.FormPanel({
				width: 400,
				labelWidth: 110,
				padding:5,
				id: 'bs-nm-dlg-add',
				items: [{
					xtype: 'textfield',
					width: 250,
					allowBlank: false,
					blankText: BsNamespaceManager.i18n.emptyMsgNamespaceName,
					msgTarget: 'under',
					fieldLabel: BsNamespaceManager.i18n.labelNamespaceName,
					name: 'name'
				}],
				buttons: [{
					text: BsNamespaceManager.i18n.btnSave,
					id: 'bs-nm-add-btn-save',
					handler: function() {
						BsNamespaceManager.form.form.submit({
							url: BsCore.buildRemoteString('NamespaceManager', 'addNamespace'),
							success: function(form, action) {
								var obj = action.result;
								BsNamespaceManager.panel.store.load();
								BsNamespaceManager.showAddWindow.hide();
							},
							failure: function(form, action) {
								var obj = action.result;
								Ext.Msg.alert(BsNamespaceManager.i18n.titleError, obj.msg);
							}
						});
					},
					scope: this
				},{
					text: BsNamespaceManager.i18n.btnCancel,
					id: 'bs-nm-add-btn-cancel',
					handler: function(){
						BsNamespaceManager.showAddWindow.hide();
					},
					scope: this
				}]
			});
			
			BsNamespaceManager.formElements.each(function(item) {
				BsNamespaceManager.form.add(new Ext.form.Checkbox(item));
			});

			BsNamespaceManager.showAddWindow = new Ext.Window({
				title: BsNamespaceManager.i18n.titleNewNamespace,
				items: BsNamespaceManager.form,
				width: 415,
				closeAction: 'hide',
				modal: true,
				resizable: false,
				keys: {
					key: 27,
					handler: function() {
						BsNamespaceManager.showAddWindow.hide();
					},
					scope: this
				}
			});
		}
		BsNamespaceManager.showAddWindow.show();
	},
	showEditWindow: false,

	showEditNamespace: function(record) {
//		if(!record.data.editable) {

			if(!BsNamespaceManager.showEditWindow) {
				BsNamespaceManager.editform = new Ext.FormPanel({
					width: 400,
					id: 'bs-nm-dlg-edit',
					labelWidth:110,
					padding:5,
					items: [{
						xtype: 'textfield',
						width: 250,
						id: 'editWindowName',
						allowBlank: false,
						blankText: BsNamespaceManager.i18n.emptyMsgNamespaceName,
						msgTarget: 'under',
						fieldLabel: BsNamespaceManager.i18n.labelNamespaceName,
						name: 'name'
					}, {
						xtype: 'hidden',
						id: 'editWindowId',
						name: 'id'
					}],
					buttons: [{
						text: BsNamespaceManager.i18n.btnSave,
						id: 'bs-nm-edit-btn-save',
						handler: function() {
							Ext.getCmp('editWindowName').enable();
							BsNamespaceManager.editform.form.submit({
								url: BsCore.buildRemoteString('NamespaceManager', 'editNamespace'),
								success: function(form, action) {
									var obj = action.result;
									BsNamespaceManager.panel.store.load();
									BsNamespaceManager.showEditWindow.hide();
								},
								failure: function(form, action) {
									var obj = action.result;
									Ext.Msg.alert(BsNamespaceManager.i18n.titleError, obj.msg);
								}
							});
						},
						scope: this
					},{
						text: BsNamespaceManager.i18n.btnCancel,
						id: 'bs-nm-edit-btn-cancel',
						handler: function(){
							this.showEditWindow.hide();
						},
						scope: this
					}]
				});
				
				BsNamespaceManager.formElements.each(function(item) {
					var edititem = item;
					edititem['id'] = 'editWindow' + edititem.name
					BsNamespaceManager.editform.add(new Ext.form.Checkbox(edititem));
				});

				BsNamespaceManager.showEditWindow = new Ext.Window({
					title: BsNamespaceManager.i18n.tipEdit,
					items: BsNamespaceManager.editform,
					closeAction: 'hide',
					width: 415,
					modal: true,
					resizable: false,
					keys: {
						key: 27,
						handler: function() {
							this.showEditWindow.hide();
						},
						scope: this
					}
				});
			}
			Ext.getCmp('editWindowName').setValue(record.data.name);
			Ext.getCmp('editWindowId').setValue(record.data.id);
			BsNamespaceManager.formElements.each(function(item) {
				Ext.getCmp('editWindow' + item.name).setValue(record.data[item.name]);
			});
			if(record.data.name.match(/_talk/) || !record.data.editable) {
				Ext.getCmp('editWindowName').disable();
			}
			else {
				Ext.getCmp('editWindowName').enable();
			}
			BsNamespaceManager.showEditWindow.show();

	},
	showRemoveWindow: false,
	showRemoveNamespace: function(record) {
		if(!record.data.editable) {
			Ext.Msg.alert('', this.i18n.msgNotEditableDelete);
		}
		else {
			if(!BsNamespaceManager.showRemoveWindow) {
				
				this.form = new Ext.FormPanel({
					id: 'bs-nm-dlg-delete',
					padding:5,
					items: [
					new Ext.Panel({
						border: false,
						style: {
							'margin-left': '20px',
							'margin-right': '20px',
							'text-align': 'center',
							'font-size': '1.2em'
						},
						html: '<div style="background-image: url('+wgScriptPath+'/extensions/BlueSpiceExtensions/WikiAdmin/images/warning.png); background-repeat: no-repeat; background-position: left center;">'
						+ '<div style="background-image: url('+wgScriptPath+'/extensions/BlueSpiceExtensions/WikiAdmin/images/warning.png); background-repeat: no-repeat; background-position: right center;">'
						+ '<p>' + BsNamespaceManager.i18n.sureDeletePt1 + '</p>'
						+ '<p>' + BsNamespaceManager.i18n.sureDeletePt2 + '</p></div></div>'
					}),
					new Ext.form.FieldSet({
						id: 'NsChooseFieldSet',
						title: BsNamespaceManager.i18n.articlesPresent, //'Noch vorhandene Artikel in diesem Namespace ... ',
						autoHeight: true,
						style: {
							'margin-left': '5px',
							'margin-right': '5px'
						},
						items: [{
							id:'bs-nm-dlg-radio-delete',
							xtype: 'radio',
							boxLabel: BsNamespaceManager.i18n.willDelete ,//'... werden gel&ouml;scht',
							hideLabel: true,
							name: 'doArticle',
							inputValue: 0
						}, {
							xtype: 'hidden',
							name: 'id',
							id: 'removeWindowId',
							value: this.idNS
						}, 
						
						{
							id:'bs-nm-dlg-radio-move',
							xtype: 'radio',
							boxLabel: BsNamespaceManager.i18n.willMove,//'... werden in den Mainspace verschoben*',
							hideLabel: true,
							name: 'doArticle',
							inputValue: 1,
							checked: true
						}, {
							id:'bs-nm-dlg-radio-movesuffix',
							xtype: 'radio',
							boxLabel: BsNamespaceManager.i18n.willMoveSuffix ,//'... werden mit dem Suffix "(from <span class="removeWindowNamespaceName"></span>)" in den Mainspace verschoben',
							hideLabel: true,
							name: 'doArticle',
							inputValue: 2
						}, new Ext.Panel({
							border: false,
							html: BsNamespaceManager.i18n.moveConflict //'* Wenn ein Namenskonflikt auftritt, wird dem zu verschiebenden Artikel das Suffix "(from <span class="removeWindowNamespaceName"></span>)" angehängt'
						})
						]
					})
					],
					buttons: [{
						text: BsNamespaceManager.i18n.btnDelete, //'l&ouml;schen',
						id: 'bs-nm-delete-btn-save',
						handler: function() {
							this.form.form.submit({
								url: BsCore.buildRemoteString('NamespaceManager', 'deleteNamespace'),
								success: function(form, action) {
									var obj = action.result;
									BsNamespaceManager.panel.store.load();
									BsNamespaceManager.showRemoveWindow.hide();
								},
								failure: function(form, action) {
									var obj = action.result;
									Ext.Msg.alert(BsNamespaceManager.i18n.titleError, obj.msg);
								}
							});
						},
						scope: this
					},{
						text: BsNamespaceManager.i18n.btnCancel, //'abbrechen',
						id: 'bs-nm-delete-btn-cancel',
						handler: function(){
							BsNamespaceManager.showRemoveWindow.hide();
						},
						scope: this
					}]
				});

				BsNamespaceManager.showRemoveWindow = new Ext.Window({
					title: BsNamespaceManager.i18n.deleteNamespace, //'Namespace l&ouml;schen',
					items: this.form,
					id: 'bs-nm-delete-window',
					closeAction: 'hide',
					width: 550,
					modal: true,
					resizable: false,
					keys: {
						key: 27,
						handler: function() {
							BsNamespaceManager.showRemoveWindow.hide();
						},
						scope: this
					}
				});
			}
			BsNamespaceManager.showRemoveWindow.show();
			
			var spans = Ext.query('span.removeWindowNamespaceName');
			for(var i = 0; i < spans.length; i++) {
				spans[i].innerHTML = record.data.name;
			}
			if( record.json.empty == false) { 
				Ext.getCmp('NsChooseFieldSet').show();
				Ext.getCmp('bs-nm-dlg-radio-delete').setValue(0);
				Ext.getCmp('bs-nm-dlg-radio-move').setValue(1);
			} else {
				Ext.getCmp('NsChooseFieldSet').hide();
				Ext.getCmp('bs-nm-dlg-radio-delete').setValue(1);
				Ext.getCmp('bs-nm-dlg-radio-move').setValue(0);
			}
			
			Ext.getCmp('removeWindowId').setValue(record.data.id);
			BsNamespaceManager.showRemoveWindow.syncShadow();
		}
	}
}

Ext.onReady(function() {
	BsNamespaceManager.show();
}, window, {
	delay: 500
});
