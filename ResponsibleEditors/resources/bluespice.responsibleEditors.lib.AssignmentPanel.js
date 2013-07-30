Ext.namespace('biz', 'biz.hallowelt', 'biz.hallowelt.ResponsibleEditors');

biz.hallowelt.ResponsibleEditors.AssignmentPanel = Ext.extend(Ext.Panel, {
	layout:'column',
	border: false,

	initComponent: function() {
		this.astrAssignedEditors = new Ext.data.ArrayStore({
			url: wgScriptPath + '/index.php',
			autoLoad: true,
			baseParams: {
				action: 'ajax',
				rs: 'SpecialResponsibleEditors::ajaxGetResponsibleEditors',
				'rsargs[]': this.articleData.articleId
			},
			idProperty: 'id',
			fields: ['id', 'name', 'delete']
		});

		this.gdAssignedEditors = new Ext.grid.GridPanel({
			title: mw.message('bs-responsibleeditors-assignedEditors').plain(),
			hideHeaders: true,
			columnWidth: .5,
			height: 256,
			store: this.astrAssignedEditors,
			loadMask: true,
			enableHdMenu: false,
			selModel: new Ext.grid.CellSelectionModel({
				listeners: {
				cellselect: this.onCellSelect,
				scope: this
				}
			}),
			colModel: new Ext.grid.ColumnModel({
				columns: [{
					id:'editorname',
					dataIndex: 'name'
				},
				{
					id: 'delete',
					'class': 'bs-ic-delete-cat-btn',
					align: 'center',
					dataIndex: 'delete',
					width: 20,
					css: 'font-weight: bold; color: #F00; cursor: pointer;'
				}],
				defaults: {
					sortable: true
				}
			}),
			autoExpandColumn: 'editorname'
		});

		this.tlAvailableEditors = new Ext.tree.TreeLoader({
			url: wgScriptPath + '/index.php',
			baseParams: {
				action: 'ajax',
				rs: 'SpecialResponsibleEditors::ajaxGetPossibleEditors'
			},
			baseAttrs: {
				iconCls: 'icon-user'
			}
		});
		this.tlAvailableEditors.addListener( 'beforeload', function(loader, node) {
				loader.baseParams['rsargs[]'] = this.articleData.articleId;
			}, this);

		this.tpAvailableEditors = new Ext.tree.TreePanel({
			title: mw.message('bs-responsibleeditors-availableEditors').plain(),
			columnWidth: .5,
			height: 256,
			autoScroll: true,
			useArrows: true,
			loader: this.tlAvailableEditors,
			loadMask: true,
			singleExpand: false,
			root: {
				nodeType: 'async',
				text: 'root',
				draggable: false,
				id: 'editors',
				expanded: true
			},
			rootVisible: false,
			listeners: {
				click: this.onClick,
				scope:this
			}
		});

		this. items = [
			this.tpAvailableEditors,
			this.gdAssignedEditors
		];

		this.addEvents( 'saved' );

		biz.hallowelt.ResponsibleEditors.AssignmentPanel.superclass.initComponent.call(this);
	},

	onCellSelect: function(model, row, cell){
		if(cell == 1) {
			//This removes the selected editor from the list
			model.grid.store.remove(model.grid.store.getAt(row));
		}
	},

	onClick : function(node) {
		var path = node.getPath('text');
		var chunks = path.split("/");
		for(var i in chunks) {
			if(chunks[i] == 'root' || chunks[i] == '' || typeof(chunks[i]) != 'string') {
			continue;
			}
			var search = this.astrAssignedEditors.find('name', chunks[i]);
			if(this.astrAssignedEditors.getCount() > 0) {
				var match = false;
				var recs = this.astrAssignedEditors.getRange(
					0,
					this.astrAssignedEditors.getCount()
				);
				for(var j=0; j<recs.length && match == false; j++) {
					if(recs[j].get('name') == chunks[i]) {
						match = true;
					}
				}
			}
			if(search == -1 || match == false) {
			var defaultData = {
				id: node.attributes.editorId,
				name: chunks[i],
				'delete': 'X'
			};// CR RBV (30.06.11 09:18): Warum ist das 'delete' in Hochkommas, category aber nicht? Brauchen wir eine Coding Convention diesbezüglich?
			var recId = this.astrAssignedEditors.getCount(); // provide unique id
			var p = new this.astrAssignedEditors.recordType(defaultData, recId); // create new record
			this.astrAssignedEditors.add(p, recId);
			}
		}
	},

	save: function () {
		var records = this.astrAssignedEditors.getRange(
					0,
					this.astrAssignedEditors.getCount()
		);
		var editorIds = [];
		for(var i = 0; i < records.length; i++) {
			editorIds.push( records[i].data.id );
		}

		Ext.Ajax.request({
			url: wgScriptPath + '/index.php?action=ajax&rs=SpecialBookshelfBookManager::ajaxSetResponsibleEditors',
			method: 'post',
			params: {
				'action' : 'ajax',
				'rs' : 'SpecialResponsibleEditors::ajaxSetResponsibleEditors',
				'rsargs[]' : Ext.encode({
					articleId: this.articleData.articleId,
					'editorIds': editorIds
				})
			},
			success: function() {
				this.fireEvent( 'saved', this );
				//Ext.Msg.alert('Status', BsBookshelfBookManagerI18n.statusSuccess)
			},
			failure: function(response) {
				Ext.Msg.alert('Status', Ext.decode(response.responseText).msg);
			},
			scope: this
		});
	},
	//Loadmask cannot be created in initComponent because the panel is not rendered yet...
	loadMask: false,
	doneLoading: false,
	loadArticle: function( iArticleId ) {
		Ext.apply( this.articleData, { articleId: iArticleId } );
		if( this.loadMask == false ) {
			this.loadMask = new Ext.LoadMask( this.getEl() );
		}
		this.loadMask.show();
		this.doneLoading = false;

		this.astrAssignedEditors.load( {
			params: {
			'rsargs[]': iArticleId
			},
			callback: this.hideLoadMask,
			scope: this
		} );

		this.tlAvailableEditors.load( this.tpAvailableEditors.getRootNode(), this.hideLoadMask, this );
		},

		hideLoadMask: function( record, options, success) {
		if( this.doneLoading ) this.loadMask.hide();
			this.doneLoading = true;
	}
});