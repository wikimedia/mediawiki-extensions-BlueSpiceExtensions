Ext.define('BS.FormattingHelp.Window', {
	extend: 'Ext.Window',
	height: 630,
	width: 760,
	autoScroll: true,
	closeAction: 'hide',
	singleton: true,

	initComponent: function(){
		this.setTitle( mw.message('bs-formattinghelp-formatting').plain() );
		
		this.pnlMain = Ext.create('Ext.Panel',{
			id: 'bs-formattinghelp-content',
			loader: {
				url: bs.util.getAjaxDispatcherUrl( 'FormattingHelp::getFormattingHelp' )
			},
			autoScroll: true
		});
		
		this.pnlMain.getLoader().load();
		
		this.items = [
			this.pnlMain
		];
		this.callParent(arguments);
	}
});