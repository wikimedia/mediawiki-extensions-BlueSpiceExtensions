Ext.define( 'BS.InsertFile.BaseDialog', {
	extend: 'BS.Window',
	requires: [
		'Ext.data.Store', 'Ext.form.TextField', 'Ext.ux.form.SearchField',
		'BS.InsertFile.UploadDialog', 'Ext.Button', 'Ext.toolbar.Toolbar',
		'Ext.grid.Panel', 'Ext.form.Panel'
	],
	modal: true,
	bodyPadding: 0,
	width: 700,
	height: 500,
	layout: 'border',

	storeFileType: 'file',
	isSetData: false,

	configPanel: {
		fieldDefaults: {
			labelAlign: 'right',
			anchor: '100%'
		},
		collapsible: true,
		collapsed: true,
		title: mw.message('bs-insertfile-details-title').plain(),
		region: 'south',
		height: 100,
		bodyPadding: 5,
		layout: 'anchor',
		items: []
	},

	//HINT: 4.2.1/examples/grid/infinite-scroll-with-filter.js
	afterInitComponent: function() {
		this.conf = {
			columns: {
				items: [{
					dataIndex: 'img_thumbnail',
					renderer: this.renderThumb,
					width: 56,
					sortable: false
				},{
					text: mw.message('bs-insertfile-filename').plain(),
					dataIndex: 'img_name',
					flex: 1
				},{
					text: mw.message('bs-insertfile-filesize').plain(),
					dataIndex: 'img_size',
					renderer:this.renderSize,
					width: 100
				},{
					text: mw.message('bs-insertfile-lastmodified').plain(),
					dataIndex: 'page_touched',
					renderer:this.renderLastModified,
					width: 150
				}],
				defaults: {
					tdCls: 'bs-if-cell'
				}
			}
		};

		this.stImageGrid = Ext.create('Ext.data.Store', {
			height: 200,
			buffered: true, // allow the grid to interact with the paging scroller by buffering
			pageSize: 20,
			leadingBufferZone: 60,
			proxy: {
				type: 'ajax',
				url: mw.util.wikiScript('api'),
				reader: {
					type: 'json',
					root: 'results',
					idProperty: 'img_name',
					totalProperty: 'total'
				},
				extraParams: {
					format: 'json',
					action: 'bs-filebackend-store',
					filter: Ext.encode([{
						type: 'string',
						comparison: 'eq',
						field: 'img_major_mime',
						value: this.storeFileType
					}])
				}
			},
			remoteFilter: true,
			autoLoad: true,
			fields: ['img_name', 'page_touched', 'img_thumbnail', 'img_size', 'img_width', 'img_height' ],
			sortInfo: {
				field: 'page_touched',
				direction: 'ASC'
			}
		});
		this.stImageGrid.on( 'load', this.onStImageGridLoad, this );


		this.sfFilter = Ext.create( 'Ext.ux.form.SearchField', {
			fieldLabel: mw.message('bs-insertfile-labelfilter').plain(),
			width: 500,
			labelWidth: 50,
			store: this.stImageGrid,
				listeners: {
					change: function( field, newValue, oldValue, eOpts ) {
						field.onTrigger2Click();
						return true;
					}
				}
		});

		this.dlgUpload = Ext.create('BS.InsertFile.UploadDialog',{
			title: mw.message('bs-insertfile-labelupload').plain(),
			id: this.getId()+'-upload-dlg',
			allowedFileExtensions: this.allowedFileExtensions
		});

		this.dlgUpload.on( 'ok', this.dlgUploadOKClick, this );

		this.btnUpload = Ext.create('Ext.Button',{
			text: mw.message('bs-insertfile-labelupload').plain()
			//glyph: 72
			//iconCls: ''
		});

		this.btnUpload.on( 'click', this.btnUploadClick, this );

		var toolBarItems = [
			this.sfFilter
		];

		if( mw.config.get('bsEnableUploads') ) {
			toolBarItems.push( '->' );
			toolBarItems.push( this.btnUpload );
		}

		this.tbGridTools = Ext.create('Ext.toolbar.Toolbar', {
			dock: 'top',
			items: toolBarItems
		});

		this.gdImages = Ext.create('Ext.grid.Panel', {
			region: 'center',
			collapsible: false,
			store: this.stImageGrid,
			loadMask: true,
			dockedItems: this.tbGridTools,
			selModel: {
				pruneRemoved: false
			},
			viewConfig: {
				trackOver: false,
				emptyText: mw.message('bs-insertfile-nomatch').plain()
			},
			columns: this.conf.columns
		});

		this.gdImages.on( 'select', this.onGdImagesSelect, this );

		this.tfFileName = Ext.create('Ext.form.TextField', {
			readOnly: true,
			fieldLabel: mw.message('bs-insertfile-filename').plain()
		});

		this.tfLinkText = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-insertfile-linktext').plain()
		});

		this.configPanel.items.unshift(this.tfLinkText);
		this.configPanel.items.unshift(this.tfFileName);
		this.tfFileName.on('change', this.onTfFileNameChange, this);

		this.pnlConfig = Ext.create('Ext.form.Panel', this.configPanel );
		this.pnlConfig.on('expand', this.onPnlConfigExpand, this);

		this.items = [
			this.gdImages,
			this.pnlConfig
		];

		this.callParent(arguments);
	},

	onStImageGridLoad: function( store, records, successful, eOpts ) {
		//Only if we have a exact match selected
		if( store.filters.items.length > 0 && records.length === 1 ) {
			this.gdImages.getSelectionModel().select(0);
		}
	},

	onTfFileNameChange: function( textfield, newValue, oldValue, eOpts ){
		$(document).trigger("BSInsertFileConfigPanelFileNameChange", [this, textfield, newValue, oldValue, eOpts]);
	},

	onPnlConfigExpand: function(panel, eOpts){
		$(document).trigger("BSInsertFileConfigPanelExpand", [this, panel, eOpts]);
	},

	btnUploadClick: function( sender, event ) {
		this.dlgUpload.show();
	},

	dlgUploadOKClick: function( dialog, upload ){
		this.stImageGrid.reload();
		this.sfFilter.setValue( upload.filename );
	},

	getData: function() {
		var cfg = {
			title: this.tfFileName.getValue(),
			displayText: this.tfLinkText.getValue()
		};
		return cfg;
	},

	setData: function( obj ) {
		//Reset all fields. maybe do this onOKClick
		this.sfFilter.reset();
		this.tfFileName.reset();
		this.tfLinkText.reset();

		if( obj.title ) {
			this.tfFileName.setValue( obj.title );
			this.sfFilter.setValue( obj.title );
			this.sfFilter.onTrigger2Click();
			this.pnlConfig.expand( false );
		}
		else{
			this.stImageGrid.clearFilter();
			this.pnlConfig.collapse();
		}

		if( obj.displayText ) {
			this.tfLinkText.setValue( obj.displayText );
		}
		this.callParent( arguments );
	},

	renderThumb: function( url ) {
		return '<img src="'+url+'" height="48" width="48" />';
	},

	renderSize: function( size ){
		return Ext.util.Format.fileSize( size );
	},

	renderLastModified: function( lastmod ){
		//mw timestamp to date params
		var aDate = lastmod.match(new RegExp('.{1,2}', 'g'));
		return Ext.Date.format(
			new Date(aDate[0] + aDate[1], aDate[2], aDate[3], aDate[4], aDate[5], aDate[6] ),
			'd.m.Y G:i'
		);
	},

	onGdImagesSelect: function( grid, record, index, eOpts ){
		this.tfFileName.setValue( record.get('img_name') );
		this.pnlConfig.expand();
	},

	getSingleSelection: function() {
		var selectedRecords = this.gdImages.getSelectionModel().getSelection();
		if( selectedRecords.length > 0) {
			return selectedRecords[0];
		}
		return null;
	}
});
