Ext.define('BS.Flexiskin.Menuitems.General', {
	extend: 'Ext.Panel',
	title: mw.message('bs-flexiskin-headerGeneral').plain(),
	layout: 'form',
	currentData: {},
	id: 'bs-flexiskin-preview-menu-general',
	initComponent: function() {
		this.tfName = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-flexiskin-labelName').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'name',
			allowBlank: false
		});
		this.tfDesc = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-flexiskin-labelDesc').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'desc',
			allowBlank: false
		});

		this.pfBackgroundColor = Ext.create('Ext.picker.Color', {
			value: '', // initial selected color
			id: 'bs-flexiskin-general-background-color',
			listeners: {
				select: function(picker, selColor) {
					Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
				},
				scope: this
			}
		});

		this.coBackgroundColorContainer = Ext.create('Ext.form.FieldContainer', {
			fieldLabel: mw.message('bs-flexiskin-labelBackgroundColor').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			items: [this.pfBackgroundColor]
		});

		this.pfCompleteColor = Ext.create('Ext.picker.Color', {
			value: '', // initial selected color
			id: 'bs-flexiskin-general-complete-color',
			listeners: {
				select: function(picker, selColor) {
					Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
				},
				scope: this
			}
		});
		this.coCompleteColorContainer = Ext.create('Ext.form.FieldContainer', {
			fieldLabel: mw.message('bs-flexiskin-labelCompleteColor').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			items: [this.pfCompleteColor]
		});
		this.ufBackgroundUpload = Ext.create('BS.form.UploadPanel', {
			url: bs.util.getAjaxDispatcherUrl('Flexiskin::uploadFile'),
			defaultValues: {
				uploadFormName: 'background',
				fieldLabel: mw.message('bs-flexiskin-labelBackgroundUpload').plain(),
				labelWidth: 100,
				resetButton: true
			}
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
			fieldLabel: mw.message('bs-flexiskin-labelRepeatBackground').plain(),
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
			this.coCompleteColorContainer,
			this.coBackgroundColorContainer,
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
			completeColor: this.pfCompleteColor.getValue(),
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
		this.setColor(this.pfCompleteColor, data.config.completeColor);
		this.cgRepeatBackground.setValue(data.config.repeatBackground)
		Ext.getCmp('bs-extjs-uploadCombo-background-hidden-field').setValue(data.config.backgroundImage);
	},
	setColor: function(el, clr) {
		var bFound = false;
		clr = clr.replace('#', "");
		Ext.Array.each(el.colors, function(val) {
			if (clr == val) {
				bFound = true;
			}
		});
		if (bFound == false)
			el.colors.push(clr);
		el.select(clr);
	}
});