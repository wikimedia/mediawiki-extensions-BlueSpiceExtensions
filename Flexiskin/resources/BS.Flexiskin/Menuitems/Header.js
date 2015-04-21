Ext.define('BS.Flexiskin.Menuitems.Header', {
	extend: 'BS.Panel',
	title: mw.message('bs-flexiskin-headerheader').plain(),
	layout: 'form',
	currentData: {},
	parent: null,
	id: 'bs-flexiskin-preview-menu-header',
	initComponent: function() {
		this.ufLogoUpload = Ext.create('BS.form.UploadPanel', {
			url: mw.util.wikiScript('api'),
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
				name: 'logo',
				format: 'json'
			},
			success: function(response, action) {
				var responseObj = Ext.decode(response.responseText);
				responseObj = Ext.decode(responseObj.flexiskin);
				if (responseObj.success === true) {
					Ext.getCmp('bs-extjs-uploadCombo-logo-hidden-field').setValue(responseObj.name);
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
				Ext.getCmp('bs-extjs-uploadCombo-logo-hidden-field').setValue("");
				me.parent.onItemStateChange();
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