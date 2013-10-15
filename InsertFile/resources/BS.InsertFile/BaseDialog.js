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
		title: mw.message('bs-insertfile-tabTitle1').plain(),
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
					dataIndex: 'url',
					renderer: this.renderThumb,
					width: 56,
					sortable: false
				},{
					text: mw.message('bs-insertfile-fileName').plain(),
					dataIndex: 'name',
					flex: 1
				},{
					text: mw.message('bs-insertfile-fileSize').plain(),
					dataIndex: 'size',
					renderer:this.renderSize,
					width: 100
				},{
					text: mw.message('bs-insertfile-lastModified').plain(),
					dataIndex: 'lastmod',
					renderer:this.renderLastModified,
					width: 150
				}],
				defaults: {
					tdCls: 'bs-if-cell'
				}
			}
		}
		
		this.stImageGrid = Ext.create('Ext.data.Store', {
			height: 200,
			buffered: true, // allow the grid to interact with the paging scroller by buffering
			pageSize: 20,
			leadingBufferZone: 60,
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl('InsertFileAJAXBackend::getFiles'),
				reader: {
					type: 'json',
					root: 'images',
					idProperty: 'name'
				},
				extraParams: {
					type: this.storeFileType
				}
			},
			remoteFilter: true,
			autoLoad: true,
			fields: ['name', 'lastmod', 'url', 'size', 'width', 'height' ],
			sortInfo: {
				field: 'lastmod',
				direction: 'ASC'
			}
		});
		
		this.sfFilter = Ext.create( 'Ext.ux.form.SearchField', {
			fieldLabel: mw.message('bs-insertfile-labelFilter').plain(),
			width: 500,
			labelWidth: 50,
			store: this.stImageGrid
		});
		
		this.dlgUpload = Ext.create('BS.InsertFile.UploadDialog',{
			title: mw.message('bs-insertfile-labelUpload').plain(),
			id: this.getId()+'-upload-dlg',
			allowedFileExtensions: this.allowedFileExtensions
		});
		
		this.dlgUpload.on( 'ok', this.dlgUploadOKClick, this );
		
		this.btnUpload = Ext.create('Ext.Button',{
			text: mw.message('bs-insertfile-labelUpload').plain()
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
				emptyText: mw.message('bs-insertfile-noMatch').plain()
			},
			columns: this.conf.columns
		});
		
		this.gdImages.on( 'select', this.onGdImagesSelect, this );
		
		this.tfFileName = Ext.create('Ext.form.TextField', {
			readOnly: true,
			fieldLabel: mw.message('bs-insertfile-fileName').plain()
		});
		
		this.tfLinkText = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-insertfile-linktext').plain()
		});
		
		this.configPanel.items.unshift(this.tfLinkText);
		this.configPanel.items.unshift(this.tfFileName);

		this.pnlConfig = Ext.create('Ext.form.Panel', this.configPanel );
		
		this.items = [
			this.gdImages,
			this.pnlConfig
		]
		
		this.callParent(arguments);
	},
	
	btnUploadClick: function( sender, event ) {
		this.dlgUpload.show();
	},
	
	dlgUploadOKClick: function( dialog, upload ){
		this.sfFilter.setValue( upload.filename );
		this.stImageGrid.reload();
	},
	
	getData: function() {
		var cfg = {
			title: this.tfFileName.getValue(),
			displayText: this.tfLinkText.getValue()
		}
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
		/*return mw.html.element( 
			'img',
			{ 
				src: url,
				height: 48,
				width: 48
			}
		);*/
		return '<img src="'+url+'" height="48" width="48" />';
	},
	
	renderSize: function( size ){
		if(size < 1024) {
			return size + " " + mw.message('bs-insertfile-bytes').plain();
		} else {
			return (
				Math.round(
					((size*10) / 1024))/10
			) + " " + mw.message('bs-insertfile-kilobytes').plain();
		}
	},
	
	renderLastModified: function( lastmod ){
		return Ext.Date.format(
			new Date(lastmod * 1000), 
			mw.message('bs-insertfile-dateformat').plain()
		);
	},
	
	onGdImagesSelect: function( grid, record, index, eOpts ){
		this.tfFileName.setValue( record.get('name') );
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