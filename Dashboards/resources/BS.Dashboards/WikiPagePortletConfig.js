Ext.define('BS.Dashboards.WikiPagePortletConfig', {
	extend: 'BS.portal.PortletConfig',

	afterInitComponent: function() {
		this.tfArticle = Ext.create( 'Ext.form.TextField',{
			fieldLabel: mw.message( 'bs-dashboard-userportlet-wikipage-wiki-article' ).plain(),
			labelAlign: 'right'
		});
		this.items.push( this.tfArticle );
		this.callParent( arguments );
	},
	setConfigControlValues: function( cfg ) {
		this.tfArticle.setValue( cfg.wikiArticle );
		this.callParent( arguments );
	},
	getConfigControlValues: function() {
		var cfg = this.callParent( arguments );
		cfg.wikiArticle = this.tfArticle.getValue();
		return cfg;
	}
});