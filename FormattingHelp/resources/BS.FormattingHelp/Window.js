Ext.define('BS.FormattingHelp.Window', {
	extend: 'Ext.Window',
	autoScroll: true,
	closeAction: 'hide',
	singleton: true,
	id: 'bs-formattinghelp-window',

	initComponent: function(){
		this.setTitle( mw.message('bs-formattinghelp-formatting').plain() );
		
		this.pnlMain = Ext.create('Ext.Panel',{
			id: 'bs-formattinghelp-content',
			/*loader: {
				url: bs.util.getAjaxDispatcherUrl( 'FormattingHelp::getFormattingHelp' )
			},*/
			html: mw.message('bs-formattinghelp-help-text').plain(),
			autoScroll: true
		});
		
		//this.pnlMain.getLoader().load();
		
		this.items = [
			this.pnlMain
		];
		this.callParent(arguments);
	}
});