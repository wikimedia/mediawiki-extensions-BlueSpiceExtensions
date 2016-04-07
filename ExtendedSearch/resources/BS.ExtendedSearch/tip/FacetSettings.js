Ext.define( 'BS.ExtendedSearch.tip.FacetSettings', {
	extend: 'Ext.tip.ToolTip',
	anchor: 'left',
	autoHide : false,
	autoShow: false,
	initComponent: function() {
		this.rdgOperator = new Ext.form.RadioGroup({
			width: 130,
			items: [
				{
					boxLabel: mw.message( 'bs-extendedsearch-facetsetting-op-or' ).plain(),
					name: 'op',
					inputValue: 'OR',
					checked: true
				},
				{
					boxLabel: mw.message( 'bs-extendedsearch-facetsetting-op-and' ).plain(),
					name: 'op',
					inputValue: 'AND'
				}
			]
		});
		this.rdgOperator.on( 'change', this.settingChanged, this );

		this.items = [
			this.rdgOperator
		];

		this.addEvents( 'settingschange' );

		this.callParent( arguments );
	},

	settingChanged: function( sender, newValue, oldValue, eOpts ) {
		//TODO: When more settings are available, we need to walk through all
		//fields and collect the whole "settings"
		$( this.target.dom ).data( 'fset', newValue );
		this.fireEvent( 'settingschange', newValue );
		this.hide();
	},

	setData: function( data ) {
		this.rdgOperator.suspendEvents( false ); //Do not reload the view
		this.rdgOperator.setValue( data );
		this.rdgOperator.resumeEvents();
	}
});