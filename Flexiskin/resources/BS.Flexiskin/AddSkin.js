Ext.define( 'BS.Flexiskin.AddSkin', {
	extend: 'BS.Window',
	id: 'bs-flexiskin-add-dlg',
	currentData: {},
	selectedData: {},
	afterInitComponent: function() {
		this.strSkins = Ext.create( 'Ext.data.JsonStore', {
			fields: [ 'flexiskin_id', 'flexiskin_name' ],
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'Flexiskin::getFlexiskins' ),
				reader: {
					type: 'json',
					root: 'flexiskin',
					idProperty: 'flexiskin_id'
				}
			},
			autoLoad: true
		});
		this.strSkins.on( 'load', this.onStrSkinsLoad, this );

		this.tfName = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message('bs-flexiskin-labelname').plain(),
			labelWidth: 130,
			labelAlign: 'left',
			name: 'name',
			required: true,
			allowBlank: false,
			id: 'bs-flexiskin-add-dlg-name'
		});
		this.tfDesc = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message('bs-flexiskin-labeldesc').plain(),
			labelWidth: 130,
			labelAlign: 'left',
			name: 'desc',
			id: 'bs-flexiskin-add-dlg-desc'
		});
		
		this.cbSkins = Ext.create( 'Ext.form.field.ComboBox', {
			fieldLabel: mw.message('bs-flexiskin-labelskins').plain(),
			labelWidth: 130,
			labelAlign: 'left',
			store: this.strSkins,
			valueField: 'flexiskin_id',
			displayField: 'flexiskin_name',
			forceSelection: true,
			allowBlank: false,
			id: 'bs-flexiskin-add-dlg-skins'
		} );

		this.items = [
		this.tfName,
		this.tfDesc,
		this.cbSkins
		];

		this.callParent(arguments);
	},
	resetData: function() {
		this.tfName.reset();
		this.tfDesc.reset();
		this.strSkins.reload();
		this.cbSkins.setValue('default');

		this.callParent();
	},
	setData: function( obj ) {
		this.currentData = obj;

		this.tfName.setValue( this.currentData.flexiskin_name );
		this.tfDesc.setValue( this.currentData.flexiskin_desc );
		if( !this.strSkins.isLoading() ) {
			this.cbSkins.setValue( this.getSkinsValue(this.currentData.skins) );
		}
	},
	getData: function() {
		if( this.cbSkins.validate() != true || this.tfDesc.validate() != true || this.tfName.validate() != true ) {
			return null;
		}

		this.selectedData.flexiskin_name = this.tfName.getValue();
		this.selectedData.flexiskin_desc = this.tfDesc.getValue();
		this.selectedData.skins = this.cbSkins.getValue();

		return this.selectedData;
	},
	onStrSkinsLoad: function( store, records, successful, eOpts ) {
		store.insert(0, {
				flexiskin_id:'default',
				flexiskin_name: mw.message('bs-flexiskin-defaultname').plain(),
				flexiskin_desc: mw.message('bs-flexiskin-defaultdesc').plain()
		});
		this.cbSkins.setValue('default');
	},
			
	getSkinsValue: function( data ) {
		var skins = [];
		for( var i = 0; i < data.length; i++ ) {
			skins.push( data[i].skin );
		}
		return skins;
	}
} );