
Ext.define( 'BS.InsertFile.ImageDialog', {
	extend: 'BS.InsertFile.BaseDialog',
	//'requires' and 'Ext.create(...)' are more or less the same. It may be
	//more safe to use 'requires' at runtime.
	requires: [
		'Ext.form.ComboBox', 'Ext.form.field.Checkbox', 'Ext.Button',
		'Ext.form.field.Number', 'Ext.form.RadioGroup', 'Ext.form.TextField'
	],

	singleton: true,
	id: 'bs-InsertImage-dlg-window',
	title: mw.message('bs-insertfile-titleimage').plain(),

	//Custom Settings
	allowedFileExtensions: mw.config.get( 'bsImageExtensions' ),
	storeFileType: 'image',

	initComponent: function() {
		this.cbPages = Ext.create( 'BS.form.field.TitleCombo', {
			width: 350,
			margin: '0 5 0 0'
		});

		this.cbxNoLink = Ext.create( 'Ext.form.field.Checkbox', {
			boxLabel: mw.message('bs-insertfile-no-link').plain(),
			handler: this.onCbxNoLinkChange,
			scope: this
		});
		//Change event is not fired properly. Seems to be a bug in ExtJS. We
		//use 'handler' in stead.
		//this.cbxNoLink.on( 'change ', this.onCbxNoLinkChange, this );

		this.nbHeight = Ext.create( 'Ext.form.field.Number',{
			width: 70,
			minValue: 1,
			value: 1,
			margin: '0 0 0 5',
			allowDecimals: false
		});
		this.nbHeight.on('blur', this.onNbHeightChange, this);
		this.nbWidth = Ext.create( 'Ext.form.field.Number',{
			width: 70,
			minValue: 1,
			value: 1,
			margin: '0 5 0 0',
			allowDecimals: false
		});
		this.nbWidth.on('blur', this.onNbWidthChange, this);
		this.btnKeepRatio = Ext.create('Ext.Button', {
			text: '&nbsp;x&nbsp;',
			tooltip: mw.message('bs-insertfile-tipkeepratio').plain(),
			enableToggle: true,
			pressed: true,
			ui: 'default-toolbar-small',
			id: 'btnRatio'
		});

		this.rgFormat = Ext.create('Ext.form.RadioGroup', {
			fieldLabel: mw.message('bs-insertfile-labeltype').plain(),
			value: 'thumb',
			items: [{
					boxLabel: mw.message('bs-insertfile-typenone').plain(),
					id: 'img-type-none',
					name: 'img-type',
					inputValue: 'none'
				},
				{
					boxLabel: mw.message('bs-insertfile-typethumb').plain(),
					id: 'img-type-thumb',
					name: 'img-type',
					inputValue: 'thumb',
					checked: true
				},
				{
					boxLabel: mw.message('bs-insertfile-typeframe').plain(),
					id: 'img-type-frame',
					name: 'img-type',
					inputValue: 'frame'
				},
				{
					boxLabel: mw.message('bs-insertfile-typeborder').plain(),
					id: 'img-type-border',
					name: 'img-type',
					inputValue: 'border'
				}
			]
		});
		this.rgFormat.on( 'change', this.onRgFormatChange, this );

		this.cbxEnableAlign = Ext.create( 'Ext.form.field.Checkbox', {
			handler: this.onCbxEnableAlign,
			scope: this
		});
		this.cbxEnableAlign.on('render', function(c){
			Ext.QuickTips.register({
				target: c.getEl(),
				text: mw.message('bs-insertfile-enable-align').plain()
			});
		});

		this.rgAlign = Ext.create('Ext.form.RadioGroup', {
			value: 'none',
			disabled: true,
			flex: 1,
			items: [{
					boxLabel: mw.message('bs-insertfile-alignnone').plain(),
					id: 'img-align-none',
					name: 'img-align',
					inputValue: 'none',
					checked: true
				},
				{
					boxLabel: mw.message('bs-insertfile-alignleft').plain(),
					id: 'img-align-left',
					name: 'img-align',
					inputValue: 'left'
				},
				{
					boxLabel: mw.message('bs-insertfile-aligncenter').plain(),
					id: 'img-align-center',
					name: 'img-align',
					inputValue: 'center'
				},
				{
					boxLabel: mw.message('bs-insertfile-alignright').plain(),
					id: 'img-align-right',
					name: 'img-align',
					inputValue: 'right'
				}
			]
		});

		this.tfAlt = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message('bs-insertfile-labelalt').plain(),
			//todo: needs implementation, just setting an empty string
			//otherwise the edit dialog would display false
			value: ""
		});

		this.hdnUrl = Ext.create( 'Ext.form.field.Hidden' );

		this.configPanel.height = 280;
		var items = [
			this.rgFormat,
			{
				xtype: 'fieldcontainer',
				fieldLabel: mw.message('bs-insertfile-labelalign').plain(),
				layout: 'hbox',
				items: [
					this.cbxEnableAlign,
					{ xtype: 'splitter'	},
					this.rgAlign
				]
			},
			{
				xtype: 'fieldcontainer',
				fieldLabel: mw.message('bs-insertfile-labellink').plain(),
				layout: 'hbox',
				items: [
					this.cbPages,
					this.cbxNoLink
				]
			},
			{
				xtype: 'fieldcontainer',
				fieldLabel: mw.message('bs-insertfile-labeldimensions').plain(),
				layout: 'hbox',
				/*fieldDefaults: {
					margin: '0 5 5 0'
				},*/
				items: [
					this.nbWidth,
					this.btnKeepRatio,
					this.nbHeight
				]
			},
			this.tfAlt
		];

		$(document).trigger("BSInsertFileInsertImageDialogAfterInit", [items]);
		this.configPanel.items = items;

		this.callParent(arguments);
	},
	makeRgNsTextItems: function() {
		var items = this.callParent( arguments );
		items[0].boxLabel = mw.message('bs-insertfile-nstextfile-image').plain();
		return items;
	},
	//We need to set the
	onStImageGridLoad: function( store, records, successful, eOpts ) {
		//Only if we have a image selected
		if( store.filters.items.length > 0 && records.length === 1 ) {
			//And only if the images has no width/height information
			if( this.nbWidth.getValue() === null && this.nbHeight.getValue() === null ) {
				var record = records[0];
				this.isSetData = true;
				this.nbWidth.setValue(+record.get('file_width'));
				this.nbHeight.setValue(+record.get('file_height'));
				this.isSetData = false;
			}
		}
		this.callParent(arguments);
	},
	onPnlConfigExpand: function(panel, eOpts){
		this.callParent(arguments);
	},
	onNbHeightChange: function( element, event ) {
		if (this.btnKeepRatio.pressed && !this.isSetData) {
			this.nbWidth.setValue(this.processRatio(0, element.lastValue));
		}
	},
	onNbWidthChange: function( element, event ) {
		if (this.btnKeepRatio.pressed && !this.isSetData) {
			this.nbHeight.setValue(this.processRatio(element.lastValue, 0));
		}
	},
	processRatio: function(w, h) {
		var record = this.getSingleSelection();
		if ((w === 0 && h === 0) || record === null ) {
			return 0;
		}
		var orgW = record.get('file_width');
		var orgH = record.get('file_height');

		if (w === 0) {
			return Math.round(orgW / (orgH / h));
		}
		else {
			return Math.round(orgH / (orgW / w));
		}
	},
	/*
	onRender: function() {
		this.pnlConfig.setHeight( 250 );
		this.callParent(arguments);
	},
	*/

	getData: function() {
		var cfg = this.callParent(arguments);

		var noLink = this.cbxNoLink.getValue();
		var link = this.cbPages.getRawValue();
		if( noLink || link === '' ) {
			link = false;
		}

		Ext.apply(cfg, {
			//bs.wikiText.Link stuff
			caption: this.tfLinkText.getValue(),
			sizeheight: false,
			sizewidth: false,
			link: link ,
			alt: this.tfAlt.getValue(),
			thumb: false,
			border: false,
			frame: false,
			//VisualEditor stuff
			imagename: this.tfFileName.getValue(),
			noLink: noLink,
			src: Ext.htmlDecode(this.hdnUrl.getValue()) //Ext.htmlDecode(): this feels like the wrong place...
		});

		var format = this.rgFormat.getValue();
		format = format['img-type'];

		if( format === 'thumb' ) {
			cfg.thumb = true;
		}
		else if( format === 'frame' ) {
			cfg.frame = true;
		}
		else if( format === 'border' ) {
			cfg.border = true;
		}

		var alignEnabled = this.cbxEnableAlign.getValue();
		var align = this.rgAlign.getValue();
		align = align['img-align'];
		if( alignEnabled == false ) {
			align = 'no-align';
		}
		cfg.align = align;

		//Only set width and height if they are _not_ the original size!
		var record = this.getSingleSelection();
		if( record === null ){
			return cfg;
		}

		var height = this.nbHeight.getValue();
		var width = this.nbWidth.getValue();
		if( height != record.get('file_height') || width != record.get('file_width') ) {
			cfg.sizeheight = height;
			cfg.sizewidth = width;
		}

		$(document).trigger("BSInsertFileInsertImageDialogBeforeReturnGetData", [this, cfg]);
		return cfg;
	},
	setData: function( obj ) {
		this.isSetData = true;
		if( obj.imagename ) {
			var titleParts = obj.imagename.split(':');
			titleParts.shift(); //Remove namespace prefix
			obj.title = titleParts.join(':');
		}
		if( obj.caption ) {
			obj.displayText = obj.caption;
		}

		this.callParent( arguments );

		//Reset all fields to default; Maybe do this onOKClick
		this.rgFormat.reset();
		this.rgAlign.reset();
		this.nbHeight.reset();
		this.btnKeepRatio.toggle(true);
		this.nbWidth.reset();
		this.cbPages.reset();
		this.cbxNoLink.reset();
		this.tfAlt.reset();
		this.hdnUrl.reset();

		var format = 'none';
		if( obj.thumb && obj.thumb !== 'false' ) format = 'thumb';
		if( obj.frame && obj.frame !== 'false')  format = 'frame';
		if( obj.border && obj.border !== 'false' ) format = 'border';
		this.rgFormat.setValue({
			'img-type': format
		});

		if( !('align' in obj) || obj.align == 'no-align' ) {
			this.cbxEnableAlign.setValue( false );
		} else {
			this.cbxEnableAlign.setValue( true );
			this.rgAlign.setValue({
				'img-align': obj.align
			});
		}

		if( obj.sizewidth !== '' ) {
			this.nbWidth.setValue(obj.sizewidth);
		}

		if( obj.sizeheight !== '' ) {
			this.nbHeight.setValue(obj.sizeheight);
		}

		if( obj.alt !== '' ) {
			this.tfAlt.setValue( obj.alt );
		}
		else {
			this.tfAlt.setValue("");
		}

		if( obj.link !== '' && obj.link !== false && obj.link !== 'false' ) {
			this.cbPages.setValue( obj.link );
		}
		if( obj.nolink === true || obj.link === '' ){
			this.cbPages.disable( );
			this.cbxNoLink.setValue(true);
		}
		else {
			this.cbPages.enable( );
			this.cbxNoLink.setValue(false);
		}

		this.hdnUrl.setValue( obj.src );
		this.isSetData = false;
	},

	onGdImagesSelect: function( grid, record, index, eOpts ){
		this.callParent(arguments);

		this.hdnUrl.setValue( record.get('file_url') );
		//This is to avoid an overriding of the dimension that may have been
		//set by this.setData()
		if( grid.getStore().filters.items.length === 0 || grid.getStore().getCount() !== 1 ) {
			this.nbWidth.setValue( record.get('file_width') );
			this.nbHeight.setValue( record.get('file_height') );
		}
		$(document).trigger("BSInsertFileInsertImageDialogAfterImageSelect", [this, grid, record, index]);
	},

	//If we want do have a WikiImageLink that produces a unlinked image we will
	//have to supply a "link=" (empty value) parameter.
	onCbxNoLinkChange: function( sender, checked ) {
		if( checked ) {
			this.cbPages.disable();
		}
		else {
			this.cbPages.enable();
		}
	},

	onRgFormatChange: function( sender, newValue, oldValue, eOpts ) {
		if( newValue['img-type'] === 'frame' ) {
			// Hint: frame ignores size but we want to keep this information
			// See: mediawiki.org/wiki/Help:Images#Size_and_frame
			this.nbWidth.disable();
			this.btnKeepRatio.disable();
			this.nbHeight.disable();
			this.tfLinkText.enable();
		}
		else {
			this.nbWidth.enable();
			this.btnKeepRatio.enable();
			this.nbHeight.enable();
			this.tfLinkText.disable();
		}
		if( newValue['img-type'] === 'thumb' ) {
			this.tfLinkText.enable();
		}
		else {
			this.tfLinkText.disable();
		}
	},

	makeGridFilterFeatureConfig: function() {
		var filtersCfg = this.callParent( arguments );
		filtersCfg.filters[0].value = { 'sw': 'image/' }; //Set to "starts with"; value is 'image/' defined by base class
		return filtersCfg;
	},

	onCbxEnableAlign: function( sender, newValue, oldValue, eOpts ) {
		if( newValue ) {
			this.rgAlign.enable();
		} else {
			this.rgAlign.disable();
		}
	}
});