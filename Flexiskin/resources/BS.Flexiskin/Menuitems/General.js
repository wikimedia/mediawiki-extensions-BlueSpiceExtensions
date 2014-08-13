Ext.define('BS.Flexiskin.Menuitems.General', {
	extend: 'Ext.Panel',
	title: mw.message('bs-flexiskin-headergeneral').plain(),
	layout: 'form',
	currentData: {},
	id: 'bs-flexiskin-preview-menu-general',
	initComponent: function() {
		this.tfName = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-flexiskin-labelname').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'name',
			allowBlank: false
		});
		this.tfName.on("blur", function(el){
			Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
		});
		this.tfDesc = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-flexiskin-labeldesc').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'desc',
			allowBlank: false
		});
		this.tfDesc.on("blur", function(){
			Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
		});
		this.pfBackgroundColor = Ext.create('Ext.picker.Color', {
			value: '', // initial selected color
			id: 'bs-flexiskin-general-background-color',
			listeners: {
				select: function(picker, selColor) {
					this.tfCustomBackgroundColor.setValue(selColor.replace("#", ""));
					Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
				},
				scope: this
			}
		});

		this.coBackgroundColorContainer = Ext.create('Ext.form.FieldContainer', {
			fieldLabel: mw.message('bs-flexiskin-labelbgcolor').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			items: [this.pfBackgroundColor]
		});
		this.tfCustomBackgroundColor = Ext.create('Ext.form.TextField', {
			id: 'bs-flexiskin-general-custom-background-field',
			fieldLabel: mw.message('bs-flexiskin-labelcustombgcolor').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'customBackgroundColor',
			allowBlank: true
		});
		this.tfCustomBackgroundColor.on("blur", function(el){
			var isOk  = /(^#?[0-9A-F]{6}$)|(^#?[0-9A-F]{3}$)/i.test(el.getValue());
			Ext.getCmp("bs-flexiskin-preview-menu-general").setColor(Ext.getCmp("bs-flexiskin-preview-menu-general").pfBackgroundColor, el.getValue());
			if (isOk)
				Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
		});
		this.ufBackgroundUpload = Ext.create('BS.form.UploadPanel', {
			url: bs.util.getAjaxDispatcherUrl('Flexiskin::uploadFile'),
			uploadFormName: 'background',
			uploadFieldLabel: mw.message('bs-flexiskin-labelbackgroundupload').plain(),
			uploadLabelWidth: 100,
			uploadResetButton: true
		});
		var rep_back_pos = Ext.create('Ext.data.Store', {
			fields: ['repeating', 'val'],
			data: [
				{"repeating": "no-repeat", 'val': mw.message('bs-flexiskin-no-repeat').plain()},
				{"repeating": 'repeat-x', 'val': mw.message('bs-flexiskin-repeat-x').plain()},
				{"repeating": 'repeat-y', 'val': mw.message('bs-flexiskin-repeat-y').plain()},
				{"repeating": "repeat", 'val': mw.message('bs-flexiskin-repeat').plain()}
			]
		});
		this.cgRepeatBackground = Ext.create('Ext.form.ComboBox', {
			fieldLabel: mw.message('bs-flexiskin-labelrepeatbackground').plain(),
			mode: 'local',
			store: rep_back_pos,
			displayField: 'val',
			valueField: 'repeating',
			listeners: {
				'select': function(cb, rec) {
					Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
				},
				scope: this
			},
			scope: this
		});
		this.ufBackgroundUpload.on('reset', this.btnResetClick, this);
		this.ufBackgroundUpload.on('upload', this.btnUploadClick, this);

		this.items = [
			this.tfName,
			this.tfDesc,
			this.coBackgroundColorContainer,
			this.tfCustomBackgroundColor,
			this.ufBackgroundUpload,
			this.cgRepeatBackground
		];
		this.callParent(arguments);
	},
	btnUploadClick: function(el, form) {
		if (!form.isValid())
			return;
		form.submit({
			params: {
				id: this.currentData.skinId,
				name: 'background'
			},
			waitMsg: mw.message('bs-extjs-uploading').plain(),
			success: function(fp, o) {
				var responseObj = o.result;
				if (responseObj.success === true) {
					Ext.getCmp('bs-extjs-uploadCombo-background-hidden-field').setValue(responseObj.name);
					Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
				} else {
					bs.util.alert('bs-flexiskin-saveskin-error',
							{
								text: responseObj.msg,
								titleMsg: 'bs-extjs-error'
							}, {
						ok: function() {
						},
						cancel: function() {
						},
						scope: this
					}
					);
				}
			},
			scope: this
		});
		Ext.getCmp('bs-extjs-uploadCombo-background-reset-btn').enable();
	},
	btnResetClick: function(el) {
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl('Flexiskin::uploadFile'),
			params: {
				id: this.currentData.skinId,
				name: ''
			},
			callback: function(response) {
				Ext.getCmp('bs-extjs-uploadCombo-background-hidden-field').setValue("");
				Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
			},
			scope: this
		});
		Ext.getCmp('bs-extjs-uploadCombo-background-reset-btn').disable();
	},
	getData: function() {
		var data = {
			id: 'general',
			name: this.tfName.getValue(),
			desc: this.tfDesc.getValue(),
			backgroundColor: this.pfBackgroundColor.getValue(),
			customBackgroundColor: this.tfCustomBackgroundColor.getValue().replace("#", ""),
			backgroundImage: Ext.getCmp('bs-extjs-uploadCombo-background-hidden-field').getValue(),
			repeatBackground: this.cgRepeatBackground.getValue()
		};
		return data;
	},
	setData: function(data) {
		this.currentData = data;
		this.tfName.setValue(data.config.name);
		this.tfDesc.setValue(data.config.desc);
		this.setColor(this.pfBackgroundColor, data.config.backgroundColor);
		this.tfCustomBackgroundColor.setValue(data.config.customBackgroundColor);
		this.cgRepeatBackground.setValue(data.config.repeatBackground);
		Ext.getCmp('bs-extjs-uploadCombo-background-hidden-field').setValue(data.config.backgroundImage);
	},
	setColor: function(el, clr) {
		if( typeof clr == "undefined" || clr == null) return;

		var bFound = false;
		clr = clr.replace('#', "");
		Ext.Array.each(el.colors, function(val) {
			if (clr == val) {
				bFound = true;
			}
		});
		if (bFound == false){
			this.tfCustomBackgroundColor.setValue(clr);
			el.clear();
		}
		else
			el.select(clr);
	}
});