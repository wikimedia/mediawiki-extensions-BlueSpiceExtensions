Ext.require([
    'Ext.grid.*',
    'Ext.data.*',
    'Ext.tip.QuickTipManager',
    'BS.PermissionManager.model.Data',
    'BS.PermissionManager.model.Namespace',
    'BS.PermissionManager.model.Template',
    'BS.PermissionManager.model.AccessData'
],
        function() {
            Ext.override(Ext.grid.locking.Lockable, {
                modifyHeaderCt: function() {
                    return;
                }
            });

            Ext.tip.QuickTipManager.init();
            var _visibleOnDefault = {
                0: true,
                1: true,
                2: true,
                3: true
            };
            var _templateMatrix = {
                templates: {},
                memory: {}
            };
            var _checkLock = false;
            var _checkTemplates = function() {
                Ext.suspendLayouts();
                _checkLock = true;
                for (var key in _templateMatrix.templates) {
                    var ruleSet = _templateMatrix.templates[key].ruleSet;

                    for (var ns in _templateMatrix.memory) {
                        var permissions = _templateMatrix.memory[ns];
                        var match = true;

                        for (var permission in ruleSet) {
                            if (!Ext.Array.contains(permissions, ruleSet[permission])) {
                                match = false;
                            }
                        }

                        if (match) {
                            _gridStore.getById(key).set(ns, true);
                        } else {
                            _gridStore.getById(key).set(ns, false);
                        }
                        _gridStore.getById(key).commit();
                    }
                }
                _checkLock = false;
                Ext.resumeLayouts(true);
            };
            var _checkPermissions = function() {
                Ext.suspendLayouts();
                _gridStore.each(function(record) {
                    _checkLock = true;
                    if (record.get('isTemplate')) {
                        var ruleSet = _templateMatrix.templates[record.get('permission')].ruleSet;

                        record.fields.eachKey(function(ns) {
                            if (Ext.Array.contains(['group', 'permission',
                                'isGlobal', 'isTemplate', 'grouping'], ns)) {
                                _checkLock = false;
                                Ext.resumeLayouts(true);
                                return true;
                            }
                            if (ns.indexOf('_allowed') != -1) {
                                _checkLock = false;
                                Ext.resumeLayouts(true);
                                return true;
                            }

                            for (var permission in ruleSet) {
                                _gridStore.getById(ruleSet[permission]).set(ns, record.get(ns));
                            }
                        });
                    }
                    _checkLock = false;
                });
                Ext.resumeLayouts(true);
            }
            var _templatePermissionStore = Ext.create('Ext.data.Store', {
                storeId: 'testStore',
                fields: ['name', 'enabled'],
                data: {
                    'items': []
                },
                proxy: {
                    type: 'memory',
                    reader: {
                        type: 'json',
                        root: 'items'
                    }
                }
            });
            var _templateTreeStore = Ext.create('Ext.data.TreeStore', {
                model: 'BS.PermissionManager.model.Template',
                root: {
                    expanded: true,
                    children: [],
                    proxy: {
                        type: 'memory',
                        reader: {
                            type: 'json'
                        }
                    }
                }
            });
            var _templateEditor = Ext.create('BS.PermissionManager.TemplateEditor', {
                treeStore: _templateTreeStore,
                permissionStore: _templatePermissionStore
            });
            _templateEditor.on('hide', function() {
                if (_templateEditor.hasChanged()) {
                    _mainStore.load();
                }
            });
            var _gridStore = Ext.create('Ext.data.Store', {
                model: 'BS.PermissionManager.model.Data',
                data: []
            });
            var _grid = Ext.create('BS.PermissionManager.GridPanel');
            var _gridStoreExtra = Ext.create('Ext.data.Store', {
                model: 'BS.PermissionManager.model.Data',
                data: []
            });
            var _gridExtra = Ext.create('BS.PermissionManager.GridPanelExtra');
            var _columnsHiddenDefault = {};
            var _extraStore = Ext.create('Ext.data.Store', {
                model: 'BS.PermissionManager.model.AccessData',
                proxy: {
                    type: 'ajax',
                    url: bs.util.getAjaxDispatcherUrl('PermissionManager::getGroupAccessData'),
                    reader: {
                        type: 'json',
                        root: 'data'
                    }
                },
                autoLoad: false,
                listeners: {
                    load: function(store, records, successful, eOpts) {
                        var columns = [{
                                header: mw.messages.get('bs-permissionmanager-header-group'),
                                dataIndex: 'group',
                                width: 300,
                                locked: true,
                                sortable: true,
                                hideable: false
                            }, {
                                header: mw.messages.get('bs-permissionmanager-header-global'),
                                dataIndex: 'global',
                                width: 160,
                                xtype: 'checkcolumn',
                                sortable: false,
                                listeners: {
                                    beforecheckchange: function() {
                                        return false;
                                    }
                                }
                            }];
                        var fields = [{
                                name: 'groups', type: 'string'
                            }, {
                                name: 'global', type: 'boolean'
                            }];
                        var subcolumns = [];
                        var data = records[0].get('data')[0];
                        for (var key in data) {
                            if (key == 'group' || key == 'global') {
                                continue;
                            }
                            subcolumns.push({
                                header: key,
                                dataIndex: key,
                                width: 200,
                                hidden: _columnsHiddenDefault[key],
                                xtype: 'checkcolumn',
                                sortable: false,
                                listeners: {
                                    beforecheckchange: function() {
                                        return false;
                                    }
                                }
                            });
                            fields.push({
                                name: key,
                                type: 'boolean'
                            });
                        }
                        columns.push({
                            header: mw.messages.get('bs-permissionmanager-header-namespaces'),
                            columns: subcolumns
                        });
                        Ext.define('AccessDataSet', {
                            extend: 'Ext.data.Model',
                            fields: fields,
                            idProperty: 'group'
                        });
                        _gridStoreExtra = Ext.create('Ext.data.Store', {
                            model: 'AccessDataSet',
                            data: [],
                            proxy: {
                                type: 'memory',
                                reader: {
                                    //This is needed in case an namespace contains "."
                                    //i.e. "MW_1.21.1"
                                    useSimpleAccessors: true
                                }
                            }
                        });
                        _gridExtra.destroy();
                        _gridExtra = Ext.create('BS.PermissionManager.GridPanelExtra', {
                            store: _gridStoreExtra,
                            columns: columns
                        });
                    }
                }
            });
            var _mainStore = Ext.create('Ext.data.Store', {
                model: 'BS.PermissionManager.model.Data',
                proxy: {
                    type: 'ajax',
                    url: bs.util.getAjaxDispatcherUrl('PermissionManager::getAccessRules'),
                    reader: {
                        type: 'json',
                        root: 'data'
                    }
                },
                listeners: {
                    load: function(store, records, successful, eOpts) {
                        var data,
                                ruleset,
                                hidden,
                                fields = [{
                                        name: 'group'
                                    }, {
                                        name: 'permission'
                                    }, {
                                        name: 'tip'
                                    }, {
                                        name: 'isGlobal'
                                    }, {
                                        name: 'isTemplate', type: 'boolean'
                                    }, {
                                        name: 'grouping'
                                    }, {
                                        name: 'global'
                                    }, {
                                        name: 'global_allowed'
                                    }],
                        columns = [{
                                header: mw.messages.get('bs-permissionmanager-header-permissions'),
                                dataIndex: 'permission',
                                width: 300,
                                locked: true,
                                sortable: true,
                                hideable: false,
                                renderer: function(value, meta, record) {
                                    if(record.get('tip')) {
                                        meta.tdAttr = 'data-qtip="' + record.get('tip') + '"';
                                    }
                                    return value;
                                }
                            }, {
                                header: mw.messages.get('bs-permissionmanager-header-global'),
                                dataIndex: 'global',
                                width: 160,
                                xtype: 'checkcolumn',
                                sortable: false,
                                listeners: {
                                    checkchange: function(column, index, checked, eOpts) {
                                        var record = _grid.getView().getRecord(index);
                                        if (checked === false) {
                                            for (var field in record.data) {
                                                if (field === 'permission'
                                                        || field === 'tip'
                                                        || field === 'isGlobal'
                                                        || field === 'isTemplate'
                                                        || field === 'global'
                                                        || field === 'grouping'
                                                        || field === 'group') {
                                                    continue;
                                                }
                                                record.set(field, false);
                                            }
                                        }
                                    }
                                },
                                renderer: function(value, meta, record) {
                                    if (record.get('global_allowed')) {
                                        meta.tdCls = 'allowed';
                                    }
                                    var cssPrefix = Ext.baseCSSPrefix,
                                            cls = [cssPrefix + 'grid-checkcolumn'];
                                    if (this.disabled) {
                                        meta.tdCls += ' ' + this.disabledCls;
                                    }
                                    if (value) {
                                        cls.push(cssPrefix + 'grid-checkcolumn-checked');
                                        if (typeof _templateMatrix.memory['global'] == 'undefined') {
                                            _templateMatrix.memory['global'] = [];
                                        }
                                        _templateMatrix.memory['global'].push(record.get('permission'));
                                    }
                                    return '<img class="' + cls.join(' ') + '" src="' + Ext.BLANK_IMAGE_URL + '"/>';
                                }
                            }],
                        subcolumns = [];
                        data = records[0].getAssociatedData();
                        Ext.Array.each(data.namespaces, function(namespace, index, namespaces) {
                            hidden = !(typeof (_visibleOnDefault[namespace.id]) !== 'undefined'
                                    && _visibleOnDefault[namespace.id] === true);
                            _columnsHiddenDefault[namespace.name] = hidden;
                            subcolumns.push({
                                header: namespace.name,
                                dataIndex: namespace.name,
                                width: 200,
                                hidden: hidden,
                                xtype: 'checkcolumn',
                                sortable: false,
                                renderer: function(value, meta, record) {
                                    if (!record.get('isGlobal')) {
                                        if (record.get(this.text + '_allowed')) {
                                            meta.tdCls = 'allowed';
                                            //console.log(record.get(this.text + '_allowed'));
                                        }
                                        var cssPrefix = Ext.baseCSSPrefix,
                                                cls = [cssPrefix + 'grid-checkcolumn'];
                                        if (this.disabled) {
                                            meta.tdCls += ' ' + this.disabledCls;
                                        }
                                        if (value) {
                                            cls.push(cssPrefix + 'grid-checkcolumn-checked');
                                            if (typeof _templateMatrix.memory[this.text] == 'undefined') {
                                                _templateMatrix.memory[this.text] = [];
                                            }
                                            _templateMatrix.memory[this.text].push(record.get('permission'));
                                        }
                                        return '<img class="' + cls.join(' ') + '" src="' + Ext.BLANK_IMAGE_URL + '"/>';
                                    }
                                    return '';
                                },
                                listeners: {
                                    checkchange: function(column, index, checked, eOpts) {
                                        if (checked === true) {
                                            var record = _grid.getView().getRecord(index);
                                            record.set('global', true);
                                        }
                                    }
                                }
                            });
                            fields.push({
                                name: namespace.name
                            }, {
                                name: namespace.name + '_allowed'
                            });
                        });
                        columns.push({
                            header: mw.messages.get('bs-permissionmanager-header-namespaces'),
                            columns: subcolumns
                        });
                        Ext.define('PermissionRule', {
                            extend: 'Ext.data.Model',
                            fields: fields,
                            idProperty: 'permission'
                        });
                        _templateMatrix['memory'] = {};
                        _gridStore = Ext.create('Ext.data.Store', {
                            model: 'PermissionRule',
                            data: [],
                            proxy: {
                                type: 'ajax',
                                url: bs.util.getAjaxDispatcherUrl('PermissionManager::setAccessRules'),
                                writer: {
                                    type: 'json',
                                    root: 'rules',
                                    encode: true
                                },
                                reader: {
                                    //This is needed in case an namespace contains "."
                                    //i.e. "MW_1.21.1"
                                    useSimpleAccessors: true
                                }
                            },
                            listeners: {
                                write: function(store, operation, eOpts) {
                                    var button = Ext.getCmp('btnSave');
                                    button.setText(mw.messages.get('bs-permissionmanager-btn-save-label'));
                                    button.enable();
                                    Ext.MessageBox.alert('', mw.messages.get('bs-permissionmanager-btn-save-success'));
                                    _mainStore.reload();
                                },
                                update: function(store, record, op, modifiedFields, options) {
                                    var permission = record.get('permission');

                                    for (var i in modifiedFields) {
                                        if (typeof _templateMatrix.memory[modifiedFields[i]] == 'undefined') {
                                            _templateMatrix.memory[modifiedFields[i]] = [];
                                        }
                                        if (record.get(modifiedFields[i])) {
                                            _templateMatrix.memory[modifiedFields[i]].push(permission);
                                        } else {
                                            for (var j in _templateMatrix.memory[modifiedFields[i]]) {
                                                if (_templateMatrix.memory[modifiedFields[i]][j] == permission) {
                                                    delete _templateMatrix.memory[modifiedFields[i]][j];
                                                }
                                            }
                                        }
                                    }

                                    if (!_checkLock) {
                                        if (!record.get('isTemplate')) {
                                            _checkTemplates();
                                        } else {
                                            _checkPermissions();
                                        }
                                    }
                                }
                            },
                            groupField: 'grouping'
                        });

                        _templateMatrix['records'] = {};
                        _templateMatrix['templates'] = {};
                        Ext.Array.each(records[0].raw.templates, function(template, index, rules) {
                            _templateTreeStore.getRootNode().appendChild(template);
                            ruleset = {
                                group: records[0].raw.activeGroup,
                                isTemplate: true,
                                grouping: ' ' + mw.message('bs-permissionmanager-labelTemplates').plain(),
                                permission: template.text
                            };

                            _gridStore.add(ruleset);
                            _templateMatrix['templates'][template.text] = template;
                        });

                        Ext.Array.each(records[0].raw.rules, function(rule, index, rules) {
                            _templatePermissionStore.add({
                                name: rule.permission,
                                enabled: false
                            });
                            ruleset = {
                                group: records[0].raw.activeGroup
                            };
                            for (var key in rule) {
                                ruleset[key] = rule[key];
                            }
                            _gridStore.add(ruleset);
                        });
                        _grid.destroy();
                        _grid = Ext.create('BS.PermissionManager.GridPanel', {
                            store: _gridStore,
                            columns: columns,
                            listeners: {
                                select: function(rm, record, index, options) {
                                    var permission = record.get('permission');
                                    if (!record.get('isTemplate')) {
                                        var extraRecord = _extraStore.getById(permission);
                                        _gridStoreExtra.loadRawData(extraRecord.get('data'));
                                        //_gridExtra.show();
                                    } else {
                                        console.log(record);
                                        _gridStoreExtra.removeAll();
                                        //_gridExtra.getView().refresh();
                                        //_gridExtra.hide();
                                    }
                                }
                            },
                            dockedItems: [
                                Ext.create('Ext.toolbar.Toolbar', {
                                    dock: 'top',
                                    items: [{
                                            xtype: 'cycle',
                                            showText: true,
                                            prependText: mw.messages.get('bs-permissionmanager-btn-group-label'),
                                            menu: {
                                                items: records[0].raw.btnGroup
                                            },
                                            changeHandler: function(cycleBtn, activeItem) {
                                                _mainStore.load({
                                                    url: bs.util.getAjaxDispatcherUrl(
                                                            'PermissionManager::getAccessRules',
                                                            {group: activeItem.value}
                                                    )
                                                });
                                            }
                                        }, {
                                            text: mw.messages.get('bs-permissionmanager-btn-save-label'),
                                            id: 'btnSave',
                                            handler: function(button, event) {
                                                button.setText(mw.messages.get('bs-permissionmanager-btn-save-in-progress-label'));
                                                button.disable();
                                                _gridStore.save();
                                            }
                                        }, {
                                            text: mw.messages.get('bs-permissionmanager-btn-template-editor'),
                                            id: 'btnTemplateEditor',
                                            handler: function(button, event) {
                                                _templateEditor.show();
                                            }
                                        }]
                                })
                            ]
                        });
                        /*var view = _grid.getView();
                        var tip = Ext.create('Ext.tip.ToolTip', {
                            // The overall target element.
                            target: view.el,
                            // Each grid row causes its own separate show and hide.
                            delegate: view.itemSelector,
                            // Moving within the row should not hide the tip.
                            trackMouse: true,
                            // Render immediately so that tip.body can be referenced prior to the first show.
                            renderTo: Ext.getBody(),
                            listeners: {
                                // Change content dynamically depending on which element triggered the show.
                                beforeshow: function updateTipBody(tip) {
                                    console.log(tip);
                                    var record = view.getRecord(tip.triggerElement);
                                    if(record.get('tip')) {
                                        tip.update(record.get('tip'));
                                    } else {
                                        tip.update(record.get('permission'));
                                    }
                                    
                                }
                            }
                        });*/
                        _extraStore.load();
                        window.setTimeout(function() {
                            _checkTemplates();
                        }, 0);
                    }
                },
                autoLoad: true
            });
        });