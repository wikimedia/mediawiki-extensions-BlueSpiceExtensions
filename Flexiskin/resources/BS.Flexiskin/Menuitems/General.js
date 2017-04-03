Ext.define( 'BS.Flexiskin.Menuitems.General', {
	extend: 'Ext.Panel',
	require: [ 'BS.form.action.MediaWikiApiCall', 'BS.store.BSApi' ],
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

		this.tfName.on( "blur", function( el ) {
				this.parent.onItemStateChange();
			},
			this
		);

		this.tfDesc = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-flexiskin-labeldesc' ).plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'desc',
			allowBlank: false
		});

		this.tfDesc.on( "blur", function() {
				this.parent.onItemStateChange();
			},
			this
		);

		this.pfBackgroundColor = Ext.create( 'Ext.picker.Color', {
			value: '',
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

		this.cbUseBackground = Ext.create( 'Ext.form.field.Checkbox', {
			fieldLabel: mw.message( 'bs-flexiskin-usebackground' ).plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'useBackground',
			handler: this.onCbUseBackgroundChange,
			scope: this
		});

		var rep_back_pos = Ext.create( 'Ext.data.Store', {
			fields: [ 'repeating', 'val' ],
			data: [
				{ "repeating": "no-repeat", 'val': mw.message( 'bs-flexiskin-no-repeat' ).plain() },
				{ "repeating": 'repeat-x', 'val': mw.message( 'bs-flexiskin-repeat-x' ).plain() },
				{ "repeating": 'repeat-y', 'val': mw.message( 'bs-flexiskin-repeat-y' ).plain() },
				{ "repeating": "repeat", 'val': mw.message( 'bs-flexiskin-repeat' ).plain() }
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

		this.strBackground = new BS.store.BSApi( {
			apiAction: 'bs-flexiskin-upload-store',
			proxy: {
				extraParams: {
					query: this.currentData.skinId
				}
			},
			fields: [ 'filename' ],
			sortInfo: {
				field: 'flexiskin_name',
				direction: 'ASC'
			}
		} );

		this.buttonUploadBackground = Ext.create( 'Ext.Button', {
			tooltip: mw.message( 'bs-flexiskin-button-upload-image-tooltip' ).plain(),
			width: '20px',
			iconCls: 'bs-icon-upload',
			style: {
				marginLeft: '10px'
			},
			handler: function( oButton, oEvent ) {
				if ( !this.dlgUploadBackground ) {
					this.dlgUploadBackground = Ext.create( 'BS.Flexiskin.UploadFile', {
						target: 'background',
						windowlabel:  mw.message( 'bs-extjs-upload' ),
						skinId: this.currentData.skinId
					} );
					this.dlgUploadBackground.on( 'uploadComplete', this.onBackgroundUploadComplete, this );
				}
				this.dlgUploadBackground.show();
			},
			scope: this
		});

		this.cgUseBackground = Ext.create( 'Ext.form.ComboBox', {
			fieldLabel: mw.message( 'bs-flexiskin-labelcurrentbackground' ).plain(),
			queryMode: 'local',
			anyMatch: true,
			forceSelection: true,
			store: this.strBackground,
			displayField: 'filename',
			valueField: 'filename',
			flex: 1,
			listeners: {
				'select': function( cb, rec ) {
					this.parent.onItemStateChange();
				},
				scope: this
			},
			scope: this
		});

		this.backgroundFieldContainer = {
			xtype: 'fieldcontainer',
			layout: 'hbox',
			defaults: {
				hideLabel: true
			},
			items: [
				this.cgUseBackground,
				this.buttonUploadBackground
			]
		};

		this.items = [
			this.tfName,
			this.tfDesc,
			this.coBackgroundColorContainer,
			this.tfCustomBackgroundColor,
			this.cbUseBackground,
			this.backgroundFieldContainer,
			this.cgRepeatBackground
		];

		$( document ).trigger( "BSFlexiskinMenuGeneralInitComponent", [this, this.items] );

		this.callParent( arguments );
	},

	onBackgroundUploadComplete: function( window, fileName ) {
		this.cgUseBackground.getStore().reload();
		this.cgUseBackground.setValue( fileName );
		this.parent.onItemStateChange();
	},

	onCbUseBackgroundChange: function( sender, checked ) {
		if( checked ) {
			this.cgUseBackground.enable()
			this.buttonUploadBackground.enable();
			this.cgRepeatBackground.enable();
		}
		else {
			this.cgUseBackground.disable();
			this.buttonUploadBackground.disable();
			this.cgRepeatBackground.disable();
		}
		this.parent.onItemStateChange();
	},
	getData: function() {
		var background;
		if ( this.cbUseBackground.getValue() === false ) {
			this.cgUseBackground.disable();
			this.buttonUploadBackground.disable();
			this.cgRepeatBackground.disable();
			background = "none";
		}
		else {
			if ( this.cgUseBackground.getValue() === null ) {
				background = this.currentBackground;
			}
			else {
				background = this.cgUseBackground.getValue();
			}
			this.cgUseBackground.setValue( background );
			this.cgUseBackground.enable();
			this.buttonUploadBackground.enable();
			this.cgRepeatBackground.enable();
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
		this.cgUseBackground.getStore().proxy.extraParams.query = this.currentData.skinId;
		this.cgUseBackground.getStore().reload();

		this.tfName.setValue( data.config.name );
		this.tfDesc.setValue( data.config.desc );

		this.setColor( this.pfBackgroundColor, data.config.backgroundColor );
		this.tfCustomBackgroundColor.setValue( data.config.customBackgroundColor );

		this.cgRepeatBackground.setValue( data.config.repeatBackground );
		this.currentBackground = data.config.backgroundImage;
		if ( typeof ( this.currentBackground ) !== 'undefined' && this.currentBackground === "none" ) {
			this.cbUseBackground.setValue( false );
			this.cgUseBackground.disable();
			this.buttonUploadBackground.disable();
		}
		if ( typeof ( this.currentBackground ) !== 'undefined' && this.currentBackground !== "none" ) {
			this.cbUseBackground.setValue( true );
			this.cgUseBackground.enable();
			this.cgUseBackground.setValue( this.currentBackground );
			this.buttonUploadBackground.enable();
		}

		$( document ).trigger( "BSFlexiskinMenuGeneralSetData", [this, data] );

	},
	setColor: function( el, clr ) {
		if( typeof clr === "undefined" || clr === null ) return;

		var bFound = false;
		clr = clr.replace( '#', "" );
		Ext.Array.each( el.colors, function( val ) {
			if ( clr === val ) {
				bFound = true;
			}
		});
		if ( bFound === false ){
			this.tfCustomBackgroundColor.setValue( clr );
			el.clear();
		}
		else
			el.select( clr );
	}
});