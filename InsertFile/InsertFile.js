// Register buttons with hwactions plugin of VisualEditor
$(document).bind('hwactions-init', function( event, plugin, buttons, commands ){
	var t = plugin;
	var ed = t.editor;

	buttons.push({
		buttonId: 'hwimage',
		buttonConfig: {
			title : BsFileManager.i18n.button_image_title,
			cmd : 'mceHwImage',
			image : wgScriptPath+'/extensions/BlueSpiceExtensions/InsertFile/images/hwimage.gif'
		}
	});

	buttons.push({
		buttonId: 'hwfile',
		buttonConfig: {
			title : BsFileManager.i18n.button_file_title,
			cmd : 'mceHwFile',
			image : wgScriptPath+'/extensions/BlueSpiceExtensions/InsertFile/images/hwfile.gif'
		}
	});

	commands.push({
		commandId: 'mceHwImage',
		commandCallback: function() {
			BsFileManager.data.href= false;
			BsFileManager.data.width = 0;
			BsFileManager.data.height = 0;
			BsFileManager.data.alt = '';
			BsFileManager.data.link = '';
			BsFileManager.data.style = 'none';
			BsFileManager.data.type = 'none';
			BsFileManager.data.image = false;
			BsFileManager.data.selection = false;


			var file = t.editor.selection.getNode();

			if (file.src) {
				BsFileManager.data.href = file.src;
				BsFileManager.data.width = file.width;
				BsFileManager.data.height = file.height;
				BsFileManager.data.alt = file.alt;
				if(file.parentNode.nodeName.toLowerCase() == "a") {
					BsFileManager.data.link = file.parentNode.href;
				}
				BsFileManager.data.style = file.style.cssFloat;
				if(!BsFileManager.data.style) {
					BsFileManager.data.style = file.parentNode.style.textAlign;
				}
				BsFileManager.data.type = file.title;
				BsFileManager.data.image = file;
			}

			BsFileManager.data.selection = t.editor.selection.getBookmark();

			parentTag = ed.dom.getParent(file);
			if (parentTag.nodeName.toLowerCase() == 'body') {
				BsFileManager.data.selection.start++;
			}
			BsFileManager.show('image');
		}
	});
	
	commands.push({
		commandId: 'mceHwFile',
		commandCallback: function() {
			BsFileManager.data.href= false;
			BsFileManager.data.width = 0;
			BsFileManager.data.height = 0;
			BsFileManager.data.alt = '';
			BsFileManager.data.link = '';
			BsFileManager.data.style = 'none';
			BsFileManager.data.type = 'none';
			BsFileManager.data.image = false;
			BsFileManager.data.selection = false;


			var file = t.editor.selection.getNode();

			if (file.src)
			{
				BsFileManager.data.href = file.src;
				BsFileManager.data.width = file.width;
				BsFileManager.data.height = file.height;
				BsFileManager.data.alt = file.alt;
				BsFileManager.data.style = file.style.cssFloat;
				if(!BsFileManager.data.style) {
					BsFileManager.data.style = file.parentNode.style.textAlign;
				}
				BsFileManager.data.type = file.title;
				BsFileManager.data.image = file;
			}

			BsFileManager.data.selection = t.editor.selection.getBookmark();

			parentTag = ed.dom.getParent(file);
			if (parentTag.nodeName.toLowerCase() == 'body')
			{
				BsFileManager.data.selection.start++;
			}
			BsFileManager.show('file');
		}
	});
	
	//Override default command "mceImage"
	commands.push({
		commandId: 'mceImage',
		commandCallback: function( ui, v ) {
			this.execCommand( 'mceHwImage', ui );
		}
	});
	
	//Override default command "mceAdvImage"
	commands.push({
		commandId: 'mceAdvImage',
		commandCallback: function( ui, v ) {
			this.execCommand( 'mceHwImage', ui );
		}
	});
	
	ed.onNodeChange.add(function(ed, cm, element, c, o) {
		cm.setActive(  'hwimage', element.nodeName == 'IMG');
		cm.setDisabled('hwimage', element.nodeName == 'A');
		if (element.nodeName == 'A') {
			if ( t.elementIsCategoryAnchor( element ) ) {
				cm.setActive(  'hwfile', false);
				cm.setDisabled('hwfile', true);
			}
			else if ( t.elementIsMediaAnchor( element ) ) {
				cm.setActive(  'hwfile', true);
				cm.setActive(  'hwfile', false); //Why twice?
				cm.setDisabled('hwfile', false);
			}
			else {
				cm.setDisabled('hwfile', true);
			}
		}
	});
});

BsFileManager = {
	lookup: {},
	errors: false,
	uploadedPage: 0,
	uploadedFile: false,
	fileExtensions: false,
	imageExtensions: false,
	storeParams: {
		method: 'POST',
		start: 0,
		limit: 15,
		firstchars: '',
		sort: 'name',
		type: 'image'
	},
	i18n: {
		button_image_title: 'Insert/edit image',
		button_file_title: 'Insert/edit file',
		uploadsDisabled: 'Uploads are disabled in this wiki.',
		noMatch: 'No matches',
		labelSort: 'Sort according to:',
		labelFilter: 'Filter:',
		fileName: 'File name',
		fileSize: 'File size',
		lastModified: 'Recent changes',
		labelOk: 'Ok',
		labelCancel: 'Cancel',
		labelClose: 'Close',
		labelUpload: 'Upload',
		tabTitle1: 'Details',
		labelDimensions: 'Size (w x h):',
		labelAlt: 'Alternative text:',
		labelAlign: 'Adjustment',
		labelLink: 'Link',
		alignNone: 'none',
		alignLeft: 'left',
		alignCenter: 'center',
		alignRight: 'right',
		labelType: 'Decoration',
		typeNone: 'none',
		typeThumb: 'preview picture',
		typeFrame: 'frame',
		typeBorder: 'border',
		tabTitle2: 'Upload',
		uploadButtonText: 'Browse',
		uploadImageEmptyText: 'Choose a new image ...',
		uploadImageFieldLabel: 'Upload new image',
		uploadFileEmptyText: 'Choose a new file ...',
		uploadFileFieldLabel: 'Upload new File',
		uploadDestFileLabel: 'Target name',
		uploadDescFileLabel: 'Description/Source',
		uploadWatchThisLabel: 'Watch this file',
		uploadIgnoreWarningsLabel: 'Ignore alert',
		uploadSubmitValue: 'Upload file',
		specialUpload:  'Special:Upload',
		errorLoading:   'An error occurred on loading files.',
		fileNS:        'Media',
		wrongType:      'The file you chose has no valid filetype.',
		warning:	'Warning',
		warningUpload:	'There is a file, waiting for upload. You really want to close this window?',
		allowedFiletypesAre: 'Valid filetypes are:',
		success: 'Success',
		error:          'Error',
		errorNoFileExtensionOnUpload:	'The file you chose has no file extension.',
		errorNoFileExtensionOnDestination:	'The target name you chose has no file extension.',
		errorWrongFileExtensionOnUpload:	'The file you chose has no valid file extension.',
		errorWrongImageTypeOnUpload:	'The file you chose is not an allowed image type.',
		errorWrongFileTypeOnUpload:	'The file you chose is not an allowed file type.',
		errorWrongFileExtensionOnDestination:	'The target name you chose has no valid file extension.',
		errorWrongImageTypeOnDestination:	'The target name you chose has no allowed image extension.',
		errorWrongFileTypeOnDestination:	'The target name you chose has no allowed file extension.',
		uploadComplete: 'Your file has saved successful.',
		statusNotClear: 'The state of your file is loose. Please check the filelist.',
		bytes:          'Byte',
		kilobytes:      'KB',
		dateformat:     'd.m.Y G:i',
		titleFile:		'Insert file',
		titleImage:		'Insert image',
		imageTag:		'Image',
		fileTag:		'Media',
		noTopicsMessage: 'No topics to display',
		tipKeepRatio: 'Keep proportions',
		pagingToolbarPosition: '{0} - {1} of {2}',
		select_a_link: 'Select or type a link',
		license: 'License'
	},
	data: {
		href: false,
		width: 0,
		height: 0,
		alt: '',
		link: '',
		style: 'none',
		type: 'none',
		image: false,
		selection: false
	},
	win: false,
	cancel: false,
	reload: true,
	checkFileSize: function( ExtCmpId ) {
		if(typeof window.FileReader !== 'undefined') {
			var allowedSize = mw.config.get('bsMaxUploadSize');
			if(allowedSize == undefined || allowedSize.file == undefined) return true;
			var filesize = Ext.getCmp( ExtCmpId ).fileInput.dom.files[0].size;
			if( filesize > allowedSize.file ) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	},
	show: function(filetype) {

		if(this.storeParams.type != filetype) {
			this.reload = true;
			this.storeParams.method = 'POST';
			this.storeParams.start = 0;
			this.storeParams.limit = 15;
			this.storeParams.firstchars = '';
			this.storeParams.sort = 'name';
		}
		this.storeParams.type = filetype;
		this.cancel = false;
		this.dataViewLoadMask = false;
		
		$(document).trigger( 'BSInsertFileBeforeMaybeInitWindow', [ this ] );
		if(!this.win) {
			this.formatSize = function(data){
				if(data.size < 1024) {
					return data.size + " " + this.i18n.bytes;
				} else {
					return (
						Math.round(
							((data.size*10) / 1024))/10
					) + " " + this.i18n.kilobytes;
				}
			};

			this.formatData = function(data){
				data.shortName = data.name.ellipse(15);
				data.sizeString = this.formatSize(data);
				data.dateString = new Date(data.lastmod).format(this.i18n.dateformat);
				this.lookup[data.name] = data;
				return data;
			};

			this.thumbTemplate = new Ext.XTemplate(
				'<tpl for=".">',
				'<div class="thumb-wrap" id="{name}">',
				'<div class="thumb"><img src="{url}" title="{name}" width="80"></div>',
				'<span>{shortName}</span></div>',
				'</tpl>'
				);
			this.thumbTemplate.compile();

			this.detailsTemplate = new Ext.XTemplate(
				'<div class="details">',
				'<tpl for=".">',
				'<img src="{url}" width="80"><div class="details-info">',
				'<b>'+ this.i18n.fileName +'</b>',
				'<span>{name}</span>',
				'<b>'+ this.i18n.fileSize +'</b>',
				'<span>{sizeString}</span>',
				'<b>'+ this.i18n.lastModified +'</b>',
				'<span>{dateString}</span></div>',
				'</tpl>',
				'</div>'
				);
			this.detailsTemplate.compile();

			this.storePages = new Ext.data.JsonStore({
				url: BlueSpice.buildRemoteString('InsertFile', 'getPages'),
				root: 'items',
				fields: ['name', 'label']
			});
			this.storePages.load();
			
			this.storeLicenses = new Ext.data.JsonStore({
				url: BlueSpice.buildRemoteString('InsertFile', 'getLicenses'),
				root: 'items',
				fields: ['text', 'value', 'indent']
			});
			this.storeLicenses.load();

			this.store = new Ext.data.JsonStore({
				url: BsCore.buildRemoteString('InsertFile', 'getFiles'),
				root: 'items',
				totalProperty: 'totalCount',
				remoteSort: true,
				fields: [
					'name',
					'url',
					{
						name:'size',
						type: 'float'
					}, {
						name:'lastmod',
						type:'date',
						dateFormat:'timestamp'
					}, {
						name: 'width',
						type: 'int'
					}, {
						name: 'height',
						type: 'int'
					}
				],
				listeners: {
					'load': {
						fn:function(store, records, options){
							if(this.uploadedFile) {
								if(this.uploadedPage > 0) {
									pageData = this.pbar.getPageData();
									if(this.uploadedPage != pageData.activePage) {
										this.pbar.changePage(this.uploadedPage);
									}
									this.uploadedPage = 0;
									return;
								}
								idx = store.find('name', this.uploadedFile);
								this.view.select(idx);
								Ext.fly(this.view.getNode(idx)).scrollIntoView(this.view.container.dom);
								this.uploadedFile = false;
							}
							else {
								this.view.select(0);
							}

							this.hideDataViewLoadMask();
						},
						scope:this
					},
					'beforeload': {
						fn: function(store, options) {
							Ext.apply(this.storeParams, options.params);
							options.params = this.storeParams;

							if( !this.dataViewLoadMask ) return;
							if (!this.dataViewLoadMaskTask) {
								this.dataViewLoadMaskTask = new Ext.util.DelayedTask(
									function() {this.dataViewLoadMask.show();},
									this
								);
							}
							this.dataViewLoadMaskTask.delay( 150 );
						},
						scope: this
					},
					'exception' : {
						fn: function ( misc ) {
							this.hideDataViewLoadMask();
						},
						scope: this
					}
				}
			});

			this.view = new Ext.DataView({
				tpl: this.thumbTemplate,
				region: 'center',
				singleSelect: true,
				overClass:'x-view-over',
				itemSelector: 'div.thumb-wrap',
				emptyText : '<div style="padding:10px;">'+this.i18n.noMatch+'</div>',
				store: this.store,
				listeners: {
					'selectionchange': {
						fn:this.showDetails,
						scope:this,
						buffer:100
					},
					'dblclick'       : {
						fn:this.insertFile,
						scope:this
					},
					'loadexception'  : {
						fn:this.onLoadException,
						scope:this
					},
					'beforeselect'   : {
						fn:function(view){
							return view.store.getRange().length > 0;
						}
					},
					'render': {
						fn: function( dataview ) {
							if( this.dataViewLoadMask != false ) return; //This is to make loadMask removable by hook
							this.dataViewLoadMask = new Ext.LoadMask( 
								dataview.ownerCt.ownerCt.getEl()
							);
						},
						scope: this
					}
				},
				prepareData: this.formatData.createDelegate(this)
			});

			this.detailPanel = new Ext.Panel({
				id: 'img-detail-panel',
				region: 'center',
				height: 250,
				autoScroll: true
			});

			this.tabPanel = new Ext.TabPanel({
				region: 'south',
				height: 355,
				xtype: 'tabpanel',
				activeTab: 0,
				items: [{
					title: this.i18n.tabTitle1,
					xtype: 'form',
					id: 'tabSettings',
					padding: 10,
					tbar: [
					this.i18n.labelDimensions,
					{
						xtype: 'textfield',
						id: 'img_width',
						width: 40,
						listeners: {
							'change': {
								fn: function(field, newValue, oldValue) {
									this.data.width = newValue;
									if(Ext.getCmp('btnRatio').pressed) {
										Ext.getCmp('img_height').setValue(this.processRatio(newValue, 0));
										this.data.height = this.processRatio(newValue, 0);
									}
								},
								scope: this
							}
						}
					}, {
						text: '&nbsp;x&nbsp;',
						enableToggle: true,
						pressed: true,
						id: 'btnRatio',
						tooltip: this.i18n.tipKeepRatio
					}, {
						xtype: 'textfield',
						id: 'img_height',
						width: 40,
						listeners: {
							'change': {
								fn: function(field, newValue, oldValue) {
									this.data.height = newValue;
									if(Ext.getCmp('btnRatio').pressed) {
										Ext.getCmp('img_width').setValue(this.processRatio(0, newValue));
										this.data.width = this.processRatio(0, newValue);
									}
								},
								scope: this
							}
						}
					},
					'px', '-', this.i18n.labelAlt,
					{
						xtype: 'textfield',
						id: 'img_alt',
						listeners: {
							'change': {
								fn: function(field, newValue, oldValue) {
									this.data.alt = newValue;
								},
								scope: this
							}
						}
					}],
					items: [{
						xtype: 'fieldset',
						title: this.i18n.labelAlign,
						autoHeight: true,
						style: 'text-align: left; display: inline;',
						hideLabels: true,
						layoutConfig: {
							labelSeparator: ''
						},
						width: 212,
						items: [{
							xtype: 'radiogroup',
							id: 'img_style',
							columns: 1,
							items: [
							{
								boxLabel: this.i18n.alignNone,
								id: 'img-align-none',
								name: 'img-align',
								inputValue: 'none'
							},

							{
								boxLabel: this.i18n.alignLeft,
								id: 'img-align-left',
								name: 'img-align',
								inputValue: 'left'
							},

							{
								boxLabel: this.i18n.alignCenter,
								id: 'img-align-center',
								name: 'img-align',
								inputValue: 'center'
							},

							{
								boxLabel: this.i18n.alignRight,
								id: 'img-align-right',
								name: 'img-align',
								inputValue: 'right'
							}],
							listeners: {
								'change': {
									fn: function(group, elm) {
										this.data.style = elm.getRawValue();
									},
									scope: this
								}
							}
						}]
					}, {
						xtype: 'fieldset',
						title: this.i18n.labelType,
						autoHeight: true,
						// TODO MRG (27.09.10 13:30): wird denn hidetypeselektor nicht mehr berücksichtigt? Das wäre doof...
						style: 'text-align: left; margin-left: 10px; display:inline;',//+(hwInsertImageHideTypeSelector?'display:none;':'display:inline;'),
						hideLabels: true,
						layoutConfig: {
							labelSeparator: ''
						},
						width: 212,
						items: [{
							xtype: 'radiogroup',
							id: 'img_type',
							columns: 1,
							items: [
							{
								boxLabel: this.i18n.typeNone,
								id: 'img-type-none',
								name: 'img-type',
								inputValue: 'none'
							},

							{
								boxLabel: this.i18n.typeThumb,
								id: 'img-type-thumb',
								name: 'img-type',
								inputValue: 'thumb'
							},

							{
								boxLabel: this.i18n.typeFrame,
								id: 'img-type-frame',
								name: 'img-type',
								inputValue: 'frame'
							},

							{
								boxLabel: this.i18n.typeBorder,
								id: 'img-type-border',
								name: 'img-type',
								inputValue: 'border'
							}],
							listeners: {
								'change': {
									fn: function(group, elm) {
										this.data.type = elm.getRawValue();
									},
									scope: this
								}
							}
						}]
					}, {
						xtype: 'fieldset',
						title: this.i18n.labelLink,
						autoHeight: true,
						style: 'text-align: left; display:block;',
						hideLabels: true,
						layoutConfig: {
							labelSeparator: ''
						},
						items: [{
							xtype: 'combo',
							enableKeyEvents: true,
							store: this.storePages,
							displayField:'name',
							typeAhead: true,
							mode: 'local',
							triggerAction: 'all',
							emptyText:this.i18n.select_a_link,
							lastQuery: '',
							id: 'img_link',
							fieldLabel: 'Link',
							width: 414,
							listeners: {
								'change': {
									fn: function(field, newValue, oldValue) {
										this.data.link = newValue;
									},
									scope: this
								},
								'keyup': {
									fn: function(field, event) {
										this.data.link = field.getValue();
									},
									scope: this
								}
							}
						}]
					}]
				}, {
					title: this.i18n.tabTitle2,
					xtype: 'form',
					id: 'uploadForm',
					disabled: !bsInsertFileEnableUploads,
					padding: 10,
					fileUpload: true,
					labelWidth: 125,
					items: [{
						xtype: 'fileuploadfield',
						buttonText: this.i18n.uploadButtonText,
						id: 'file',
						name: 'file',
						width: 307,
						emptyText: ((this.storeParams.type == 'image') ? this.i18n.uploadImageEmptyText : this.i18n.uploadFileEmptyText),
						fieldLabel: ((this.storeParams.type == 'image') ? this.i18n.uploadImageFieldLabel : this.i18n.uploadFileFieldLabel),
						listeners: {
							'fileselected': {
								fn: function(field, value) {
									// TODO MRG (27.09.10 13:31): wozu wird dieses replace ausgeführt?
									value = value.replace(/^.*?([^\\\/:]*?\.[a-z0-9]+)$/img, "$1");
									value = value.replace(/\s/g, "_");
									//document.getElementById('filename').value = value;
									if( BsFileManager.checkFileSize( 'file' ) == false ) {
										Ext.MessageBox.alert( this.i18n.warning, mw.message( 'largefileserver' ).escaped(), function(){return;} );
									}
									Ext.getCmp('filename').setValue(value);
									Ext.getCmp('filename').fireEvent('change', Ext.getCmp('filename'), value);
									//Ext.getCmp('wpDestFile').setValue(value.replace(/^.*?([^\\\/:]*?\.[a-z0-9]+)$/img, "test"));
									//Ext.getCmp('wpDestFile').setValue(value.replace(/\s/g, "_"));
								},
								scope: this
							}
						}
					}, {
						xtype: 'textfield',
						id: 'filename',
						name: 'filename',
						width: 307,
						fieldLabel: this.i18n.uploadDestFileLabel,
						listeners: {
							'change': {
								fn: function(field, value) {
									var url = wgScriptPath + '/index.php?action=ajax&rs=SpecialUpload::ajaxGetExistsWarning';
									Ext.Ajax.request({
										url: url + '&rsargs[]=' + value,
										success: function(response, options) {
											if(!(response.responseText.trim() == ''
												|| response.responseText == '&#160;'
												|| response.responseText == '&nbsp;')) {
												Ext.Msg.minWidth = 250;
												Ext.Msg.alert('Status', response.responseText);
											}
										}
									});
								},
								scope:this
							}
						}
					}, {
						xtype: 'textarea',
						id: 'text',
						name: 'text',
						width: 307,
						value: BsFileManager.getFileDescription(),
						fieldLabel: this.i18n.uploadDescFileLabel
					}, {
						xtype: 'combo',
						autoSelect: true,
						forceSelection: true,
						id: 'wpLicense',
						typeAhead: true,
						triggerAction: 'all',
						lazyRender: true,
						mode: 'local',
						store: this.storeLicenses,
						valueField: 'value',
						displayField: 'text',
						tpl: new Ext.XTemplate(
							'<tpl for=".">',
							'<tpl if="this.hasValue(value) == false">',
							'<div class="x-combo-list-item no-value">{text}</div>',
							'</tpl>',
							'<tpl if="this.hasValue(value)">',
							'<div class="x-combo-list-item indent-{indent}">{text}</div>',
							'</tpl>',
							'</tpl>', {
								compiled: true,
								disableFormats: true,
								// member functions:
								hasValue: function(value) {
									return value != '';
								}
							}
							),
						width: 307,
						fieldLabel: this.i18n.license
					}, {
						xtype: 'checkbox',
						id: 'watch_page',
						name: 'watch',
						checked: false,
						value: 'true',
						boxLabel: this.i18n.uploadWatchThisLabel
					}, {
						xtype: 'checkbox',
						id: 'ignorewarnings',
						name: 'ignorewarnings',
						checked: false,
						value: 'true',
						boxLabel: this.i18n.uploadIgnoreWarningsLabel
					}], 
					buttons: [{
						text: this.i18n.uploadSubmitValue,
						handler: function() {
							if(!bsInsertFileEnableUploads) {
								Ext.Msg.alert(this.i18n.error, this.i18n.uploadsDisabled);
								return;
							}
							if(!this.checkFileExtension()) {
								return;
							}
							var license = Ext.getCmp('wpLicense').getValue();
							Ext.getCmp('text').setValue(Ext.getCmp('text').getValue() + license)
							BsUploader.doFormUpload(Ext.getCmp('uploadForm').getForm(), this.doAfterUpload);
						},
						scope: this
					}]
				}]
			});

			this.pbar = new Ext.PagingToolbar({
				id: 'insertfile-pagingtoolbar',
				pageSize: 15,
				store: this.store,
				displayInfo: true,
				displayMsg: this.i18n.pagingToolbarPosition,
				emptyMsg: this.i18n.noTopicsMessage
			});

			this.win = new Ext.Window({
				id: 'file-chooser-dlg',
				title: ((this.storeParams.type == 'image') ? this.i18n.titleImage : this.i18n.titleFile),
				layout: 'border',
				width: 810,
				height: 670,
				modal: true,
				closeAction: 'hide',
				constrain: true,
				items: [{
					id: 'file-chooser-view',
					region: 'center',
					tbar: [
					this.i18n.labelFilter,
					{
						xtype: 'textfield',
						id: 'filter',
						width: 100,
						listeners: {
							'render': {
								fn:function(){
									Ext.getCmp('filter').getEl().on('keyup', function(){
										this.filter();
									}, this, {
										buffer:500
									});
								},
								scope:this
							}
						}
					},
					' ',
					'-',
					this.i18n.labelSort,
					{
						id: 'sortSelect',
						xtype: 'combo',
						typeAhead: true,
						triggerAction: 'all',
						width: 104,
						editable: false,
						mode: 'local',
						displayField: 'desc',
						valueField: 'name',
						lazyInit: false,
						value: 'name',
						store: new Ext.data.SimpleStore({
							fields: ['name', 'desc'],
							data : [
								['name', this.i18n.fileName],
								['lastmod', this.i18n.lastModified],
								['size', this.i18n.fileSize]
							]
						}),
						listeners: {
							'select': {
								fn:this.sortImages,
								scope:this
							}
						}
					}],
					autoScroll: true,
					items: [{
						tbar: this.pbar,
						border: false,
						items: this.view
					}]
				}, {
					region: 'east',
					width: 460,
					border: false,
					items: [
					this.detailPanel,
					this.tabPanel
					]
				}],
				buttons: [{
					id: 'ok-btn-insertfile',
					text: this.i18n.labelOk,
					handler: function() {
						if(Ext.getCmp('file').getValue()) {
							Ext.Msg.show({
								title:this.i18n.warning,
								msg: this.i18n.warningUpload,
								buttons: {
									yes: this.i18n.labelClose,
									no: this.i18n.labelUpload,
									cancel: this.i18n.labelCancel
								},
								fn: function(res) {
									if(res == 'cancel') {
										BsFileManager.win.show();
									} else if (res == 'no') {
										if(!bsInsertFileEnableUploads) {
											Ext.Msg.alert(this.i18n.error, this.i18n.uploadsDisabled);
											return;
										}
										if(!this.checkFileExtension()) {
											return;
										}
										var license = Ext.getCmp('wpLicense').getValue();
										Ext.getCmp('text').setValue(Ext.getCmp('text').getValue() + license);
										BsUploader.doFormUpload(Ext.getCmp('uploadForm').getForm(), this.doAfterUploadAndClose);
									} else {
										this.insertFile();
									}
								},
								scope: this,
								icon: Ext.MessageBox.QUESTION
							});
						} else {
							this.insertFile();
						}
					},
					scope: this
				},{
					text: this.i18n.labelCancel,
					handler: function(){
						this.cancel = true;
						this.win.hide();
					},
					scope: this
				}],
				keys: {
					key: 27, // Esc key
					handler: function(){
						this.cancel = true;
						this.win.hide();
					},
					scope: this
				},
				listeners: {
					'beforehide': {
						fn: function(win) {
							/*if(!this.cancel) {
								if(Ext.getCmp('file').getValue()) {
									Ext.Msg.show({
										title:this.i18n.warning,
										msg: this.i18n.warningUpload,
										buttons: {
											yes: this.i18n.labelClose,
											no: this.i18n.labelUpload,
											cancel: this.i18n.labelCancel
										},
										fn: function(res) {
											if(res == 'cancel') {
												BsFileManager.win.show();
											} else if (res == 'no') {
												if(!bsInsertFileEnableUploads) {
													Ext.Msg.alert(this.i18n.error, this.i18n.uploadsDisabled);
													return;
												}
												if(!this.checkFileExtension()) {
													return;
												}
												var license = Ext.getCmp('wpLicense').getValue();
												Ext.getCmp('text').setValue(Ext.getCmp('text').getValue() + license);
												BsUploader.doFormUpload(Ext.getCmp('uploadForm').getForm(), this.doAfterUploadAndClose);
											}
										},
										scope: this,
										icon: Ext.MessageBox.QUESTION
									});
								}
							}*/
						},
						scope: this
					}
				}
			});
			$(document).trigger( 'BSInsertFileAfterInitWindow', [ this ] );
		}
		BsCore.saveScrollPosition();
		BsCore.saveSelection();
		if(this.reload) {
			this.store.load({
				params: this.storeParams
			});
			this.reload = false;
		}
		this.initOptions();
		this.win.show();
		this.reset();
	},
	
	doAfterUploadAndClose: function(response) {
		if(response.result == 'Success') {
			Ext.get('file').dom.value = '';
			Ext.get('filename').dom.value = '';
			BsFileManager.insertFile(response.filename);
			Ext.Msg.alert('Status', BsFileManager.i18n.uploadComplete);
		}
	},

	doAfterUpload: function(response) {
		if(response.result != 'Success') return;

		Ext.get('file').dom.value = '';
		Ext.get('filename').dom.value = '';
		Ext.Msg.alert('Status', BsFileManager.i18n.uploadComplete);
		/*BsFileManager.store.load({
			params: BsFileManager.storeParams
		});*/
		Ext.Ajax.request({
			url: BsCore.buildRemoteString(
				'InsertFile', 
				'getUploadedFilePage',
				{
					type: BsFileManager.storeParams.type,
					sort: BsFileManager.storeParams.sort,
					pagesize: BsFileManager.storeParams.limit
				}
			),
			success: function(response, opts) {
				var obj = Ext.decode(response.responseText);
				BsFileManager.uploadedFile = obj.file;
				BsFileManager.pbar.changePage(obj.page);
			},
			failure: function(response, opts) {
				mw.log( response.responseText );
			}
		});
	},

	checkFileExtension: function() {
		return true;
		uploadFile = Ext.getCmp('file').getValue();
		destFile   = Ext.getCmp('filename').getValue();
		regexp = /\.(\w+)$/im;
		ufmatch = regexp.exec(uploadFile);
		dfmatch = regexp.exec(destFile);
		if(this.storeParams.type == 'image') {
			extmsg = '<br />' + this.i18n.allowedFiletypesAre + '<br />' + this.imageExtensions;
		}
		else {
			extmsg = '<br />' + this.i18n.allowedFiletypesAre + '<br />' + this.fileExtensions;
		}
		if(ufmatch == null) {
			Ext.Msg.show({
				title: this.i18n.error,
				msg: this.i18n.errorNoFileExtensionOnUpload + extmsg,
				icon: Ext.MessageBox.ERROR,
				buttons: Ext.Msg.OK
			});
			return false;
		}
		if(dfmatch == null) {
			Ext.Msg.show({
				title: this.i18n.error,
				msg: this.i18n.errorNoFileExtensionOnDestination + extmsg,
				icon: Ext.MessageBox.ERROR,
				buttons: Ext.Msg.OK
			});
			return false;
		}
		ufExtension = ufmatch[1];
		dfExtension = dfmatch[1];
		if(this.storeParams.type == 'image' && bsImageExtensions.indexOf(ufExtension.toLowerCase()) == -1) {
			Ext.Msg.show({
				title: this.i18n.error,
				msg: this.i18n.errorWrongImageTypeOnUpload + extmsg,
				icon: Ext.MessageBox.ERROR,
				buttons: Ext.Msg.OK
			});
			return false;
		}
		if(this.storeParams.type == 'file' && bsImageExtensions.indexOf(ufExtension.toLowerCase()) != -1) {
			Ext.Msg.show({
				title: this.i18n.error,
				msg: this.i18n.errorWrongFileTypeOnUpload + extmsg,
				icon: Ext.MessageBox.ERROR,
				buttons: Ext.Msg.OK
			});
			return false;
		}
		if(this.storeParams.type == 'image' && bsImageExtensions.indexOf(dfExtension.toLowerCase()) == -1) {
			Ext.Msg.show({
				title: this.i18n.error,
				msg: this.i18n.errorWrongImageTypeOnDestination + extmsg,
				icon: Ext.MessageBox.ERROR,
				buttons: Ext.Msg.OK
			});
			return false;
		}
		if(this.storeParams.type == 'file' && bsImageExtensions.indexOf(dfExtension.toLowerCase()) != -1) {
			Ext.Msg.show({
				title: this.i18n.error,
				msg: this.i18n.errorWrongFileTypeOnDestination + extmsg,
				icon: Ext.MessageBox.ERROR,
				buttons: Ext.Msg.OK
			});
			return false;
		}
		return true;
	},
	getFileDescription: function() {
		if( mw.message('fileupload-description').plain() != '<fileupload-description>') {
			return mw.message('fileupload-description').plain();
		} else {
			return '';
		}
	},
	initOptions : function() {
		if(!this.fileExtensions) {
			this.fileExtensions = bsFileExtensions;
			this.imageExtensions = bsImageExtensions;
			for(i=0; i<this.imageExtensions.length; i++) {
				this.fileExtensions.remove(this.imageExtensions[i]);
			}
			this.fileExtensions = this.fileExtensions.join(', ');
			this.imageExtensions = this.imageExtensions.join(', ');
		}

		this.win.setTitle(((this.storeParams.type == 'image') ? this.i18n.titleImage : this.i18n.titleFile));
		if(this.storeParams.type == 'image') {
			Ext.getCmp('tabSettings').enable();
			this.tabPanel.setActiveTab(0);
		}
		else {
			Ext.getCmp('tabSettings').disable();
			this.tabPanel.setActiveTab(1);
		}

		if(this.data.href) {
			image = this.data.href.replace(/.*[\\\/]/g, "");
			Ext.Ajax.request({
				url: BsCore.buildRemoteString('InsertFile', 'getFilePage', {
					filename: image,
					type: this.storeParams.type,
					pagesize: this.storeParams.limit
				}),
				success: function(response, opts) {
					var obj = Ext.decode(response.responseText);
					BsFileManager.uploadedFile = obj.file;
					BsFileManager.pbar.changePage(obj.page);
				},
				failure: function(response, opts) {
					mw.log( response.responseText );
				}
			});
		}
		Ext.getCmp('img_width').setValue(this.data.width);
		Ext.getCmp('img_height').setValue(this.data.height);
		Ext.getCmp('img_alt').setValue(this.data.alt);
		Ext.getCmp('img_link').setValue(this.data.link);
		Ext.getCmp('img_style').setValue('img-align-'+this.data.style, true);
		Ext.getCmp('img_type').setValue('img-type-'+this.data.type, true);
	},

	processRatio: function(w, h) {
		var data = 0;
		var selNode = this.view.getSelectedNodes();
		if(selNode && selNode.length > 0){
			selNode = selNode[0];
			data = this.lookup[selNode.id];
		}
		if((w == 0 && h == 0) || data == 0) {
			return 0;
		}
		var orgW = data.width;
		var orgH = data.height;

		if(w == 0) {
			return Math.round(orgW / (orgH / h));
		}
		else {
			return Math.round(orgH / (orgW / w));
		}
	},

	showDetails : function(){
		var selNode = this.view.getSelectedNodes();
		var detailEl = Ext.getCmp('img-detail-panel').body;
		if(selNode && selNode.length > 0){
			selNode = selNode[0];
			Ext.getCmp('ok-btn-insertfile').enable();
			var data = this.lookup[selNode.id];
			detailEl.hide();
			this.detailsTemplate.overwrite(detailEl, data);
			if(this.data.width) {
				Ext.getCmp('img_width').setValue(this.data.width);
			}
			else {
				Ext.getCmp('img_width').setValue(data.width);
			}
			if(this.data.height) {
				Ext.getCmp('img_height').setValue(this.data.height);
			}
			else {
				Ext.getCmp('img_height').setValue(data.height);
			}
			detailEl.slideIn('l', {
				stopFx:true,
				duration:.2
			});
		}
	},

	filter : function(){
		var filter = Ext.getCmp('filter');
		this.storeParams.firstchars = filter.getValue();
		this.storeParams.sort = Ext.getCmp('sortSelect').getValue();
		this.storeParams.start = 0;
		this.view.store.load({
			params:this.storeParams
		});
		this.view.select(0);
	},

	sortImages : function(){
		var filter = Ext.getCmp('filter');
		this.storeParams.firstchars = filter.getValue();
		this.storeParams.sort = Ext.getCmp('sortSelect').getValue();
		this.view.store.load({
			params:this.storeParams
		});
		this.view.select(0);
	},

	reset : function(){
		if(this.win.rendered){
			Ext.getCmp('filter').reset();
			this.view.getEl().dom.scrollTop = 0;
		}
		this.storeParams.firstchar = '';
		this.storeParams.sort = '';
		this.view.store.clearFilter();
		this.view.select(0);
	},

	insertFile : function(name){
		if(typeof(name) == 'undefined' || typeof(name) == 'object') {
			name = false;
		}
		var selNode = this.view.getSelectedNodes()[0];
		var callback = this.callback;
		var lookup = this.lookup;
		if(!this.win.hidden) {
			this.win.hide(this.animateTarget);
		}
		if(selNode || name != false){
			var data;
			if(name != false) {
				data = {
					name: name
				};
				this.data.width = 500;
			} else {
				data = lookup[selNode.id];
			}
			if(this.storeParams.type == 'image') {
				text = data.name;
				if(this.data.width && this.data.width != 0) {
					size = this.data.width;
					if (this.data.height && this.data.height != 0)
					{
						size = size + 'x' + this.data.height;
					}
					text = text + "|" + size + 'px';
				}
				if(this.data.style && this.data.style != 'none') {
					text = text + "|" + this.data.style;
				}
				if(this.data.type && this.data.type != 'none') {
					text = text + "|" + this.data.type;
				}
				if(this.data.alt && this.data.alt != '') {
					text = text + "|alt=" + this.data.alt;
				}
				if(this.data.link && this.data.link != '') {
					text = text + "|link=" + this.data.link;
				}
				//text = jQuery.trim(text);
				//console.log('[['+this.i18n.imageTag+':'+text+']]');

				// TODO MRG (27.09.10 13:35): kann sein, dass das zukünftig bsTinyMCEMode heisst
				if ((typeof(VisualEditorMode)=="undefined") || !VisualEditorMode )
				{
					BsCore.restoreSelection('[['+bsInsertFileImageTag+':'+text+']]');
					BsCore.restoreScrollPosition();
				}
				else
				{
					tinyMCE.activeEditor.selection.moveToBookmark(this.data.selection);
					tinyMCE.activeEditor.dom.setOuterHTML(this.data.image, "");
					tinyMCE.activeEditor.getBody().innerHTML
					tinyMCE.execCommand('mceInsertRawHTML', true, '[['+bsInsertFileImageTag+':' + text + ']]');
					tinyMCE.activeEditor.selection.moveToBookmark(this.data.selection);
					tinyMCE.activeEditor.selection.collapse(true);
				}
			}
			else {
				text = data.name;
				if ((typeof(VisualEditorMode)=="undefined") || !VisualEditorMode )
				{
					BsCore.restoreSelection('[['+bsInsertFileFileTag+':'+text+']]');
					BsCore.restoreScrollPosition();
				}
				else
				{
					tinyMCE.activeEditor.selection.moveToBookmark(this.data.selection);
					tinyMCE.activeEditor.dom.setOuterHTML(this.data.link, "");
					tinyMCE.execCommand('mceInsertRawHTML', true, '[['+bsInsertFileFileTag+':'+text+']]');
					tinyMCE.activeEditor.selection.moveToBookmark(this.data.selection);
					tinyMCE.activeEditor.selection.collapse(true);
				}
			}
		}
	},
	getFileUrl: function(name) {
		Ext.Ajax.request({
			url: BsCore.buildRemoteString('InsertFile', 'getFileRealLink', {
				filename: name
			}),
			success: function(response, opts) {
				var obj = Ext.decode(response.responseText);
				// TODO MRG (27.09.10 13:36): was, wenns kein tinyMCE gibt? das muss abgeprüft werden.
				content = tinyMCE.activeEditor.getBody().innerHTML;
				tinyMCE.activeEditor.getBody().innerHTML = content.split('_BN_REPLACE'+obj.file).join(obj.url);
			},
			failure: function(response, opts) {
			// TODO MRG (27.09.10 13:36): s.o. z.686
			//console.log('server-side failure with status code ' + response.status); 		//TODO RBV (24.09.2010 09:37): ist console immer verfügbar
			}
		});
	},
	onLoadException : function(v,o){
		this.view.getEl().update('<div style="padding:10px;">'+i18n.insertfile.errorLoading+'</div>');
	},
	
	hideDataViewLoadMask: function() {
		if( this.dataViewLoadMask ) {
			if (this.dataViewLoadMaskTask) {
				this.dataViewLoadMaskTask.cancel();
			}
			this.dataViewLoadMask.hide();
		}
	}
}