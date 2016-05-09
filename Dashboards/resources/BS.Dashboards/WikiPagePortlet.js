Ext.define('BS.Dashboards.WikiPagePortlet', {
	extend: 'BS.portal.Portlet',
	height: 300,

	portletConfigClass : 'BS.Dashboards.WikiPagePortletConfig',

	initComponent: function() {
		//this.portletArticle
		this.cContent = Ext.create('Ext.Component', {
			loader: {
				url: '',
				target: this.cContent,
				renderer: function( loader, response, active ) {
					var responseObj = Ext.JSON.decode( response.responseText );
					loader.getTarget().update( responseObj.payload.html );
					return true;
				}
			},
			autoScroll: true
		});

		this.loadContent();

		this.items = [
			this.cContent
		];
		this.on( 'configchange', this.onConfigChange, this);
		this.callParent( arguments );
	},

	getPortletConfig: function() {
		var cfg = this.callParent( arguments );
		cfg.wikiArticle = this.wikiArticle;
		return cfg;
	},
	setPortletConfig: function( oConfig ) {
		this.wikiArticle = oConfig.wikiArticle;
		this.callParent( arguments );
	},
	onConfigChange: function( oConfig ) {
		this.loadContent();
	},
	loadContent: function() {
		var me = this;
		bs.api.tasks.exec(
			'dashboards-widgets',
			'wikipage',
			{
				"wikiArticle": me.wikiArticle
			}
		).done( function(response) {
			me.cContent.update( response.payload.html );
		});
	}
});
