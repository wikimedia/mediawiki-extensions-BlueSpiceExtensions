Ext.define('BS.Flexiskin.PreviewWindow', {
	extend: 'Ext.window.Window',
	layout: 'border',
	maximized: true,
	closable: false,
	collapsible: false,
	frame: false,
	shadow: false,
	border: false,
	draggable: false,
	id: 'bs-flexiskin-config-window',
	singleton: true,
	closeAction: 'hide',
	//header: false,

	//Custom Setting
	currentData: {},
	
	initComponent: function() {
		this.pnlMenu = Ext.create("BS.Flexiskin.PreviewMenu");
		
		this.cpIframe = Ext.create( 'Ext.Component', {
			xtype: "component",
			region: 'center',
			id: 'bs-flexiskin-preview-frame',
			autoEl: {
				tag: "iframe",
				src: mw.util.wikiScript()
			},
			scope: this
		});
		
		this.items = [
			this.pnlMenu,
			this.cpIframe
		];

		this.on('show', this.onShow, this);
		//this.on('afterrender', this.onAfterRender, this);
		
		this.callParent(arguments);
	},
	
	onShow: function() {
		Ext.getCmp('bs-flexiskin-preview-frame').setLoading();
		Ext.get('bs-flexiskin-preview-frame').on('load', function() {
			Ext.getCmp('bs-flexiskin-preview-frame').setLoading(false);
		});
		this.setWidth(Ext.getBody().getWidth());
		
		this.callParent(arguments);
	},
	
	
	
	setData: function( obj ) {
		this.currentData = obj;
		Ext.getCmp('bs-flexiskin-preview-frame').autoEl.src = 
				mw.util.wikiScript() +"?"+ $.param({
					flexiskin: this.currentData.skinId
			});
		if( this.rendered ) {
			Ext.getCmp('bs-flexiskin-preview-frame').autoEl.src = 
				mw.util.wikiScript() +"?"+ $.param({
					flexiskin: this.currentData.skinId
			});
		}
		this.pnlMenu.setData( obj);
	}
});