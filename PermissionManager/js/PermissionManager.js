Ext.QuickTips.init();

Ext.override(Ext.grid.CellSelectionModel, {
	initEvents : function(){
		this.grid.on('cellclick', this.handleClick, this);
		this.grid.on(Ext.EventManager.getKeyEvent(), this.handleKeyDown, this);
		this.grid.getView().on({
			scope: this,
			refresh: this.onViewChange,
			rowupdated: this.onRowUpdated,
			beforerowremoved: this.clearSelections,
			beforerowsinserted: this.clearSelections
		});
		if(this.grid.isEditor){
			this.grid.on('beforeedit', this.beforeEdit,  this);
		}
	},
	handleClick : function(g, row, cell, e){
		if(e.button !== 0 || this.isLocked()){
			return;
		}
		this.select(row, cell);
	}
});

Ext.override(Ext.grid.PivotGridView, {
	renderRows : function(startRow, endRow) {
		var grid		  = this.grid,
		rows		  = grid.extractData(),
		rowCount      = rows.length,
		templates     = this.templates,
		renderer      = grid.renderer,
		hasRenderer   = typeof renderer == 'function',
		getCellCls    = this.getCellCls,
		hasGetCellCls = typeof getCellCls == 'function',
		cellTemplate  = templates.cell,
		rowTemplate   = templates.row,
		rowBuffer     = [],
		meta		  = {},
		tstyle		= 'width:' + this.getGridInnerWidth() + 'px;',
		colBuffer, column, i;

		startRow = startRow || 0;
		endRow   = Ext.isDefined(endRow) ? endRow : rowCount - 1;

		for (i = 0; i < rowCount; i++) {
			row = rows[i];
			colCount  = row.length;
			colBuffer = [];

			rowIndex = startRow + i;

			//build up each column's HTML
			for (j = 0; j < colCount; j++) {
				cell = row[j];

				meta.id    = i + '-' + j;
				meta.css   = j === 0 ? 'x-grid3-cell-first ' : (j == (colCount - 1) ? 'x-grid3-cell-last ' : '');
				meta.attr  = meta.cellAttr = '';
				meta.value = cell;

				if (Ext.isEmpty(meta.value)) {
					meta.value = '&#160;';
				}

				if (hasGetCellCls) {
					meta.css += getCellCls(meta.value) + ' ';
				}

				if (hasRenderer) {
					meta.value = renderer(meta.value);
				}

				colBuffer[colBuffer.length] = cellTemplate.apply(meta);
			}

			rowBuffer[rowBuffer.length] = rowTemplate.apply({
				tstyle: tstyle,
				cols  : colCount,
				cells : colBuffer.join(""),
				alt   : ''
			});
		}

		return rowBuffer.join("");
	},

	getCellIndex : function(el) {
		if (el) {
			var match = el.className.match(this.colRe),
			data;

			if (match && (data = match[1])) {
				return parseInt(data.split('-')[1], 10);
			}
		}
		return false;
	}
});

Ext.override(Ext.grid.PivotAxis, {
	templateCounter: false,
	getTuples: function() {
		var newStore = new Ext.data.Store({});

		newStore.data = this.store.data.clone();
		newStore.fields = this.store.fields;

		var sorters    = [],
		dimensions = this.dimensions,
		length     = dimensions.length,
		i;

		for (i = 0; i < length; i++) {
			sorters.push({
				field    : dimensions[i].sortIndex || dimensions[i].dataIndex, // Custom sorting
				direction: dimensions[i].direction || 'ASC'
			});
		}

		newStore.sort(sorters);

		var records = newStore.data.items,
		hashes  = [],
		tuples  = [],
		recData, hash, info, data, key;

		length = records.length;

		for (i = 0; i < length; i++) {
			info = this.getRecordInfo(records[i]);
			data = info.data;
			hash = "";

			for (key in data) {
				hash += data[key] + '---';
			}

			if (hashes.indexOf(hash) == -1) {
				hashes.push(hash);
				tuples.push(info);
			}
		}

		newStore.destroy();

		return tuples;
	},
	renderHorizontalRows: function() {
		var headers  = this.buildHeaders(),
		rowCount = headers.length,
		rows     = [],
		cells, cols, colCount, i, j;

		for (i = 0; i < rowCount; i++) {
			cells = [];
			cols  = headers[i].items;
			colCount = cols.length;

			for (j = 0; j < colCount; j++) {
				cells.push({
					tag: 'td',
					html: cols[j].header + '',
					colspan: cols[j].span,
					'ext:qtip': cols[j].header + ''
				});
				
			}

			rows[i] = {
				tag: 'tr',
				cn: cells
			};
		}

		return rows;
	},
	renderVerticalRows: function() {
		var headers  = this.buildHeaders(),
		colCount = headers.length,
		rowCells = [],
		rows     = [],
		rowCount, col, row, colWidth, i, j;
		
		for (i = 0; i < colCount; i++) {
			col = headers[i];
			if(i == 0 && this.templateCounter == false) {
				this.templateCounter = col.items[0].span - 1;
			}
			colWidth = col.width || 80;
			rowCount = col.items.length;
		    
			for (j = 0; j < rowCount; j++) {
				row = col.items[j];
				rowCells[row.start] = rowCells[row.start] || [];
				if(i == 1 && j < this.templateCounter) {
					rowCells[row.start].push({
						tag    : 'td',
						html   : row.header,
						rowspan: row.span,
						'ext:qtip': bs_perm_mng_desc[row.header],
						width  : Ext.isBorderBox ? colWidth : colWidth - this.paddingWidth
					});
				} else {
					rowCells[row.start].push({
						tag    : 'td',
						html   : row.header,
						rowspan: row.span,
						width  : Ext.isBorderBox ? colWidth : colWidth - this.paddingWidth
					});
				}
				
			}
		}
		
		rowCount = rowCells.length;
		for (i = 0; i < rowCount; i++) {
			rows[i] = {
				tag: 'tr',
				cn : rowCells[i]
			};
		}
		
		return rows;
	},
	buildHeaders: function() {
		var tuples     = this.getTuples(),
		rowCount   = tuples.length,
		dimensions = this.dimensions,
		colCount   = dimensions.length,
		headers    = [],
		tuple, rows, currentHeader, previousHeader, span, start, isLast, changed, i, j;

		for (i = 0; i < colCount; i++) {
			dimension = dimensions[i];
			rows  = [];
			span  = 0;
			start = 0;

			for (j = 0; j < rowCount; j++) {
				tuple  = tuples[j];
				isLast = j == (rowCount - 1);
				currentHeader = tuple.data[dimension.dataIndex];

				/*
				 * 'changed' indicates that we need to create a new cell. This should be true whenever the cell
				 * above (previousHeader) is different from this cell, or when the cell on the previous dimension
				 * changed (e.g. if the current dimension is Product and the previous was Person, we need to start
				 * a new cell if Product is the same but Person changed, so we check the previous dimension and tuple)
				 */
				changed = previousHeader != undefined && previousHeader != currentHeader;
				if (i > 0 && j > 0) {
					changed = changed || tuple.data[dimensions[i-1].dataIndex] != tuples[j-1].data[dimensions[i-1].dataIndex];
				}

				if (changed) {
					rows.push({
						header: previousHeader,
						span  : span,
						start : start
					});

					start += span;
					span = 0;
				}

				if (isLast) {
					rows.push({
						header: currentHeader,
						span  : span + 1,
						start : start
					});

					start += span;
					span = 0;
				}

				previousHeader = currentHeader;
				span++;
			}

			headers.push({
				items: rows,
				width: dimension.width || this.defaultHeaderWidth
			});

			previousHeader = undefined;
		}

		return headers;
	}
});

var pmPivotGrid = false;
var pmTemplateEditor = false;
var pmStore = false;
var axis = {
	group: {
		leftAxis: [{
			width: 60,
			dataIndex: 'sGroupingLabel',
			direction: 'ASC',
			sortIndex: 'iGroupingId'
		}, {
			width: 165,
			dataIndex: 'sPermission'
		}],
		topAxis: [{
			dataIndex: 'sNamespace',
			sortIndex: 'iNS'
		}]
	},
	namespace: {
		leftAxis: [{
			width: 60,
			dataIndex: 'sGroupingLabel',
			direction: 'ASC',
			sortIndex: 'iGroupingId'
		}, {
			width: 165,
			dataIndex: 'sPermission'
		}],
		topAxis: [{
			dataIndex: 'sGroup'
		}]
	},
	permission: {
		leftAxis: [{
			width: 165,
			dataIndex: 'sNamespace',
			sortIndex: 'iNS'
		}],
		topAxis: [{
			dataIndex: 'sGroup'
		}]
	}
};


	
pmI18n = {
	save: 'Save',
	cancel: 'Reset',
	state: 'Status',
	error: 'Error',
	allChangesSaved: 'All changes are saved successful.',
	allChangesCanceled: 'All changes are canceled.',
	templateNameIsEmpty: 'The template name cannot be empty.',
	templateNameExists: 'The template name already exists.',
	labelGroup: 'Group',
	labelNamespace: 'Namespace',
	labelRight: 'Right',
	labelTemplateEditor: 'Template editor',
	labelPermissions: 'Rights',
	labelAdd: 'Add template',
	labelTitle: 'Title:',
	labelEdit: 'Edit template',
	labelRemove: 'Remove template',
	labelDescription: 'Description'
}

Ext.onReady(function() {
	pmStore = new Ext.data.JsonStore({
		root: 'rows',
		totalProperty: 'total',
		fields: [
		{
			name: 'iNS', 
			type: 'int'
		},
		'sNamespace', 'sGroup', 'sPermission',
		{
			name: 'iPermissionSortId', 
			type: 'int'
		},
		{
			name: 'iPermissionSet', 
			type: 'int'
		},
		'sGroupingLabel',
		{
			name: 'iGroupingId', 
			type: 'int'
		}
		],
		url: BsCore.buildRemoteString('PermissionManager', 'getData'),
		listeners: {
			beforeload: function(store, options) {
				options.params.idxType = pmPivotGrid.idxType;
				options.params.index = pmPivotGrid.index;
			},
			load: function(store, records, options) {
				pmPivotGrid.leftAxis.setDimensions(axis[options.params.idxType].leftAxis);
				pmPivotGrid.topAxis.setDimensions(axis[options.params.idxType].topAxis);
				pmPivotGrid.view.refresh(true);
				var rowHeaders = pmPivotGrid.getView().getRowHeaders()
				if(rowHeaders.length > 1) {
					var lastTemplateIndex = rowHeaders[0].items[0].start + rowHeaders[0].items[0].span;
					var row = pmPivotGrid.view.mainBody.query('div.x-grid3-row')[lastTemplateIndex - 1];
					Ext.get(row).addClass('lastTemplateRow');
				}
			}
		}
	});

	pmPivotGrid = new Ext.grid.PivotGrid({
		height    : 600,
		renderTo  : 'panelPermissionManager',
		aggregator: 'sum',
		measure   : 'iPermissionSet',
		columnLines: true,
		stripeRows: true,
		loadMask: true,
		idxType: 'group',
		index: '*',
		selectedCell: {
			col: 0,
			row: 0
		},
		listeners: {
			viewready: function(grid) {
				grid.getView().on('refresh', function() {
					window.setTimeout(function() {
						if(pmPivotGrid.selectedCell.row != 0) {
							var scroller = Ext.query('div.x-grid3-scroller')[0];
							var row = Ext.query('div.x-grid3-row')[pmPivotGrid.selectedCell.row];
							Ext.get(row).scrollIntoView(scroller.id);
						}
					}, 500);
				})
			}
		},
		buttons: [{
			text: pmI18n.save,
			handler: function() {
				Ext.Ajax.request({
					url: BsCore.buildRemoteString('PermissionManager', 'setData'),
					success: function(response, opts) {
						var obj = Ext.decode(response.responseText);
						if(obj.success) {
							pmPivotGrid.store.load();
							Ext.Msg.alert(pmI18n.state, pmI18n.allChangesSaved);
						}
						else {
							Ext.Msg.alert(pmI18n.error, obj.msg);
						}
					},
					failure: function(response, opts) {
						// CR MRG (01.07.11 13:01): i18n
						Ext.Msg.alert('Error', 'server-side failure with status code ' + response.status);
					}
				});
			}
		}, {
			text: pmI18n.cancel,
			handler: function() {
				Ext.Ajax.request({
					url: BsCore.buildRemoteString('PermissionManager', 'setDataAbort'),
					success: function(response, opts) {
						pmPivotGrid.store.load();
						Ext.Msg.alert(pmI18n.state, pmI18n.allChangesCanceled);
					},
					failure: function(response, opts) {
						// CR MRG (01.07.11 13:01): i18n
						Ext.Msg.alert(pmI18n.error, 'server-side failure with status code ' + response.status);
					}
				});
			}
		}],
		store: pmStore,

		renderer: function(value) {
			if(value == 1) {
				return '<input type="checkbox" checked="checked" />';
			}
			else {
				return '<input type="checkbox" />';
			}
		},

		bbar: new Ext.PagingToolbar({
			store: pmStore,
			pageSize: 8
		}),

		tbar: [{
			text: pmI18n.labelGroup,
			width: 80,
			id: 'pmViewChangerBtn',
			menu: {
				xtype: 'menu',
				id: 'pmViewChanger',
				items: [
				{
					xtype: 'menucheckitem',
					text: pmI18n.labelGroup,
					idxType: 'group',
					group: 'pm_vc',
					checked: true
				},
				{
					xtype: 'menucheckitem',
					text: pmI18n.labelNamespace,
					idxType: 'namespace',
					group: 'pm_vc'
				},
				{
					xtype: 'menucheckitem',
					text: pmI18n.labelRight,
					idxType: 'permission',
					group: 'pm_vc'
				}
				],
				listeners: {
					click: function(menu, item, event) {
						Ext.getCmp('pmViewChangerBtn').setText(item.text);
						pmPivotGrid.idxType = item.idxType;
						Ext.getCmp('idxCombo').store.load();
					}
				}
			}
		}, {
			xtype: 'combo',
			id: 'idxCombo',
			editable: false,
			disableKeyFilter: true,
			forceSelection: true,
			triggerAction: 'all',
			valueField: 'index',
			displayField: 'name',
			lazyInit: false,
			mode: 'local',
			store: new Ext.data.ArrayStore({
				autoLoad: true,
				fields: ['name', 'index'],
				url: BsCore.buildRemoteString('PermissionManager', 'getIndexData'),
				listeners: {
					beforeload: function(store, options) {
						options.params.idxType = pmPivotGrid.idxType;
					},
					load: function(store, records, options) {
						if(options.params.idxType == 'namespace') {
							Ext.getCmp('idxCombo').setValue(0);
							pmPivotGrid.index = 0;
						}
						else {
							Ext.getCmp('idxCombo').setValue(records[0].data.index);
							pmPivotGrid.index = records[0].data.index;
						}
						pmPivotGrid.store.load();
					}
				}
			}),
			listeners: {
				select: function(box, record, index) {
					pmPivotGrid.index = record.data.index;
					pmPivotGrid.store.load();
				}
			},
			width: 200
		}, '->', {
			text: pmI18n.labelTemplateEditor,
			handler: function() {
				pmPivotGrid.showTemplateEditor();
			}
		}],

		viewConfig: {
			getCellCls: function(value) {
				if(value == 1) {
					return 'clsPermissionSet';
				}
				if(value > 1) {
					return 'clsPermissionAvailable';
				}
				return 'clsPermissionDenied';
			}
		},

		selModel: new Ext.grid.CellSelectionModel({
			listeners: {
				cellselect: {
					fn: function(model, rowIndex, columnIndex) {
						var obj = {};
						if(pmPivotGrid.idxType == 'group') {
							obj.group = pmPivotGrid.index;
							obj.namespace = pmPivotGrid.getView().getHeaderCell(columnIndex);
							obj.namespace = obj.namespace.textContent ? obj.namespace.textContent : obj.namespace.innerText;
							obj.index = pmPivotGrid.findParentRowHeader(pmPivotGrid.getView().getRowHeaders()[0].items, rowIndex);
							obj.value = pmPivotGrid.getView().getRowHeaders()[1].items[rowIndex].header;
						}
						else if(pmPivotGrid.idxType == 'namespace') {
							obj.group = pmPivotGrid.getView().getHeaderCell(columnIndex);
							obj.group = obj.group.textContent ? obj.group.textContent : obj.group.innerText;
							obj.namespace = pmPivotGrid.index;
							obj.index = pmPivotGrid.findParentRowHeader(pmPivotGrid.getView().getRowHeaders()[0].items, rowIndex);
							obj.value = pmPivotGrid.getView().getRowHeaders()[1].items[rowIndex].header;
						}
						else {
							obj.group = pmPivotGrid.getView().getHeaderCell(columnIndex);
							obj.group = obj.group.textContent ? obj.group.textContent : obj.group.innerText;
							obj.namespace = pmPivotGrid.getView().getRowHeaders()[0].items[rowIndex].header;
							obj.index = pmI18n.labelPermissions;
							obj.value = pmPivotGrid.index;
						}
						var rows = pmPivotGrid.view.mainBody.query('div.x-grid3-row');
						var cell = Ext.get(rows[rowIndex]).query('td.x-grid3-cell-selected input')[0];
						if(typeof(cell.edited) == 'undefined') {
							if(cell.checked == cell.defaultChecked) {
								cell.edited = cell.checked;
							}
							else {
								cell.edited = !cell.checked;
							}
						}
						if(cell.edited == cell.checked) {
							return;
						}
						else {
							cell.edited = cell.checked;
							obj.checked = cell.checked;
							pmPivotGrid.selectedCell.col = columnIndex;
							pmPivotGrid.selectedCell.row = rowIndex;
							Ext.Ajax.autoAbort = true;
							Ext.Ajax.request({
								url: BsCore.buildRemoteString('PermissionManager', 'setDataTemporary'),
								success: function(response, opts) {
									pmPivotGrid.store.reload();
									Ext.Ajax.autoAbort = false;
								},
								failure: function(response, opts) {
									// CR MRG (01.07.11 13:01): i18n
									Ext.Msg.alert(pmI18n.error, 'server-side failure with status code ' + response.status);
									Ext.Ajax.autoAbort = false;
								},
								params: {
									data: Ext.encode(obj)
								}
							});
						}
						
					}
				}
			}
		}),
		findParentRowHeader: function(rowHeaders, rowIndex) {
			for(var i = 0; i < rowHeaders.length; i++) {
				if(rowIndex >= rowHeaders[i].start && rowIndex < (rowHeaders[i].start + rowHeaders[i].span)) {
					return rowHeaders[i].header;
				}
			}
		},

		leftAxis: axis.group.leftAxis,
		topAxis: axis.group.topAxis,
		showTemplateEditor: function() {
			if(!pmTemplateEditor) {
				pmTemplateEditor = new Ext.Window({
					workingTemplate: false,
					haveToSave: false,
					saveMap: [],
					title: pmI18n.labelTemplateEditor,
					loadMask: false,
					width: 498,
					height: 426,
					layout: 'border',
					closeAction: 'hide',
					buttons: [
					{
						xtype: 'button',
						text: pmI18n.labelAdd,
						id: 'pmTemplateEditorAddButton',
						handler: function() {
							pmTemplateEditor.processNode(pmTemplateEditor.workingTemplate);
							Ext.Msg.prompt(pmI18n.labelAdd, pmI18n.labelTitle, function(btn, text){
								if (btn == 'ok'){
									if (text == '') {
										Ext.Msg.alert(pmI18n.error, pmI18n.templateNameIsEmpty);
										return;
									}
									if (Ext.getCmp('pmTemplateEditorTree').getRootNode().findChild('text', text)) {
										Ext.Msg.alert(pmI18n.error, pmI18n.templateNameExists);
										return;
									}
									var node = Ext.getCmp('pmTemplateEditorTree').getRootNode().appendChild(
										new Ext.tree.TreeNode({
											text: text,
											leaf: true,
											pm: [],
											desc: ''
										})
										);
									node.select();
									pmTemplateEditor.processNode(node);
									Ext.getCmp('pmTemplateEditorEditButton').enable();
									Ext.getCmp('pmTemplateEditorRemoveButton').enable();
								}
							});
						}
					},
					{
						xtype: 'button',
						text: pmI18n.labelEdit,
						disabled: true,
						id: 'pmTemplateEditorEditButton',
						handler: function() {
							pmTemplateEditor.processNode(pmTemplateEditor.workingTemplate);
							if(pmTemplateEditor.workingTemplate) {
								Ext.Msg.prompt(pmI18n.labelEdit, pmI18n.labelTitle, function(btn, text){
									if (btn == 'ok'){
										if (text == '') {
											Ext.Msg.alert(pmI18n.error, pmI18n.templateNameIsEmpty);
											return;
										}
										if (Ext.getCmp('pmTemplateEditorTree').getRootNode().findChild('text', text)) {
											Ext.Msg.alert(pmI18n.error, pmI18n.templateNameExists);
											return;
										}
										var node = pmTemplateEditor.workingTemplate;
										node.setText(text);
										node.attributes.text = text;
										node.select();
										pmTemplateEditor.processNode(node);
										Ext.getCmp('pmTemplateEditorSaveButton').enable();
									}
								});
							}
						}
					},
					{
						xtype: 'button',
						text: pmI18n.labelRemove,
						disabled: true,
						id: 'pmTemplateEditorRemoveButton',
						handler: function() {
							pmTemplateEditor.processNode(pmTemplateEditor.workingTemplate);
							Ext.getCmp('pmTemplateEditorTree').getRootNode().removeChild(pmTemplateEditor.workingTemplate, true);
							pmTemplateEditor.workingTemplate = false;
							Ext.getCmp('pmTemplateEditorSaveButton').enable();
						}
					},
					{
						xtype: 'tbspacer',
						width: 60
					},
					{
						xtype: 'button',
						text: pmI18n.save,
						disabled: true,
						id: 'pmTemplateEditorSaveButton',
						handler: function() {
							pmTemplateEditor.saveNodes();
						}
					},
					{
						xtype: 'button',
						text: pmI18n.cancel,
						handler: function() {
							pmTemplateEditor.close();
							pmTemplateEditor = false;
						}
					}
					],
					items: [
					{
						xtype: 'treepanel',
						region: 'west',
						id: 'pmTemplateEditorTree',
						width: 160,
						rootVisible: false,
						useArrows: true,
						margins: '5 0 5 5',
						dataUrl: BsCore.buildRemoteString('PermissionManager', 'getTemplateData'),
						root: {
							text: 'Tree Node'
						},
						listeners: {
							click: function(node, event) {
								pmTemplateEditor.processNode(node);
								Ext.getCmp('pmTemplateEditorEditButton').enable();
								Ext.getCmp('pmTemplateEditorRemoveButton').enable();
							}
						}
					},
					{
						xtype: 'panel',
						region: 'center',
						layout: 'border',
						border: false,
						items: [
						{
							xtype: 'form',
							layout: 'form',
							labelAlign: 'top',
							region: 'center',
							margins: '5 5 5 5',
							border: false,
							items: [
							{
								xtype: 'textarea',
								id: 'pmTemplateEditorDescriptionArea',
								fieldLabel: pmI18n.labelDescription,
								anchor: '100%',
								height: 71,
								listeners: {
									focus: function() {
										Ext.getCmp('pmTemplateEditorSaveButton').enable();
									}
								}
							}
							]
						},
						{
							xtype: 'panel',
							region: 'south',
							width: 100,
							height: 250,
							bodyCssClass: 'pmTemplateEditorDataView',
							id: 'pmTemplateEditorDataView',
							margins: '5 5 5 5',
							autoScroll: true,
							items: [{
								xtype: 'dataview',
								store: new Ext.data.ArrayStore({
									url: BsCore.buildRemoteString('PermissionManager', 'getPermissionArray'),
									fields: ['permission', 'value'],
									autoLoad: true
								}),
								tpl: new Ext.XTemplate(
									'<tpl for=".">',
									'<div class="wrap">',
									'<label><input type="checkbox" name="{permission}" /> {permission}</label>',
									'</div>',
									'</tpl>'
									),
								listeners: {
									click: function(view, index, node, event) {
										Ext.getCmp('pmTemplateEditorSaveButton').enable();
									}
								}
							}]
						}
						]
					}
					],
					processNode: function(node) {
						var permissions = document.getElementById('pmTemplateEditorDataView').getElementsByTagName('input');
						if(pmTemplateEditor.workingTemplate != false) {
							var template = pmTemplateEditor.workingTemplate;
							for(var i=0; i<permissions.length; i++) {
								var match = BsCore.array_search(permissions[i].name, template.attributes.pm);
								if(permissions[i].checked) {
									if(match === false) {
										template.attributes.pm.push(permissions[i].name);
									}
								}
								else {
									while(match !== false) {
										template.attributes.pm.splice(match, 1);
										match = BsCore.array_search(permissions[i].name, template.attributes.pm);
									}
								}
								permissions[i].checked = false;
							}
							template.attributes.desc = Ext.getCmp('pmTemplateEditorDescriptionArea').getValue();
						}
						if(node != false) {
							for(var i=0; i<permissions.length; i++) {
								if(BsCore.array_search(permissions[i].name, node.attributes.pm)) {
									permissions[i].checked = true;
								}
							}
							Ext.getCmp('pmTemplateEditorDescriptionArea').setValue(node.attributes.desc);
						}
						pmTemplateEditor.workingTemplate = node;
					},
					saveNodes: function() {
						pmTemplateEditor.processNode(pmTemplateEditor.workingTemplate);
						Ext.getCmp('pmTemplateEditorTree').getRootNode().eachChild(function(node) {
							var obj = {
								id: node.attributes.id,
								text: node.attributes.text,
								pm: node.attributes.pm,
								desc: node.attributes.desc
							};
							pmTemplateEditor.saveMap.push(obj);
						});
						if(!pmTemplateEditor.loadMask) {
							pmTemplateEditor.loadMask = new Ext.LoadMask(pmTemplateEditor.body);
						}
						pmTemplateEditor.loadMask.show();
						Ext.Ajax.request({
							url: BsCore.buildRemoteString('PermissionManager', 'setTemplateData'),
							success: function(response, opts) {
								var obj = Ext.decode(response.responseText);
								pmPivotGrid.store.load();
								Ext.Msg.alert(pmI18n.state, obj.msg);
								Ext.getCmp('pmTemplateEditorSaveButton').disable();
								pmTemplateEditor.loadMask.hide();
								pmTemplateEditor.saveMap = [];
							},
							failure: function(response, opts) {
							// CR MRG (01.07.11 13:02): i18n
							// CR MRG (01.07.11 13:02): console raus, geht nicht im IE
							//console.log('server-side failure with status code ' + response.status);
							},
							params: {
								saveMap: Ext.encode(pmTemplateEditor.saveMap)
							}
						});
					}
				});
			}
			pmTemplateEditor.show();
		}
	});
}, window, {
	delay: 500
});
