Ext.require([
	'Ext.grid.*',
	'Ext.data.*',
	'Ext.tip.QuickTipManager',
	'BS.PermissionManager.model.Data',
	'BS.PermissionManager.model.Namespace',
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
	var _gridStore = Ext.create('Ext.data.Store', {
		model: 'BS.PermissionManager.model.Data',
		data: []
	});
	var _grid = Ext.create('BS.PermissionManager.GridPanel');
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
						name: 'isGlobal'
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
						hideable: false
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
									for (field in record.data) {
										if (field === 'permission'
											|| field === 'isGlobal'
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
								if(record.get('global_allowed')) {
									meta.tdCls = 'allowed';
								}
								var cssPrefix = Ext.baseCSSPrefix,
									cls = [cssPrefix + 'grid-checkcolumn'];
								if (this.disabled) {
									meta.tdCls += ' ' + this.disabledCls;
								}
								if (value) {
									cls.push(cssPrefix + 'grid-checkcolumn-checked');
								}
								return '<img class="' + cls.join(' ') + '" src="' + Ext.BLANK_IMAGE_URL + '"/>';
						}
					}],
				subcolumns = [];
				data = records[0].getAssociatedData();
				Ext.Array.each(data.namespaces, function(namespace, index, namespaces) {
					hidden = !(typeof(_visibleOnDefault[namespace.id]) !== 'undefined'
						&& _visibleOnDefault[namespace.id] === true);
					subcolumns.push({
						header: namespace.name,
						dataIndex: namespace.name,
						width: 200,
						hidden: hidden,
						xtype: 'checkcolumn',
						sortable: false,
						renderer: function(value, meta, record) {
							if (!record.get('isGlobal')) {
								if(record.get(this.text + '_allowed')) {
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
					fields: fields
				});
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
						write: function( store, operation, eOpts ) {
							var button = Ext.getCmp('btnSave');
							button.setText(mw.messages.get('bs-permissionmanager-btn-save-label'));
							button.enable();
							Ext.MessageBox.alert('', mw.messages.get('bs-permissionmanager-btn-save-success'));
							_mainStore.reload();
						}
					},
					groupField: 'grouping'
				});
				Ext.Array.each(records[0].raw.rules, function(rule, index, rules) {
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
												{group: activeItem.text}
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
								}]
						})
					]
				});
			}
		},
		autoLoad: true
	});
});