Ext.define( 'BS.Flexiskin.Menuitems.Header', {
	extend: 'Ext.Panel',
	require: [ 'BS.form.action.MediaWikiApiCall' ],
	title: mw.message( 'bs-flexiskin-headerheader' ).plain(),
	layout: 'form',
	currentData: {},
	parent: null,
	id: 'bs-flexiskin-preview-menu-header',
	initComponent: function() {
		this.ufLogoUpload = Ext.create( 'BS.form.UploadPanel', {
			url: mw.util.wikiScript( 'api' ),
			uploadFormName: 'logo',
			uploadFieldLabel: mw.message( 'bs-flexiskin-labellogoupload' ).plain(),
			uploadLabelWidth: 100,
			uploadResetButton: true
		});
		this.ufLogoUpload.on( 'reset', this.btnResetClick, this );
		this.ufLogoUpload.on( 'upload', this.btnUploadClick, this );
		this.items = [
			this.ufLogoUpload
		];

		$( document ).trigger( "BSFlexiskinMenuHeaderInitComponent", [this, this.items] );

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
				name: 'logo',
				format: 'xml'
			},
			success: function( response, action ) {
				var result = response.responseXML.getElementsByTagName( 'result' )[0];
				if( result.hasAttribute( 'success' ) ) {
					Ext.getCmp( 'bs-extjs-uploadCombo-logo-hidden-field' ).setValue(
						result.getAttribute( 'name' )
					);
					me.parent.onItemStateChange();
				}
				else {
					bs.util.alert( 'bs-flexiskin-saveskin-error', {
						text: result.getAttribute( 'message' ),
						titleMsg: 'bs-extjs-error'
					});
				}
			},
			scope: this
		}));
		this.ufLogoUpload.btnReset.enable();
	},
	btnResetClick: function( el ) {
		Ext.getCmp( 'bs-extjs-uploadCombo-logo-hidden-field' ).setValue( "" );
		this.parent.onItemStateChange();
		this.ufLogoUpload.btnReset.disable();
	},
	afterInitComponent: function() {
	},
	getData: function() {
		var data = {
			id: 'header',
			logo: Ext.getCmp( 'bs-extjs-uploadCombo-logo-hidden-field' ).getValue()
		};

		$( document ).trigger( "BSFlexiskinMenuHeaderGetData", [this, data] );

		return data;
	},
	setData: function( data ) {
		this.currentData = data;
		if ( typeof ( data.config.logo ) !== 'undefined' && data.config.logo !== "" ) {
			this.ufLogoUpload.btnReset.enable();
		}
		Ext.getCmp( 'bs-extjs-uploadCombo-logo-hidden-field' ).setValue( data.config.logo );

		$( document ).trigger( "BSFlexiskinMenuHeaderSetData", [this, data] );
	}
});