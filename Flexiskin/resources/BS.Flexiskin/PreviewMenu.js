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

		$( document ).trigger( "BSFlexiskinPreviewMenuInitComponent", [this, this.items] );

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

		$( document ).trigger( "BSFlexiskinMenuPreviewGetData", [this, data] );

		return data;
	},
	setData: function ( obj ) {
		if( !obj.config ) return;

		this.currentData = obj;
		var items = Ext.isArray( this.items ) ? this.items : this.items.items;
		for ( var i = 0; i < items.length; i++ ) {
			items[i].setData( {
				skinId: obj.skinId,
				config: obj.config[i]
			} );
		}

		$( document ).trigger( "BSFlexiskinMenuPreviewSetData", [this, this.items] );

		//this.callParent( arguments );
	},
	btnSaveClick: function(){
		this.setLoading( mw.message( 'bs-extjs-saving' ).plain() );
		this.btnReset.disable();
		var me = this;

		bs.api.tasks.exec( 'flexiskin', 'save', {
			data: this.getData(),
			id: this.currentData.skinId,
		})
		.done( function( response ){
			if ( response.success === true ) {
				me.parent.cpIframe.setLoading();
				me.parent.cpIframe.getEl().dom.src = response.src + "&useskin=flexiskin" + ( new Date() ).getTime() + Math.floor( Math.random() * 1000000 );
				me.currentData.skinId = response.id;
			}
			else {
				bs.util.alert( 'bs-flexiskin-saveskin-error',
						{
							text: response.msg,
							titleMsg: 'bs-extjs-error'
						}, {
					ok: function () {
					},
					cancel: function () {
					}
				});
			}
			me.setLoading( false );
		});
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
				bs.api.tasks.exec( 'flexiskin', 'reset', {
					id: me.currentData.skinId
				});
				me.parent.hide();
				Ext.getCmp( 'bs-flexiskin-panel' ).reloadStore();
			}
		})
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
				},
				scope: this
			}
		);
	},
	btnResetClickOk: function () {
		this.setLoading();
		this.btnSave.disable();
		this.btnReset.disable();
		var me = this;
		bs.api.tasks.exec( 'flexiskin', 'reset', {
			id: this.currentData.skinId
		})
		.done( function( response ){
			me.setLoading( false );
			me.parent.cpIframe.setLoading();
			me.parent.cpIframe.getEl().dom.src = response.src + "&useskin=flexiskin" + ( new Date() ).getTime() + Math.floor( Math.random() * 1000000 );
			me.setData( response.data );
		});
	},
	onItemStateChange: function () {
		this.btnSave.enable();
		this.btnReset.enable();
		var me = this;

		bs.api.tasks.exec( 'flexiskin', 'preview', {
			data: this.getData(),
			id: this.currentData.skinId
		})
		.done( function( response ){
			me.parent.cpIframe.setLoading();
			me.parent.cpIframe.getEl().dom.src = response.src + "&useskin=flexiskin" + ( new Date() ).getTime() + Math.floor( Math.random() * 1000000 );
			me.setLoading( false );
		});
	}
} );
