Ext.define( 'BS.Flexiskin.Menuitems.General', {
	extend: 'Ext.Panel',
	require: [ 'BS.form.action.MediaWikiApiCall' ],
	title: mw.message( 'bs-flexiskin-headergeneral' ).plain(),
	layout: 'form',
	currentData: {},
	parent: null,
	id: 'bs-flexiskin-preview-menu-general',
	initComponent: function() {
		this.tfName = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-flexiskin-labelname' ).plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'name',
			allowBlank: false
		});
		this.tfName.on( "blur", function( el ){
			this.parent.onItemStateChange();
		}, this );
		this.tfDesc = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-flexiskin-labeldesc' ).plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'desc',
			allowBlank: false
		});
		this.tfDesc.on( "blur", function() {
			this.parent.onItemStateChange();
		}, this );
		this.pfBackgroundColor = Ext.create( 'Ext.picker.Color', {
			value: '', // initial selected color
			id: 'bs-flexiskin-general-background-color',
			listeners: {
				select: function( picker, selColor ) {
					this.tfCustomBackgroundColor.setValue( selColor.replace( "#", "" ) );
					this.parent.onItemStateChange();
				},
				scope: this
			}
		});

		this.coBackgroundColorContainer = Ext.create( 'Ext.form.FieldContainer', {
			fieldLabel: mw.message( 'bs-flexiskin-labelbgcolor' ).plain(),
			labelWidth: 100,
			labelAlign: 'left',
			items: [ this.pfBackgroundColor ]
		});
		this.tfCustomBackgroundColor = Ext.create( 'Ext.form.TextField', {
			id: 'bs-flexiskin-general-custom-background-field',
			fieldLabel: mw.message( 'bs-flexiskin-labelcustombgcolor' ).plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'customBackgroundColor',
			allowBlank: true
		});
		var me = this;
		this.tfCustomBackgroundColor.on( "blur", function( el ){
			var isOk  = /(^#?[0-9A-F]{6}$)|(^#?[0-9A-F]{3}$)/i.test( el.getValue() );
			me.setColor( me.pfBackgroundColor, el.getValue() );
			if ( isOk )
				me.parent.onItemStateChange();
		});
		this.cbUseBackground = Ext.create('Ext.form.field.Checkbox', {
			fieldLabel: mw.message('bs-flexiskin-usebackground').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'useBackground',
			handler: this.onCbUseBackgroundChange,
			scope: this
		});
		this.tfUseBackground = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-flexiskin-labelcurrentbackground' ).plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'currentBackgroundImage',
			allowBlank: true,
			readOnly: true
		});
		this.ufBackgroundUpload = Ext.create( 'BS.form.UploadPanel', {
			url: mw.util.wikiScript( 'api' ),
			uploadFormName: 'background',
			uploadFieldLabel: mw.message( 'bs-flexiskin-labelbackgroundupload' ).plain(),
			uploadLabelWidth: 100,
			uploadResetButton: true
		} );
		this.ufBackgroundUpload.on( 'reset', this.btnResetClick, this );
		var rep_back_pos = Ext.create( 'Ext.data.Store', {
			fields: [ 'repeating', 'val' ],
			data: [
				{ "repeating": "no-repeat", 'val': mw.message('bs-flexiskin-no-repeat').plain() },
				{ "repeating": 'repeat-x', 'val': mw.message('bs-flexiskin-repeat-x').plain() },
				{ "repeating": 'repeat-y', 'val': mw.message('bs-flexiskin-repeat-y').plain() },
				{ "repeating": "repeat", 'val': mw.message('bs-flexiskin-repeat').plain() }
			]
		});
		this.cgRepeatBackground = Ext.create( 'Ext.form.ComboBox', {
			fieldLabel: mw.message( 'bs-flexiskin-labelrepeatbackground' ).plain(),
			mode: 'local',
			store: rep_back_pos,
			displayField: 'val',
			valueField: 'repeating',
			listeners: {
				'select': function( cb, rec ) {
					this.parent.onItemStateChange();
				},
				scope: this
			},
			scope: this
		});
		this.ufBackgroundUpload.on( 'reset', this.btnResetClick, this );
		this.ufBackgroundUpload.on( 'upload', this.btnUploadClick, this );

		this.items = [
			this.tfName,
			this.tfDesc,
			this.coBackgroundColorContainer,
			this.tfCustomBackgroundColor,
			this.cbUseBackground,
			this.tfUseBackground,
			this.ufBackgroundUpload,
			this.cgRepeatBackground
		];

		$( document ).trigger( "BSFlexiskinMenuGeneralInitComponent", [this, this.items] );

		this.callParent( arguments );
	},
	btnUploadClick: function( el, form ) {
		if ( !form.isValid() ){
			return;
		}
		var me = this;
		form.doAction( Ext.create( 'BS.form.action.MediaWikiApiCall', {
			form: form,
			params: {
				action: 'bs-flexiskin-upload',
				skinId: this.currentData.skinId,
				name: 'background',
				format: 'xml'
			},
			success: function( response, action ) {
				var result = response.responseXML.getElementsByTagName( 'result' )[0];
				if( result.hasAttribute( 'success' ) ) {
					Ext.getCmp( 'bs-extjs-uploadCombo-background-hidden-field' ).setValue(
						result.getAttribute( 'name' )
					);
					me.parent.onItemStateChange();
				}
				else {
					BS.util.alert( 'bs-flexiskin-saveskin-error', {
						text: result.getAttribute( 'message' ),
						titleMsg: 'bs-extjs-error'
					});
				}
			},
			scope: this
		}));
		this.ufBackgroundUpload.btnReset.enable();
	},
	btnResetClick: function( el ) {
		Ext.getCmp( 'bs-extjs-uploadCombo-background-hidden-field' ).setValue( "" );
		this.parent.onItemStateChange();
		this.ufBackgroundUpload.btnReset.disable();
	},
	onCbUseBackgroundChange: function( sender, checked ) {
		if( checked ) {
			this.ufBackgroundUpload.enable();
		}
		else {
			this.ufBackgroundUpload.disable();
		}
		this.parent.onItemStateChange();
	},
	getData: function() {
		var background;
		if ( this.cbUseBackground.getValue() === false ) {
			this.ufBackgroundUpload.disable();
			this.tfUseBackground.setValue( "" );
			this.tfUseBackground.disable();
			background = "none";
		}
		else {
			background = Ext.getCmp('bs-extjs-uploadCombo-background-hidden-field').getValue();
			this.tfUseBackground.setValue( background );
			this.tfUseBackground.enable();
		}

		var data = {
			id: 'general',
			name: this.tfName.getValue(),
			desc: this.tfDesc.getValue(),
			backgroundColor: this.pfBackgroundColor.getValue(),
			customBackgroundColor: this.tfCustomBackgroundColor.getValue().replace( "#", "" ),
			backgroundImage: background,
			repeatBackground: this.cgRepeatBackground.getValue()
		};

		$( document ).trigger( "BSFlexiskinMenuGeneralGetData", [this, data] );

		return data;
	},
	setData: function( data ) {
		this.currentData = data;
		this.tfName.setValue( data.config.name );
		this.tfDesc.setValue( data.config.desc );
		this.setColor( this.pfBackgroundColor, data.config.backgroundColor );
		this.tfCustomBackgroundColor.setValue( data.config.customBackgroundColor );
		this.cgRepeatBackground.setValue( data.config.repeatBackground );
		if ( typeof ( data.config.backgroundImage ) !== 'undefined' && data.config.backgroundImage !== "" ) {
			this.ufBackgroundUpload.btnReset.enable();
		}
		if ( typeof ( data.config.backgroundImage ) !== 'undefined' && data.config.backgroundImage === "none" ) {
			this.cbUseBackground.setValue( false );
			this.ufBackgroundUpload.disable();
		}
		if ( typeof ( data.config.backgroundImage ) !== 'undefined' && data.config.backgroundImage !== "none" ) {
			this.cbUseBackground.setValue( true );
			Ext.getCmp( 'bs-extjs-uploadCombo-background-hidden-field' ).setValue( data.config.backgroundImage );
		}

		$( document ).trigger( "BSFlexiskinMenuGeneralSetData", [this, data] );

	},
	setColor: function( el, clr ) {
		if( typeof clr == "undefined" || clr == null ) return;

		var bFound = false;
		clr = clr.replace( '#', "" );
		Ext.Array.each( el.colors, function( val ) {
			if ( clr == val ) {
				bFound = true;
			}
		});
		if ( bFound == false ){
			this.tfCustomBackgroundColor.setValue( clr );
			el.clear();
		}
		else
			el.select( clr );
	}
});