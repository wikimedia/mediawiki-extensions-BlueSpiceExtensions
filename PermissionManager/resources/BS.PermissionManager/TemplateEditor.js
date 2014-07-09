Ext.require('Ext.tree.Panel');
Ext.require('BS.AlertDialog');
Ext.require('BS.PromptDialog');
Ext.define('BS.PermissionManager.TemplateEditor', {
    extend: 'Ext.window.Window',
    title: mw.message('bs-permissionmanager-labelTemplateEditor').plain(),
    loadMask: false,
    width: 500,
    height: 450,
    layout: 'border',
    closeAction: 'hide',
    _cleanState: true,
    _hasChanged: false,
    constructor: function(config) {
        this._treeStore = config.treeStore || {};
        this._permissionStore = config.permissionStore || {};
        delete config.treeStore;
        delete config.permissionStore;
        this.callParent([config]);
    },
    setCleanState: function(clean) {
        this._cleanState = clean;
        if (this._cleanState === true) {
            Ext.getCmp('pmTemplateEditorSaveButton').disable();
        } else {
            Ext.getCmp('pmTemplateEditorSaveButton').enable();
        }
    },
    getCleanState: function() {
        return this._cleanState;
    },
    hasChanged: function() {
        return this._hasChanged;
    },
    saveTemplate: function() {
        var me = this;
        var record = Ext.getCmp('bs-template-editor-treepanel').getSelectionModel().getLastSelected();
        var newRecord = {
            id: record.get('id'),
            text: record.get('text'),
            leaf: record.get('leaf'),
            ruleSet: [],
            description: Ext.getCmp('bs-template-editor-description').getRawValue()
        };

        if (typeof record != 'undefined') {

            for (i in me._permissionStore.data.items) {
                var dataSet = me._permissionStore.data.items[i].data;
                if (dataSet.enabled === true) {
                    newRecord.ruleSet.push(dataSet.name);
                }
            }

            Ext.Ajax.request({
                url: bs.util.getAjaxDispatcherUrl('PermissionManager::setTemplateData'),
                method: 'POST',
                params: {
                    template: Ext.JSON.encode(newRecord)
                },
                success: function(response) {
                    var result = Ext.JSON.decode(response.responseText);
                    if (result.success === true) {
                        var rootNode = me._treeStore.getRootNode();
                        rootNode.replaceChild(newRecord, record);

                        me.setCleanState(true);
                        me._permissionStore.sync();
                        me._hasChanged = true;

                        Ext.getCmp('bs-template-editor-treepanel').getSelectionModel().select(me._treeStore.getNodeById(newRecord.text));
                        Ext.create('BS.AlertDialog', {
                            text: mw.message('bs-permissionmanager-template-editor-save-success').plain()
                        }).show();
                    } else {
                        Ext.create('BS.AlertDialog', {
                            text: result.msg
                        }).show();
                    }
                },
                failure: function(response) {
                    console.log(response);
                }
            });
        }
    },
    discardChanges: function() {
        Ext.getCmp('bs-template-editor-description').setRawValue('')
        this._permissionStore.each(function(record) {
            record.set('enabled', false);
        });
        this.setCleanState(true);
    },
    noUnsavedChanges: function(record) {
        var me = this;
        if (typeof record == 'undefined') {
            record = false;
        }
        if (me.getCleanState() === false) {
            var dialog = Ext.create('BS.ConfirmDialog', {
                text: mw.message('bs-permissionmanager-template-editor-saveOrAbort').plain()
            });
            dialog.on('ok', function() {
                me.saveTemplate();
                if (record !== false) {
                    Ext.getCmp('bs-template-editor-treepanel').getSelectionModel().select(record);
                }
            });
            dialog.on('cancel', function() {
                me.discardChanges();
                if (record !== false) {
                    Ext.getCmp('bs-template-editor-treepanel').getSelectionModel().select(record);
                }
            })
            dialog.show();
            return false;
        }
        return true;
    },
    initComponent: function() {
        var me = this;
        me.items = [{
                xtype: 'treepanel',
                region: 'west',
                id: 'bs-template-editor-treepanel',
                useArrows: false,
                width: 160,
                store: me._treeStore,
                rootVisible: false,
                margins: '0 0 5 0',
                listeners: {
                    'select': function(rm, record, index, options) {
                        var data = [];
                        for (i in me._permissionStore.data.items) {
                            var dataSet = me._permissionStore.data.items[i].data;
                            if (Ext.Array.contains(record.get('ruleSet'), dataSet.name)) {
                                dataSet.enabled = true;
                            } else {
                                dataSet.enabled = false;
                            }
                            data.push(dataSet);
                        }
                        me._permissionStore.loadRawData(data);
                        Ext.getCmp('bs-template-editor-description').setRawValue(record.raw.description);
                        Ext.getCmp('pmTemplateEditorEditButton').enable();
                        Ext.getCmp('pmTemplateEditorRemoveButton').enable();
                        me.setCleanState(true);
                    },
                    'beforeselect': function(rm, record, index, options) {
                        return me.noUnsavedChanges(record);
                    }
                }
            }, {
                xtype: 'container',
                layout: 'border',
                region: 'center',
                items: [{
                        xtype: 'panel',
                        layout: 'form',
                        region: 'center',
                        title: mw.message('bs-permissionmanager-labelTemplateEditor-description').plain(),
                        id: 'bs-template-editor-formpanel',
                        margins: '0 0 5 5',
                        items: [{
                                xtype: 'textareafield',
                                grow: false,
                                id: 'bs-template-editor-description',
                                name: 'description',
                                hideLabel: true,
                                margin: 0,
                                padding: 0,
                                height: 80,
                                anchor: '100%'
                            }]
                    }, {
                        xtype: 'gridpanel',
                        region: 'south',
                        id: 'bs-template-editor-gridpanel',
                        height: 250,
                        margins: '0 0 5 5',
                        store: me._permissionStore,
                        columns: [{
                                xtype: 'checkcolumn',
                                text: mw.message('bs-permissionmanager-labelTemplateEditor-active').plain(),
                                dataIndex: 'enabled',
                                listeners: {
                                    'checkchange': function(column, rowIndex, checked, options) {
                                        me.setCleanState(false);
                                    }
                                }
                            }, {
                                text: mw.message('bs-permissionmanager-labelTemplateEditor-permissions').plain(),
                                dataIndex: 'name',
                                flex: 1
                            }
                        ]
                    }]
            }];
        me.bbar = [{
                text: mw.message('bs-permissionmanager-template-editor-labelAdd').plain(),
                id: 'pmTemplateEditorAddButton',
                handler: function(button, event) {
                    if (me.noUnsavedChanges()) {
                        var dialog = Ext.create('BS.PromptDialog', {
                            text: mw.message('bs-permissionmanager-template-editor-msgNew').plain()
                        });
                        dialog.on('ok', function(input) {
                            var node = me._treeStore.tree.root.appendChild({
                                id: 0,
                                text: input.value,
                                leaf: true,
                                description: '',
                                ruleSet: []
                            });
                            Ext.getCmp('bs-template-editor-treepanel').getSelectionModel().select(node);
                        });
                        dialog.show();
                    }
                }
            }, {
                text: mw.message('bs-permissionmanager-template-editor-labelEdit').plain(),
                disabled: true,
                id: 'pmTemplateEditorEditButton',
                handler: function(button, event) {
                    var dialog = Ext.create('BS.PromptDialog', {
                        text: mw.message('bs-permissionmanager-template-editor-msgEdit').plain()
                    });
                    dialog.on('ok', function(input) {
                        Ext.getCmp('bs-template-editor-treepanel').getSelectionModel().getLastSelected().set('text', input.value);
                        me.setCleanState(false);
                    });
                    dialog.show();
                }
            }, {
                text: mw.message('bs-permissionmanager-template-editor-labelDelete').plain(),
                disabled: true,
                id: 'pmTemplateEditorRemoveButton',
                handler: function(button, event) {
                    Ext.Ajax.request({
                        url: bs.util.getAjaxDispatcherUrl('PermissionManager::deleteTemplate'),
                        method: 'POST',
                        params: {
                            id: Ext.getCmp('bs-template-editor-treepanel').getSelectionModel().getLastSelected().get('id')
                        },
                        success: function(response) {
                            var result = Ext.JSON.decode(response.responseText);
                            if (result.success === true) {
                                me.setCleanState(true);
                                me._permissionStore.sync();
                                me._hasChanged = true;
                                Ext.create('BS.AlertDialog', {
                                    text: mw.message('bs-permissionmanager-template-editor-delete-success').plain()
                                }).show();
                            } else {
                                Ext.create('BS.AlertDialog', {
                                    text: result.msg
                                }).show();
                            }
                        },
                        failure: function(response) {
                            console.log(response);
                        }
                    });
                }
            }, '->', {
                text: mw.message('bs-permissionmanager-template-editor-labelSave').plain(),
                disabled: true,
                id: 'pmTemplateEditorSaveButton',
                handler: function(button, event) {
                    me.saveTemplate();
                }
            }, {
                text: mw.message('bs-permissionmanager-template-editor-labelCancel').plain(),
                handler: function(button, event) {
                    me.discardChanges();
                    me.hide();
                }
            }];
        me.on('show', function() {
            Ext.getCmp('bs-template-editor-treepanel').getSelectionModel().deselectAll();
            Ext.getCmp('bs-template-editor-treepanel').getSelectionModel().select(me._treeStore.getRootNode().getChildAt(0));
        });
        this.callParent();
    }
});