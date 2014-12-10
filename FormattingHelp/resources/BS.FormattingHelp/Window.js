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
				url: bs.util.getAjaxDispatcherUrl( 'FormattingHelp::getFormattingHelp' ),
				autoLoad: true
			},
			html: '',
			autoScroll: true
		});

		this.items = [
			this.pnlMain
		];
		this.callParent(arguments);
	}
});