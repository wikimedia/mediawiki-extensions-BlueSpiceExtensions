// Register buttons with hwactions plugin of VisualEditor
$(document).bind('hwactions-init', function( event, plugin, buttons, commands ){
	var t = plugin;

	buttons.push({
		buttonId: 'hwcategory',
		buttonConfig: {
			title : mw.msg('bs-insertcategory-button_title'),
			cmd : 'mceHwCategory',
			image :wgScriptPath+'/extensions/BlueSpiceExtensions/InsertCategory/images/hwcategory.gif'
		}
	});

	commands.push({
		commandId: 'mceHwCategory',
		commandCallback: function() {
			CatChooser.data.href = false;
			CatChooser.data.selection = false;

			node = t.editor.selection.getNode();
			link = t.editor.dom.getParent(node, "a");
			if (link) {
				t.editor.selection.select(link);
				CatChooser.data.href = t.editor.dom.getAttrib(link, "href");
				CatChooser.data.link = link;
			}

			CatChooser.data.selection = t.editor.selection.getBookmark();
			CatChooser.show(true);
		}
	});
	
	plugin.editor.onNodeChange.add(function(ed, cm, element, c, o) {
		if (element.nodeName == 'A') {
			if ( t.elementIsCategoryAnchor( element ) ) {
				cm.setActive(  'hwcategory', true);
				cm.setDisabled('hwcategory', false);
			}
			else if ( t.elementIsMediaAnchor( element ) ) {
				cm.setActive(  'hwcategory', false);
				cm.setDisabled('hwcategory', true);
			}
			else {
				cm.setActive(  'hwcategory', false);
				cm.setDisabled('hwcategory', true);
			}
		}
		else {
			cm.setActive('hwcategory', false);
		}
	});
});

CatChooser = {
	data: {},
	bEditMode: true,
	show: function(bEditMode) {
		//this.win = false;
		this.bEditMode = bEditMode;
		this.treePanelLoadMask = false;

		$(document).trigger( 'BSInsertCategoryBeforeMaybeInitWindow', [ this ] );
		if(!this.win) {
			this.store = new Ext.data.Store({
				// explicitly create reader
				reader: new Ext.data.ArrayReader(
				{
					idIndex: 0
				}, // id for each record will be the first element
				Ext.data.Record.create(
					[ {
						name: 'category'
					}, {
						name: 'delete'
					} ]
					)
				)
			});

			// create the Grid
			this.grid = new Ext.grid.GridPanel({
				xtype: 'grid',
				id: 'bs-ic-cat-grid',
				title: mw.msg('bs-insertcategory-selected_cats'),
				region: 'center',
				width: 285,
				store: this.store,
				enableHdMenu: false,
				hideHeaders: true,
				selModel: new Ext.grid.CellSelectionModel({
					listeners: {
						cellselect: {
							fn: function(model, row, cell){
								if(cell == 1) {
									this.store.remove(this.store.getAt(row));
								}
							},
							scope:this
						}
					}
				}),
				colModel: new Ext.grid.ColumnModel({
					columns: [{
						id:'cat',
						dataIndex: 'category'
					}, {
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
				autoExpandColumn: 'cat'
			});

			var cfg = {
				title: mw.msg('bs-insertcategory-title'),
				id: 'cat_chooser_dlg',
				width: 500,
				height: 380,
				layout: 'border',
				modal: true,
				closeAction: 'hide',
				buttons: [{
					id: 'bs-ic-ok-btn',
					text: mw.msg('bs-insertcategory-ok'),
					handler: function(e) {
						this.doCallback(this.store.getRange(0, this.store.getCount()));
					},
					scope: this
				},{
					text: mw.msg('bs-insertcategory-cancel'),
					handler: function(){
						this.win.hide();
					},
					scope: this
				}],
				items: [
				{
					collapsible: false,
					title: mw.msg('bs-insertcategory-avail_cats'),
					xtype: 'treepanel',
					id: 'treeCategory',
					region: 'west',
					width: 201,
					autoScroll: true,
					useArrows: true,
					dataUrl: BsCore.buildRemoteString('InsertCategory', 'getCategory'),
					singleExpand: false,
					root: {
						nodeType: 'async',
						text: 'root',
						draggable: false,
						id: 'root',
						expanded: true
					},
					rootVisible: false,
					listeners: {
						click: function(n) {
							var path = n.getPath('text');
							var chunks = path.split("/");
							if(!bsInsertCategoryWithParents) {
								var newchunks = new Array();
								newchunks[0] = chunks.pop(chunks);
								chunks = newchunks;
							}
							for(var i in chunks) {
								if(chunks[i] == '+++ REKURSION +++') {
									return;
								}
								if(chunks[i] == 'root' || chunks[i] == '' || typeof(chunks[i]) != 'string') {
									continue;
								}
								var search = this.store.find('category', chunks[i]);
								if(this.store.getCount() > 0) {
									var match = false;
									var recs = this.store.getRange(0, this.store.getCount());
									for(var j=0; j<recs.length && match == false; j++) {
										if(recs[j].get('category') == chunks[i]) {
											match = true;
										}
									}
								}
								if(search == -1 || match == false) {
									var defaultData = {
										category: chunks[i],
										'delete': 'X'
									};
									var recId = this.store.getCount(); // provide unique id
									var p = new this.store.recordType(defaultData, recId); // create new record
									this.store.add(p, recId);
								}
							}
						},
						render: function( treepanel ) {
							if( this.treePanelLoadMask != false ) return;
							this.treePanelLoadMask = new Ext.LoadMask( treepanel.getEl() );
						},
						load : function( node) {
							if( this.treePanelLoadMask ) {
								if (this.treePanelLoadMaskTask) {
									this.treePanelLoadMaskTask.cancel();
								}
								this.treePanelLoadMask.hide();
							}
						},
						beforeload: function( node ) {
							if( !this.treePanelLoadMask ) return;
							if (!this.treePanelLoadMaskTask) {
								this.treePanelLoadMaskTask = new Ext.util.DelayedTask(
									function() { this.treePanelLoadMask.show(); },
									this
								);
							}
							this.treePanelLoadMaskTask.delay( 150 );
						},
						scope:this
					}
				},
				this.grid, // CR RBV (30.06.11 09:20): Wäre es nicht übersichtlicher die anderen Items ebenfalls in this.abc Variablen zu initialisieren. Das würde das Items-Array lesbarer machen.
				{
					xtype: 'panel',
					region: 'south',
					layout: 'border',
					height: 130,
					border: false,
					items: [
					{
						xtype: 'panel',
						region: 'center',
						bodyCssClass: 'bs_description_class',
						html: '<p>' + mw.msg('bs-insertcategory-tb_1') + '</p>'
					},
					{
						xtype: 'form',
						layout: 'absolute',
						bodyCssClass: 'bs_description_class',
						region: 'east',
						width: 285,
						labelAlign: 'top',
						items: [
						{
							xtype: 'label',
							text: mw.msg('bs-insertcategory-new_category'),
							x: 10,
							y: 5,
							style: 'font-size: 12px;'
						},
						{
							xtype: 'textfield',
							id: 'newCategory',
							x: 10,
							y: 25,
							width: 160
						},
						{
							xtype: 'button',
							id: 'bs-ic-new-category',
							text: mw.msg('bs-insertcategory-new_category_btn'),
							handler: function() {
								var term = Ext.get('newCategory').getValue();
								//console.log(term);
								if(term.trim() == '') {
									return;
								}
								var search = this.store.find('category', term);
								if(this.store.getCount() > 0) {
									var match = false;
									var recs = this.store.getRange(0, this.store.getCount());
									for(var j=0; j<recs.length && match == false; j++) {
										if(recs[j].get('category') == term) {
											match = true;
										}
									}
								}
								//console.log(search);
								if(search == -1 || match == false) {
									var defaultData = {
										category: term,
										'delete': 'X'
									};
									var recId = this.store.getCount(); // provide unique id
									var p = new this.store.recordType(defaultData, recId); // create new record
									this.store.add(p, recId);
								}
								Ext.getCmp('newCategory').setValue('');
							},
							scope: this,
							x: 180,
							y: 25,
							width: 90
						}
						]
					},
					{
						xtype: 'panel',
						region: 'south',
						bodyCssClass: 'bs_description_class',
						html: '<p><i>' + mw.msg('bs-insertcategory-tb_2') + '</i></p>',
						height: 65
					}
					]
				}
				]
			}
			this.win = new Ext.Window(cfg);
			this.win.on( 'hide', function(){
				if(bEditMode) {
					BsCore.restoreScrollPosition();
				}
			}, this );
			//Ext.getCmp('panelCatTree').body.innerHTML = '<ul id="panelCatTreeList"></ul>';
			$(document).trigger( 'BSInsertCategoryAfterInitWindow', [ this ] );
		}
		if(bEditMode) {
			BsCore.saveScrollPosition();
			BsCore.saveSelection();
			// TODO MRG (24.09.10 13:17): Hier gibt es evtl mehrere Möglichkeiten. im Deutschen z.B. Kategorie und Category.
			// Das muss in bsInsertCategoryCategoryNamespaceName reflektiert werden, am besten mit Alternativen (Kategorie|Category).
			// Aber vorsicht, weiter unten wird der Inhalt eingefügt. Das passt nicht. Evtl. brauchen wir hier
			// zwei Variablen.
			// TODO MRG (24.09.10 13:19): Warum kein i als flag?
			var myregexp = new RegExp('\\[\\['+bsInsertCategoryCategoryNamespaceName+':(.+?)\\]\\]', 'g');
			//var myregexp = /\[\[(?:k|c)ategor(?:ie|y):(.*)\]\]/ig;
			var match;
			this.store.removeAll();
			// CR RBV (30.06.11 09:26): Variable VisualEditorMode in bsVisualEditorMode ändern
			if ((typeof(VisualEditorMode)=="undefined") || !VisualEditorMode ) {
				_match = myregexp.exec(orig_text); // CR RBV (30.06.11 09:27): _match ist global?
				while (_match != null) {
					term = _match[1];
					if(term.trim() == '') {
						return;
					}
					var search = this.store.find('category', term);
					if(this.store.getCount() > 0) {
						var match = false;
						var recs = this.store.getRange(0, this.store.getCount());
						for(var j=0; j<recs.length && match == false; j++) {
							if(recs[j].get('category') == term) {
								match = true;
							}
						}
					}
					//console.log(search);
					if(search == -1 || match == false) {
						var defaultData = {
							category: term,
							'delete': 'X'
						};
						var recId = this.store.getCount(); // provide unique id
						var p = new this.store.recordType(defaultData, recId); // create new record
						this.store.add(p, recId);
					}
					_match = myregexp.exec(orig_text);
				}
			}
			else {
				_match = myregexp.exec(tinyMCE.activeEditor.getContent());
				while (_match != null) {
					term = _match[1];
					if(term.trim() == '') {
						return;
					}
					var search = this.store.find('category', term);
					if(this.store.getCount() > 0) {
						var match = false;
						var recs = this.store.getRange(0, this.store.getCount());
						for(var j=0; j<recs.length && match == false; j++) {
							if(recs[j].get('category') == term) {
								match = true;
							}
						}
					}
					//console.log(search);
					if(search == -1 || match == false) {
						var defaultData = {
							category: term,
							'delete': 'X'
						};
						var recId = this.store.getCount(); // provide unique id
						var p = new this.store.recordType(defaultData, recId); // create new record
						this.store.add(p, recId);
					}
					_match = myregexp.exec(tinyMCE.activeEditor.getContent());
				}
			}
		}
		else {
			var store = this.store;
			$.each(wgCategories, function(index, element) {
				if(element.trim() == '') {
					return;
				}
				var search = store.find('category', element);
				if(store.getCount() > 0) {
					var match = false;
					var recs = store.getRange(0, store.getCount());
					for(var j=0; j<recs.length && match == false; j++) {
						if(recs[j].get('category') == element) {
							match = true;
						}
					}
				}
				//console.log(search);
				if(search == -1 || match == false) {
					var defaultData = {
						category: element,
						'delete': 'X'
					};
					var recId = store.getCount(); // provide unique id
					var p = new store.recordType(defaultData, recId); // create new record
					store.add(p, recId);
				}
			});
		}
		this.win.show();
	},

	doCallback : function(recs) {
		this.win.hide();
		var tags = '';
		for(var i=0; i<recs.length; i++) {
			// TODO MRG (24.09.10 13:20): vgl. Kommentar in Z. 218
			tags = tags + "[[" + bsInsertCategoryCategoryNamespaceName + ':' + recs[i].get('category') + "]]\n";
		}
		this.insertCategory(tags);
	},

	addCategoryToPanel : function(name) {
		Ext.get('panelCatTreeList').innerHTML =+ '<li>' + name + '</li>';
	},

	insertCategory : function(tags)
	{
		//console.log(tags);
		if(this.bEditMode) {
			// TODO MRG (24.09.10 13:21): hier muss bsInsertCategoryCategoryNamespaceName berücksichtigt werden.
			var regexCat = /(<br \/>)*\[\[(?:k|c)ategor(?:ie|y):(.)+?\]\]\n?/ig;
			if ((typeof(VisualEditorMode)=="undefined") || !VisualEditorMode )
			{

				orig_text = orig_text.replace(regexCat, ""); // CR RBV (30.06.11 09:27): orig_text sollte nicht global sein.
				//insertTags(cOpener, cCloser, text);
				BsCore.restoreSelection(tags, 'append');
				BsCore.restoreScrollPosition();
			}
			else
			{
				tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent().replace(regexCat, "", false));
				//create an element at the end of the text and replace it with new category content
				ele = tinyMCE.activeEditor.dom.add(tinyMCE.activeEditor.getBody(), 'p', {}, 'hw_category_marker');
				tinyMCE.activeEditor.selection.select(ele);
				tinyMCE.execCommand('mceInsertRawHTML', false, tags);
				//restore original selection
				tinyMCE.activeEditor.selection.moveToBookmark(this.data.selection);
			}
		}
		else {
			Ext.Ajax.request({
				url: BlueSpice.buildRemoteString('InsertCategory', 'addCategoriesToArticle', {
					"pid":wgArticleId
				} ),
				success: function( response, opts ) {
					var obj = Ext.decode(response.responseText);
					if(obj.success) {
						Ext.Msg.alert('Status', mw.msg('bs-insertcategory-success'));
						window.location.reload( false );
					}
					else {
						Ext.Msg.alert(mw.msg('bs-insertcategory-failure'), obj.msg);
					}
				},
				failure: function() {},
				params: {
					page_name: wgPageName,
					tags: tags
				}
			});
		}
	}
};