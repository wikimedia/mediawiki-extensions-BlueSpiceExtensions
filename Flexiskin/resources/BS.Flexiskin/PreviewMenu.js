Ext.define( 'BS.Flexiskin.PreviewMenu', {
	extend: 'Ext.Panel',
	border: false,
	width: 400,
	layout: 'accordion',
	region: 'west',
	currentData: { },
	parent: null,
	id: 'bs-flexiskin-preview-menu',
	initComponent: function () {
		this.btnSave = Ext.create( "Ext.Button", {
			id: 'bs-flexiskin-preview-menu-save',
			text: mw.message( 'bs-extjs-save' ).plain(),
			disabled: true
		} );
		this.btnSave.on( 'click', this.btnSaveClick, this );
		this.btnClose = Ext.create( "Ext.Button", {
			id: 'bs-flexiskin-preview-menu-close',
			text: mw.message( 'bs-extjs-close' ).plain()
		} );
		this.btnClose.on( 'click', this.btnCloseClick, this );
		this.btnReset = Ext.create( "Ext.Button", {
			id: 'bs-flexiskin-preview-menu-reset',
			text: mw.message( 'bs-extjs-reset' ).plain(),
			disabled: true
		} );
		this.btnReset.on( 'click', this.btnResetClick, this );
		this.dockedItems = [ {
				id: 'bs-flexiskin-preview-menu-toolbar',
				xtype: 'toolbar',
				dock: 'top',
				ui: 'footer',
				items: [
					this.btnSave,
					this.btnClose,
					this.btnReset
				]
			} ];
		this.items = [
			Ext.create( "BS.Flexiskin.Menuitems.General", {
				parent: this
			} ),
			Ext.create( "BS.Flexiskin.Menuitems.Header", {
				parent: this
			} ),
			Ext.create( "BS.Flexiskin.Menuitems.Position", {
				parent: this
			} )
		];

		this.afterInitComponent( arguments );
		this.callParent( arguments );
	},
	afterInitComponent: function () {
		//this.on('afterrender', this.setData, this);
	},
	getData: function () {
		var data = [ ];
		var items = Ext.isArray( this.items ) ? this.items : this.items.items;
		for ( var i = 0; i < items.length; i++ ) {
			if ( typeof ( items[i].getData ) !== 'undefined' )
				data.push( items[i].getData() );
		}
		return data;
	},
	setData: function ( obj ) {
		this.currentData = obj;
		var items = Ext.isArray( this.items ) ? this.items : this.items.items;
		for ( var i = 0; i < items.length; i++ ) {
			items[i].setData( { skinId: obj.skinId, config: obj.config[i] } );
		}

		//this.callParent( arguments );
	},
	btnSaveClick: function () {
		this.setLoading( mw.message( 'bs-extjs-saving' ).plain() );
		var data = this.getData();
		this.btnReset.disable();
		var me = this;
		Ext.Ajax.request( {
			url: mw.util.wikiScript( 'api' ),
			params: {
				action: 'flexiskin',
				type: 'save',
				data: Ext.encode( data ),
				id: this.currentData.skinId,
				format: 'json'
			},
			success: function ( response ) {
				var responseObj = Ext.decode( response.responseText );
				responseObj = Ext.decode( responseObj.flexiskin );
				if ( responseObj.success === true ) {
					me.parent.cpIframe.setLoading();
					me.parent.cpIframe.getEl().dom.src = responseObj.src + "&useskin=flexiskin" + ( new Date() ).getTime() + Math.floor( Math.random() * 1000000 );
					this.currentData.skinId = responseObj.id;
				} else {
					bs.util.alert( 'bs-flexiskin-saveskin-error',
							{
								text: responseObj.msg,
								titleMsg: 'bs-extjs-error'
							}, {
						ok: function () {
						},
						cancel: function () {
						},
						scope: this
					}
					);
				}
				this.setLoading( false );

			},
			scope: this
		} );
	},
	btnCloseClick: function () {
		var me = this;
		bs.util.confirm(
				'bs-flexiskin-config-close',
				{
					titleMsg: 'bs-extjs-warning',
					textMsg: 'bs-flexiskin-dialogclose'
				},
		{
			ok: function () {
				Ext.Ajax.request( {
					url: mw.util.wikiScript( 'api' ),
					params: {
						action: 'flexiskin',
						type: 'reset',
						id: me.currentData.skinId,
						format: 'json'
					},
					scope: this
				} );
				me.parent.hide();
				Ext.getCmp( 'bs-flexiskin-panel' ).reloadStore();
			}
		}
		);
	},
	btnResetClick: function () {
		bs.util.confirm(
				'bs-flexiskin-config-close',
				{
					titleMsg: 'bs-extjs-warning',
					textMsg: 'bs-flexiskin-dialogreset'
				},
		{
			ok: function () {
				this.btnResetClickOk();
			}, scope: this
		}
		);
	},
	btnResetClickOk: function () {
		this.setLoading( );
		this.btnSave.disable();
		this.btnReset.disable();
		var me = this;
		Ext.Ajax.request( {
			url: mw.util.wikiScript( 'api' ),
			params: {
				action: 'flexiskin',
				type: 'reset',
				id: this.currentData.skinId,
				format: 'json'
			},
			success: function ( response ) {
				var responseObj = Ext.decode( response.responseText );
				responseObj = Ext.decode( responseObj.flexiskin );
				if ( responseObj.success === true ) {
					this.setLoading();
					me.parent.cpIframe.setLoading();
					me.parent.cpIframe.getEl().dom.src = responseObj.src + "&useskin=flexiskin" + ( new Date() ).getTime() + Math.floor( Math.random() * 1000000 );
					responseObj.data.config = Ext.decode( responseObj.data.config );
					this.setData( responseObj.data );
				} else {
					bs.util.alert( 'bs-flexiskin-addskin-error',
							{
								text: responseObj.msg,
								titleMsg: 'bs-extjs-error'
							}, {
						ok: function () {
						},
						cancel: function () {
						},
						scope: this
					}
					);
				}
				this.setLoading( false );
			},
			scope: this
		} );
	},
	onItemStateChange: function () {
		//this.setLoading( mw.message('bs-extjs-saving').plain());
		var data = this.getData();
		this.btnSave.enable();
		this.btnReset.enable();
		var me = this;
		Ext.Ajax.request( {
			url: mw.util.wikiScript( 'api' ),
			params: {
				action: 'flexiskin',
				type: 'save',
				mode: 'preview',
				data: Ext.encode( data ),
				id: this.currentData.skinId,
				format: 'json'
			},
			success: function ( response ) {
				var responseObj = Ext.decode( response.responseText );
				responseObj = Ext.decode( responseObj.flexiskin );
				if ( responseObj.success === true ) {
					me.parent.cpIframe.setLoading();
					me.parent.cpIframe.getEl().dom.src = responseObj.src + "&useskin=flexiskin" + ( new Date() ).getTime() + Math.floor( Math.random() * 1000000 );
				} else {
					bs.util.alert( 'bs-flexiskin-saveskinpreview-error',
							{
								text: responseObj.msg,
								titleMsg: 'bs-extjs-error'
							}, {
						ok: function () {
						},
						cancel: function () {
						},
						scope: this
					}
					);
				}
				this.setLoading( false );
			},
			scope: this
		} );
	}
} );
