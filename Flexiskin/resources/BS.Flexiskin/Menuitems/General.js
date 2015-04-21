Ext.define('BS.Flexiskin.Menuitems.General', {
	extend: 'Ext.Panel',
	require: ['BS.form.action.MediaWikiApiCall'],
	title: mw.message('bs-flexiskin-headergeneral').plain(),
	layout: 'form',
	currentData: {},
	parent: null,
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
			this.parent.onItemStateChange();
		});
		this.tfDesc = Ext.create('Ext.form.TextField', {
			fieldLabel: mw.message('bs-flexiskin-labeldesc').plain(),
			labelWidth: 100,
			labelAlign: 'left',
			name: 'desc',
			allowBlank: false
		});
		this.tfDesc.on("blur", function(){
			this.parent.onItemStateChange();
		});
		this.pfBackgroundColor = Ext.create('Ext.picker.Color', {
			value: '', // initial selected color
			id: 'bs-flexiskin-general-background-color',
			listeners: {
				select: function(picker, selColor) {
					this.tfCustomBackgroundColor.setValue(selColor.replace("#", ""));
					this.parent.onItemStateChange();
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
		var me = this;
		this.tfCustomBackgroundColor.on("blur", function(el){
			var isOk  = /(^#?[0-9A-F]{6}$)|(^#?[0-9A-F]{3}$)/i.test(el.getValue());
			me.setColor(me.pfBackgroundColor, el.getValue());
			if (isOk)
				this.parent.onItemStateChange();
		});
		this.ufBackgroundUpload = Ext.create('BS.form.UploadPanel', {
			url: mw.util.wikiScript('api'),
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
					this.parent.onItemStateChange();
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
		if (!form.isValid()){
			return;
		}
		var me = this;
		form.doAction(Ext.create('BS.form.action.MediaWikiApiCall', {
			form: form,
			params: {
				action: 'flexiskin',
				type: 'upload',
				mode: 'file',
				id: this.currentData.skinId,
				name: 'background',
				format: 'json'
			},
			success: function(response, action) {
				var responseObj = Ext.decode(response.responseText);
				responseObj = Ext.decode(responseObj.flexiskin);
				if (responseObj.success === true) {
					Ext.getCmp('bs-extjs-uploadCombo-background-hidden-field').setValue(responseObj.name);
					me.parent.onItemStateChange();
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
		}));
		this.ufBackgroundUpload.btnReset.enable();
	},
	btnResetClick: function(el) {
		var me = this;
		Ext.Ajax.request({
			url: mw.util.wikiScript('api'),
			params: {
				action: 'flexiskin',
				type: 'upload',
				mode: 'file',
				id: this.currentData.skinId,
				name: '',
				format: 'json'
			},
			callback: function(response) {
				Ext.getCmp('bs-extjs-uploadCombo-background-hidden-field').setValue("");
				me.parent.onItemStateChange();
			},
			scope: this
		});
		this.ufBackgroundUpload.btnReset.disable();
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