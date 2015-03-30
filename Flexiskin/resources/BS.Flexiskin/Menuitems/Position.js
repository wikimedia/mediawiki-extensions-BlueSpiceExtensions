Ext.define('BS.Flexiskin.Menuitems.Position', {
	extend: 'BS.Panel',
	title: mw.message('bs-flexiskin-headerposition').plain(),
	layout: 'form',
	currentData: {},
	id: 'bs-flexiskin-preview-menu-position',
	initComponent: function() {
		var nav_pos = Ext.create('Ext.data.Store', {
			fields: ['position', 'val'],
			data: [
				{"position": 'left', 'val': mw.message('bs-flexiskin-left').plain()},
				{"position": "right", 'val': mw.message('bs-flexiskin-right').plain()},
			]
		});
		this.cgNavigation = Ext.create('Ext.form.ComboBox', {
			fieldLabel: mw.message('bs-flexiskin-labelnavigation').plain(),
			mode: 'local',
			store: nav_pos,
			displayField: 'val',
			valueField: 'position',
			listeners: {
				'select': function(cb, rec) {
					Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
				},
				scope: this
			},
			scope: this
		});
		var cont_pos = Ext.create('Ext.data.Store', {
			fields: ['position', 'val'],
			data: [
				{"position": 'left', 'val': mw.message('bs-flexiskin-left').plain()},
				{"position": 'center', 'val': mw.message('bs-flexiskin-center').plain()},
				{"position": "right", 'val': mw.message('bs-flexiskin-right').plain()},
			]
		});
		this.cgContent = Ext.create('Ext.form.ComboBox', {
			fieldLabel: mw.message('bs-flexiskin-labelcontent').plain(),
			mode: 'local',
			store: cont_pos,
			displayField: 'val',
			valueField: 'position',
			listeners: {
				'select': function(cb, rec) {
					Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
				},
				scope: this
			},
			scope: this
		});
		this.tfWidth = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-flexiskin-labelwidth').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'width',
			allowBlank: false
		});
		this.tfWidth.on("blur", function(){
			Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
		});
		this.cbFullWidth = Ext.create('Ext.form.field.Checkbox', {
			fieldLabel: mw.message('bs-flexiskin-labelfullwidth').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'fullWidth',
			handler: this.onCbFullWidthChange, 
			scope: this
		});
		this.tfWidth.on('keyup', function() {
			//TODO: make this work...
			Ext.getCmp('bs-flexiskin-preview-menu').btnSave.enable();
		});
		this.items = [
			this.cgNavigation,
			this.cgContent,
			this.tfWidth,
			this.cbFullWidth
		];
		this.callParent(arguments);
	},
	onCbFullWidthChange: function( sender, checked ) {
		if( checked ) {
			this.tfWidth.disable();
		}
		else {
			this.tfWidth.enable();
		}
		Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
	},
	getData: function() {
		var data = {
			id: 'position',
			navigation: this.cgNavigation.getValue(),
			content: this.cgContent.getValue(),
			width: this.tfWidth.getValue(),
			fullWidth: this.cbFullWidth.getValue()
		};
		return data;
	},
	setData: function(data) {
		this.currentData = data;
		this.cgNavigation.setValue(this.currentData.config.navigation);
		this.cgContent.setValue(this.currentData.config.content);
		this.tfWidth.setValue(this.currentData.config.width);
		this.cbFullWidth.setValue(this.currentData.config.fullWidth);
	}
});