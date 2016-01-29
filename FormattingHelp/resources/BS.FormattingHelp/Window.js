Ext.define('BS.FormattingHelp.Window', {
	extend: 'Ext.Window',
	autoScroll: true,
	closeAction: 'hide',
	singleton: true,
	id: 'bs-formattinghelp-window',
	height: 600,
	width: 600,

	initComponent: function(){
		this.setTitle( mw.message('bs-formattinghelp-formatting').plain() );

		this.pnlMain = Ext.create('Ext.Panel',{
			id: 'bs-formattinghelp-content',
			loader: {
				url: mw.util.wikiScript( 'api' ),
				autoLoad: true,
				params: {
					action: 'bs-formattinghelp',
					task: 'getFormattingHelp',
					format: 'json'
				},
				renderer: this.resultRenderer
			},
			html: '',
			autoScroll: true
		});

		this.items = [
			this.pnlMain
		];
		this.callParent(arguments);
	},

	resultRenderer: function( loader, response, active ) {
		var result = Ext.decode( response.responseText );
		loader.getTarget().update( result.payload.html );
		return true;
	}
});