Ext.define( 'BS.InsertCategory.Dialog', {
	extend: 'BS.Window',
	id: 'bs-insertcategory',
	singleton: true,
	layout: 'border',
	height: 450,
	width:600,
	modal: true,
	title: mw.message('bs-insertcategory-title').plain(),

	isDirty: false,
	afterInitComponent: function() {
		this.strBoxSelect = Ext.create('Ext.data.JsonStore', {
			data: [
					{ text: 'Lorem', prefixedText: 'Kategorie:Lorem' },
					{ text: 'Ipsum', prefixedText: 'Kategorie:Ipsum' },
					{ text: 'dolor', prefixedText: 'Kategorie:dolor' },
					{ text: 'sit', prefixedText: 'Kategorie:sit' }
				],
			fields: ['text']
		});

		this.bsCategories = Ext.create('BS.form.CategoryBoxSelect', {
			fieldLabel: mw.message('bs-insertcategory-cat-label').plain(),
			labelAlign: 'top',
			id: 'bs-insertcategory-categorybox'
		});
		this.bsCategories.on( 'select', this.onSelect, this );
		this.bsCategories.on( 'change', this.onChange, this );

		this.bsCategoriesLabel = Ext.create( 'Ext.form.Label', {
			html: '<div class="bs-insertcategory-hint">'+mw.message('bs-insertcategory-hint').plain()+'</div>'
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
		if ( mw.config.get( 'BSInsertCategoryWithParents' ) ) {
			this.addValuesFromRecord( record );
		}
		else {
			this.bsCategories.addValue( [ record.data.text ] );
		}
	},
	addValuesFromRecord: function ( record ) {
		//parentNode is null if there is no parent, internalId "src" is the root of the categories
		if ( typeof ( record.parentNode ) !== "null" && record.parentNode.internalId !== "src" ) {
			this.addValuesFromRecord( record.parentNode );
		}
		this.bsCategories.addValue( [ record.data.text ] );
	},
	onSelect: function( sender, records) {
		this.isDirty = true;
	},
	onChange: function ( sender, newValue, oldValue, eOpts ) {
		this.isDirty = true;
		var values = newValue.split(',');
		var valuesToSet = [];
		$.each( values, function( index, element ){
			valuesToSet.push( element.ucFirst() );
		});
		sender.setValue( valuesToSet.join(','), true );
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