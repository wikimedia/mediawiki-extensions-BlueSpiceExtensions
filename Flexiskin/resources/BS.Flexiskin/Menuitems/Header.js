Ext.define( 'BS.Flexiskin.Menuitems.Header', {
	extend: 'Ext.Panel',
	require: [ 'BS.form.action.MediaWikiApiCall', 'BS.store.BSApi' ],
	title: mw.message( 'bs-flexiskin-headerheader' ).plain(),
	layout: 'form',
	currentData: {},
	parent: null,
	id: 'bs-flexiskin-preview-menu-header',
	initComponent: function() {
		this.strLogo = new BS.store.BSApi( {
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

		this.cgUseLogo = Ext.create( 'Ext.form.ComboBox', {
			fieldLabel: mw.message( 'bs-flexiskin-labellogoupload' ).plain(),
			queryMode: 'local',
			anyMatch: true,
			forceSelection: true,
			store: this.strLogo,
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

		this.buttonUploadLogo = Ext.create( 'Ext.Button', {
			tooltip: mw.message( 'bs-flexiskin-button-upload-image-tooltip' ).plain(),
			width: '20px',
			iconCls: 'bs-icon-upload',
			style: {
				marginLeft: '10px'
			},
			handler: function( oButton, oEvent ) {
				if ( !this.dlgUploadBackground ) {
					this.dlgUploadBackground = Ext.create( 'BS.Flexiskin.UploadFile', {
						target: 'logo',
						skinId: this.currentData.skinId
					} );
					this.dlgUploadBackground.on( 'uploadComplete', this.onLogoUploadComplete, this );
				}
				this.dlgUploadBackground.show();
			},
			scope: this
		});

		this.logoFieldContainer = {
			xtype: 'fieldcontainer',
			layout: 'hbox',
			defaults: {
				hideLabel: true
			},
			items: [
				this.cgUseLogo,
				this.buttonUploadLogo
			]
		};

		this.items = [
			this.logoFieldContainer
		];

		$( document ).trigger( "BSFlexiskinMenuHeaderInitComponent", [this, this.items] );

		this.callParent( arguments );
	},
	onLogoUploadComplete: function( window, fileName ) {
		this.cgUseLogo.getStore().reload();
		this.cgUseLogo.setValue( fileName );
		this.parent.onItemStateChange();
	},
	getData: function() {
		var data = {
			id: 'header',
			logo: this.cgUseLogo.getValue()
		};
			this.cgUseLogo.setValue( data.logo );
			this.cgUseLogo.enable();

		$( document ).trigger( "BSFlexiskinMenuHeaderGetData", [this, data] );

		return data;
	},
	setData: function( data ) {
		this.currentData = data;
		this.cgUseLogo.getStore().proxy.extraParams.query = this.currentData.skinId;
		this.cgUseLogo.getStore().reload();

		if ( typeof ( data.config.logo ) !== 'undefined' && data.config.logo !== "" ) {
			this.cgUseLogo.setValue(  data.config.logo );
		}

		$( document ).trigger( "BSFlexiskinMenuHeaderSetData", [this, data] );
	}
});