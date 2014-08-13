Ext.define('BS.Flexiskin.Menuitems.Header', {
	extend: 'BS.Panel',
	title: mw.message('bs-flexiskin-headerheader').plain(),
	layout: 'form',
	currentData: {},
	id: 'bs-flexiskin-preview-menu-header',
	initComponent: function() {
		this.ufLogoUpload = Ext.create('BS.form.UploadPanel', {
			url: bs.util.getAjaxDispatcherUrl('Flexiskin::uploadFile'),
			uploadFormName: 'logo',
			uploadFieldLabel: mw.message('bs-flexiskin-labellogoupload').plain(),
			uploadLabelWidth: 50,
			uploadResetButton: true
		});
		this.ufLogoUpload.on('reset', this.btnResetClick, this);
		this.ufLogoUpload.on('upload', this.btnUploadClick, this);
		this.items = [
			this.ufLogoUpload
		];
		this.afterInitComponent();
		this.callParent(arguments);
	},
	btnUploadClick: function(el, form) {
		if (!form.isValid())
			return;
		form.submit({
			params: {
				id: this.currentData.skinId,
				name: 'logo'
			},
			waitMsg: mw.message('bs-extjs-uploading').plain(),
			success: function(fp, o) {
				var responseObj = o.result;
				if (responseObj.success === true) {
					Ext.getCmp('bs-extjs-uploadCombo-logo-hidden-field').setValue(responseObj.name);
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
	},
	btnResetClick: function(el) {
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl('Flexiskin::uploadFile'),
			params: {
				id: this.currentData.skinId,
				name: ''
			},
			callback: function(response) {
				Ext.getCmp('bs-extjs-uploadCombo-logo-hidden-field').setValue("");
				Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
			},
			scope: this
		});
	},
	afterInitComponent: function() {

	},
	getData: function() {
		var data = {
			id: 'header',
			logo: Ext.getCmp('bs-extjs-uploadCombo-logo-hidden-field').getValue()
		};
		return data;
	},
	setData: function(data) {
		this.currentData = data;
		Ext.getCmp('bs-extjs-uploadCombo-logo-hidden-field').setValue(data.config.logo);
	}
});