Ext.define( 'BS.Flexiskin.UploadFile', {
	extend: 'Ext.window.Window',
	requires: [ 'BS.form.action.MediaWikiApiCall', 'BS.store.BSApi' ],
	layout: 'fit',
	title: mw.message( 'bs-extjs-upload' ).plain(),
	style: {
		padding: '10px'
	},
	initComponent: function() {

		this.ufBackgroundUpload = Ext.create( 'BS.form.UploadPanel', {
			url: mw.util.wikiScript( 'api' ),
			uploadFormName: this.target,
			uploadFieldLabel: null,
			uploadLabelWidth: 100,
			uploadResetButton: false
		}),
		this.ufBackgroundUpload.on( 'reset', this.btnResetClick, this );
		this.ufBackgroundUpload.on( 'upload', this.btnUploadClick, this );

		this.items = [ this.ufBackgroundUpload ];

		this.addEvents( 'uploadComplete' );

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
				skinId: this.skinId,
				name: this.target,
				format: 'xml'
			},
			success: function( response, action ) {
				var result = response.responseXML.getElementsByTagName( 'result' )[0];
				if( result.hasAttribute( 'success' ) ) {
					me.fireEvent( 'uploadComplete', me, result.getAttribute( 'name' ) );
					this.close();
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
		this.ufBackgroundUpload.btnReset.enable();
	}
});