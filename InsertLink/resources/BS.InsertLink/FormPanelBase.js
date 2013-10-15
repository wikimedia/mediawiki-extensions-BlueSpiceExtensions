
Ext.define( 'BS.InsertLink.FormPanelBase', {
	extend: 'Ext.form.Panel',
	pnlMainConf: {
		items: null
	},
	initComponent: function() {
		
		this.beforeInitComponent();
		
		this.tfDesc = Ext.create( 'Ext.form.TextField', {
			fieldLabel : mw.message('bs-insertlink-label_description').plain(),
			name : 'inputDesc',
			value: '',
			width: 600
		});

		this.pnlMainConf.items.push(this.tfDesc);

		this.items = this.pnlMainConf.items;

		this.callParent(arguments);
	},
	
	beforeInitComponent: function() {},
	resetData: function() {
		this.tfDesc.reset();
	},
	setData: function( obj ) {
		if( !obj.desc ) return;
		this.tfDesc.setValue(obj.desc);
	},
	getData: function() {
		return this.getDescription();
	},
	setDescription: function( desc ) {
		//if(desc == '') return;
		this.tfDesc.setValue( desc );
	},
	getDescription: function() {
		return this.tfDesc.getValue();
	}
});