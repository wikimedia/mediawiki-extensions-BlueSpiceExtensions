Ext.define( 'BS.InsertFile.UploadPanel', {
	extend: 'Ext.form.Panel',
	require:[
		'BS.form.action.MediaWikiApiCall'
	],
	fieldDefaults: {
		anchor: '100%',
		labelWidth: 70,
		labelAlign: 'right',
		msgTarget: 'under'
	},
	fileUpload: true,
	layout: {
		type: 'vbox',
		align: 'stretch'  // Child items are stretched to full width
	},
	bodyPadding: 5,

	//Custom settings
	allowedFileExtensions: mw.config.get( 'wgFileExtensions' ),

	initComponent: function() {

		//HINT: https://www.mediawiki.org/wiki/API:Upload#Uploading
		this.fuFile = Ext.create('Ext.form.field.File', {
			fieldLabel: mw.message('bs-insertfile-uploadfilefieldlabel').plain(),
			buttonText: mw.message('bs-insertfile-uploadbuttontext').plain(),
			id: this.getId()+'-file',
			name: 'file',
			emptyText: mw.message('bs-insertfile-uploadfileemptytext').plain(),
			validator: this.validateFile,
			validateOnChange: true
		});
		this.fuFile.on( 'change', this.fuFileChange, this );

		this.tfFileName = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-insertfile-uploaddestfilelabel').plain(),
			id: this.getId()+'-filename',
			name: 'filename'
		});
		this.tfFileName.on( 'change', this.tfFileNameChange, this );

		this.taDescription = Ext.create('Ext.form.field.TextArea', {
			fieldLabel: mw.message('bs-insertfile-uploaddescfilelabel').plain(),
			id: this.getId()+'-text',
			value: '',
			name: 'text'
		});

		this.storeLicenses = Ext.create( 'Ext.data.Store', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl('InsertFileAJAXBackend::getLicenses'),
				reader: {
					type: 'json',
					root: 'items',
					idProperty: 'value'
				}
			},
			extraParams: {
				type: this.storeFileType
			},
			remoteSort: true,
			fields: ['text', 'value', 'indent']
		});

		this.cbLicences = Ext.create('Ext.form.ComboBox',{
			fieldLabel: mw.message('bs-insertfile-license').plain(),
			//autoSelect: true,
			//forceSelection: true,
			//typeAhead: true,
			//triggerAction: 'all',
			//lazyRender: true,
			mode: 'local',
			store: this.storeLicenses,
			valueField: 'value',
			displayField: 'text',
			tpl: new Ext.XTemplate(
				'<ul class="x-list-plain">',
				  '<tpl for=".">',
				    '<tpl if="this.hasValue(value) == false">',
				      '<li role="option" class="x-boundlist-item no-value">{text}</li>',
				    '</tpl>',
				    '<tpl if="this.hasValue(value)">',
				      '<li role="option" class="x-boundlist-item indent-{indent}">{text}</li>',
				    '</tpl>',
				  '</tpl>',
				'</ul>',
				{
					compiled: true,
					disableFormats: true,
					// member functions:
					hasValue: function(value) {
						return value !== '';
					}
				}
			)
		});

		this.cbxWatch = Ext.create('Ext.form.field.Checkbox', {
			boxLabel: mw.message('bs-insertfile-uploadwatchthislabel').plain(),
			id: this.getId()+'watch_page',
			name: 'watch'
		});

		this.cbxWarnings = Ext.create('Ext.form.field.Checkbox', {
			boxLabel: mw.message('bs-insertfile-uploadignorewarningslabel').plain(),
			id: this.getId()+'ignorewarnings',
			name: 'ignorewarnings'
		});

		this.bsCategories = Ext.create( 'BS.form.CategoryBoxSelect', {
			id: this.getId()+'categories',
			name: 'categories',
			fieldLabel: mw.message('bs-insertfile-categories').plain()
		});

		this.fsDetails = Ext.create( 'Ext.form.FieldSet', {
			title: 'Details',
			collapsed: true,
			collapsible: true,
			anchor: '98%',
			defaults: {
				anchor: '100%',
				labelWidth: 90,
				labelAlign: 'right'
			}
		});

		this.panelItems = [
			this.tfFileName,
			this.fuFile,
			this.fsDetails
		];
		var detailsItems = [
			this.bsCategories,
			this.taDescription,
			this.cbLicences,
			this.cbxWarnings,
			this.cbxWatch
		];

		$(document).trigger( 'BSUploadPanelInitComponent', [ this, this.panelItems, detailsItems ] );

		this.fsDetails.add( detailsItems );
		this.items = this.panelItems;

		this.addEvents( 'uploadcomplete' );

		this.callParent(arguments);
	},

	fuFileChange:  function(field, value, eOpts) {
		//Remove path info
		value = value.replace(/^.*?([^\\\/:]*?\.[a-z0-9]+)$/img, "$1");
		value = value.replace(/\s/g, "_");
		if( mw.config.get('bsIsWindows') ) {
			value = value.replace(/[^\u0000-\u007F]/gmi, ''); //Replace Non-ASCII
		}

		this.tfFileName.setValue(value);
		this.tfFileName.fireEvent('change', this.tfFileName, value);
	},

	tfFileNameChange: function(field, value) {
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl( 'InsertFileAJAXBackend::getExistsWarning', [ value ] ),
			success: function(response, options) {
				if(!(response.responseText.trim() === ''
					|| response.responseText === '&#160;'
					|| response.responseText === '&nbsp;')) {

					bs.util.alert(
						this.getId()+'-existswarning',
						{
							titleMsg: 'bs-extjs-title-warning',
							text: response.responseText
						},
						{
							ok: function() {
								//User is noticed. Now let's set the
								//ignore warnings flag automatically
								this.cbxWarnings.setValue(true);
							},
							scope: this
						}
					);
				}
			},
			scope: this
		});
	},

	checkFileSize: function( ExtCmpId ) {
		//No FileAPI? No love.
		if(typeof window.FileReader === 'undefined') return true;

		var allowedSize = mw.config.get('bsMaxUploadSize');
		if( allowedSize === null ) return true;

		var filesize = this.fuFile.fileInputEl.dom.files[0].size;
		if( filesize > allowedSize.php || filesize > allowedSize.mediawiki) {
			return false;
		}
		return true;
	},

	uploadFile: function( sessionKeyForReupload ) {
		var desc = this.taDescription.getValue();
		var licence = this.cbLicences.getValue();
		if( licence ) {
			desc += licence + "\n";
		}

		var categories = this.bsCategories.getValue();
		var formattedNamespaces = mw.config.get('wgFormattedNamespaces');
		for( var i = 0; i < categories.length; i++ ) {
			var categoryLink = new bs.wikiText.Link({
				title: $.ucFirst( categories[i] ),
				nsText: formattedNamespaces[bs.ns.NS_CATEGORY],
				link: false //TDOD: fix this in "bs.wikiText.Link"
			});
			desc += "\n" + categoryLink.toString();
		}
		this.taDescription.setValue( desc );

		this.cbLicences.disable(); //To prevent the form from submitting a generated name

		var params = {
			action: 'upload',
			token: mw.user.tokens.get('editToken'),
			//IE9 has an issue with this API call returnug a application/json
			//content-type. Therefore we let the server return a "text/xml"
			//content-type header
			//HINT: http://stackoverflow.com/questions/18571719/extjs-file-uploading-error-on-ie8-ie9
			format: 'xml'
		};

		if( sessionKeyForReupload ) {
			params.sessionkey = sessionKeyForReupload;
		}

		this.getForm().doAction( Ext.create('BS.form.action.MediaWikiApiCall', {
			form: this.getForm(), //Required
			url: mw.util.wikiScript('api'),
			params: params,
			success: this.onUploadSuccess,
			failure: this.onUploadFailure,
			scope: this
		}));

		//We mask only the FormPanel, because masking the whole document using
		// "waitMsg" param on MediaWikiApiCall does no automatic unmasking.
		//This is because MediaWikiApiCall overrides the onSuccess/onFailure
		//methods of action "Submit"
		this.getEl().mask(
			mw.message('bs-insertfile-upload-waitmessage').plain(),
			Ext.baseCSSPrefix + 'mask-loading'
		);
	},

	onUploadSuccess: function( response, action ) {
		this.getEl().unmask();
		this.cbLicences.enable();

		var errorTag = response.responseXML
			.documentElement.getElementsByTagName('error').item(0);

		if( errorTag !== null ) {
			bs.util.alert(
				this.getId()+'-error',
				{
					title: mw.message('bs-insertfile-error').plain(),
					text: errorTag.getAttribute('info')
				}
			);
			return;
		}

		//As we process XML instead of JSON (see reason above) we have to
		//create a suitable JS object from the XML response to be compatible
		var uploadTag = response.responseXML
			.documentElement.getElementsByTagName('upload').item(0);
		var imageinfoTag = uploadTag.getElementsByTagName('imageinfo').item(0);

		var imageinfo = {};
		if( imageinfoTag.attributes ) {
			for( var i = 0; i < imageinfoTag.attributes.length; i++ ) {
				var attribute = imageinfoTag.attributes.item(i);
				imageinfo[attribute.nodeName] = attribute.nodeValue;
			}
		}
		var upload = {
			result: uploadTag.getAttribute('result'),
			filename: uploadTag.getAttribute('filename'),
			imageinfo: imageinfo
		};

		this.fireEvent( 'uploadcomplete', this, upload );
		this.getForm().reset();
	},

	onUploadFailure: function( response, action ) {
		//This would only happen when a server error occurred but not when the
		//MediaWiki API returns an JSON encoded error
		this.getForm().reset();
		this.cbLicences.enable();
	},

	//scope: "this" == fuFile
	validateFile: function( value ) {
		if( value === "" ) return true;
		var me = this.up('form');
		me.allowedFileExtensions = mw.config.get( 'wgFileExtensions' );

		var nameParts = value.split('.');
		var fileExtension = nameParts[nameParts.length-1].toLowerCase();
		var extensionFound = false;

		$.each(me.allowedFileExtensions, function(index, value) {
			if (value.toLowerCase() === fileExtension.toLowerCase()) {
				extensionFound = true;
				return false;
			}
		});

		if(!extensionFound) {
			return mw.message('bs-insertfile-allowedfiletypesare').plain()
				+ " " + me.allowedFileExtensions.join(', ');
		}

		if( me.checkFileSize() === false ) {
			return mw.message( 'largefileserver' ).plain();
		}

		return true;
	}
});
