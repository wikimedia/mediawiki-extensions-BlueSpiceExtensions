Ext.define( 'BS.InsertCategory.Dialog', {
	extend: 'BS.Window',
	singleton: true,
	layout: 'border',
	height: 350,
	width:500,
	modal: true,
	isDirty: false,
	afterInitComponent: function() {
		this.strBoxSelect = Ext.create('Ext.data.JsonStore', {
			/*proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl('InsertMagic::ajaxGetData'),
				reader: {
					type: 'json',
					root: 'categories',
					idProperty: 'prefixedText'
				}
			},*/
			data:/* {
				categories: */[
					{ text: 'Lorem', prefixedText: 'Kategorie:Lorem' },
					{ text: 'Ipsum', prefixedText: 'Kategorie:Ipsum' },
					{ text: 'dolor', prefixedText: 'Kategorie:dolor' },
					{ text: 'sit', prefixedText: 'Kategorie:sit' }
				]
			/*}*/,
			fields: ['text']
		});
		
		this.bsCategories = Ext.create('BS.form.CategoryBoxSelect', {
			fieldLabel: mw.message('bs-insertcategory-cat_tag').plain(),
			labelAlign: 'top'
		});
		this.bsCategories.on( 'select', this.onSelect, this );
		this.bsCategories.on( 'change', this.onChange, this );

		
		this.bsCategoriesLabel = Ext.create( 'Ext.form.Label', {
			html: '<div style="padding:3px; background-color:#e3e5eb;">'+mw.message('bs-insertcategory-hint').plain()+'</div>'
		});
		
		this.pnlMain = Ext.create( 'Ext.form.FormPanel', {
			region: 'center',
			bodyPadding: 5,
			items: [
				this.bsCategories,
				this.bsCategoriesLabel
			]
		});
		
		this.tpCategories = Ext.create('BS.InsertCategory.AsyncCategoryTreePanel', {
			collapsible: true,
			collapsed: false,
			region: 'west'
		});
		this.tpCategories.on( 'itemclick', this.onItemClick, this );
		
		this.items = [
			this.tpCategories,
			this.pnlMain
		];
		this.callParent();
	},
	onItemClick: function( tree, record, item, index, e, eOpts ) {
		this.isDirty = true;
		this.bsCategories.addValue( [record.data.text] );
	},
	onSelect: function( sender, records) {
		this.isDirty = true;
	},
	onChange: function ( sender, newValue, oldValue, eOpts ) {
		this.isDirty = true;
		var values = newValue.split(',');
		var valuesToSet = [];
		$.each( values, function( index, element ){
			valuesToSet.push( $.ucFirst( element ) );
		});
		sender.setValue( valuesToSet.join(','), true )
	},
	setData: function( data ) {
		this.bsCategories.setValue( data );
	},
	
	getData: function() {
		var aCategories = [];
		
		$.each( this.bsCategories.getValueRecords(), function( index, value ) {
			aCategories.push( value.raw.text );
		});
		
		return aCategories;
	}
});