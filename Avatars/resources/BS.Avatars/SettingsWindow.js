Ext.define('BS.Avatars.SettingsWindow', {
	extend: 'Ext.window.Window',
	title: mw.message('bs-avatars-upload-title').plain(),
	id: 'bs-avatars-upload-window',
	width: 430,
	/*height: 200,*/
	singleton: true,
	closeAction: 'hide',
	bodyPadding: 5,
	layout: "form",
	//Custom Setting
	currentData: {},
	initComponent: function() {
		this.ufLogoUpload = Ext.create('BS.form.UploadPanel', {
			url: bs.util.getAjaxDispatcherUrl('Avatars::uploadFile'),
			uploadFormName: 'avatars',
			uploadFieldLabel: mw.message('bs-avatars-upload-label').plain(),
			uploadLabelWidth: 50,
			uploadButtonsInline: true
		});
		this.ufLogoUpload.on('upload', this.btnUploadClick, this);
		this.fsUpload = Ext.create('Ext.form.FieldSet', {
			title: mw.message('bs-avatars-file-upload-fieldset-title').plain(),
			collapsible: true,
			items: [
				this.ufLogoUpload
			]
		});
		this.tfUserImage = Ext.create('Ext.form.field.Text', {
			name: 'uimg',
			blankText: mw.message('bs-avatars-userimage-help').plain(),
			emptyText: mw.user.options.get('MW::UserImage'),
			allowBlank: false,
			labelWidth: 150,
			padding: "0 5 0 0"
		});
		this.bUserImage = Ext.create('Ext.Button', {
			text: mw.message('bs-extjs-save').plain(),
			flex:0.5
		});
		this.bUserImage.on('click', this.tfUserImageClick, this);
		this.fsUserImage = Ext.create('Ext.form.FieldSet', {
			title: mw.message('bs-avatars-userimage-title').plain(),
			collapsible: true,
			collapsed: true,
			items: [{
					xtype: 'fieldcontainer',
					// fieldLabel: mw.message('bs-avatars-userimage-title').plain(),
					layout: 'hbox',
					defaults: {
						flex: 1,
						hideLabel: true
					},
					items: [
						this.tfUserImage,
						this.bUserImage
					]
				}
			]
		});
		this.bGenerateNew = Ext.create('Ext.Button', {
			text: mw.message('bs-avatars-generate-new-label').plain(),
					//height: 50,
					width: "100%",
					margin: "0 0 10 0"
		});
		this.bGenerateNew.on('click', this.btnGenerateNewClick, this);
		this.fsGenerateNew = Ext.create('Ext.form.FieldSet', {
			title: mw.message('bs-avatars-auto-generate-fieldset-title').plain(),
			collapsible: true,
			collapsed: true,
			items: [
				this.bGenerateNew
			]
		});
		this.bCancel = Ext.create('Ext.Button', {
			text: mw.message('bs-extjs-cancel').plain()
		});
		this.bCancel.on('click', this.btnCancelClick, this);
		this.items = [
			this.fsUpload,
			this.fsUserImage,
			this.fsGenerateNew
		];
		this.buttons = [
			this.bCancel
		];

		this.callParent(arguments);
	},
	btnCancelClick: function() {
		this.close();
	},
	doGenerateNew: function() {
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl(
					'Avatars::generateAvatarAjax'
					),
			scope: this,
			method: 'post',
			success: function(response, opts) {
				var response = Ext.decode(response.responseText);
				bs.util.alert(
						'AMsuc',
						{
							text: response,
							titleMsg: 'bs-extjs-title-success'
						},
				{
					ok: function() {
						window.location.reload();
					},
					cancel: function() {
					},
					scope: this
				}
				);
			}
		});
	},
	confirmOverwrite: function(callback) {
		if (mw.user.options.get('MW::UserImage')) {
			bs.util.confirm('AMwarn2', {
				text: mw.message('bs-avatars-warning-text').plain(),
				title: mw.message('bs-avatars-warning-title').plain()},
			{
				ok: callback,
				scope: this
			}
			);
		}
		else {
			callback.apply(this);
		}
	},
	btnGenerateNewClick: function() {
		this.confirmOverwrite(this.doGenerateNew);
	},
	tfUserImageClick: function() {
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl(
					'Avatars::setUserImage',
					[this.tfUserImage.getValue()]
					),
			scope: this,
			method: 'post',
			success: function(response, opts) {
				var response = Ext.decode(response.responseText);
				if (!response.success) {
					bs.util.alert(
							'AMUfail',
							{
								text: response.message[0],
								titleMsg: 'bs-extjs-title-warning'
							},
					{
						ok: function() {
						},
						cancel: function() {
						},
						scope: this
					}
					);
					return;
				} else {
					bs.util.alert(
							'AMUsuc',
							{
								text: response.message[0],
								titleMsg: 'bs-extjs-title-success'
							},
					{
						ok: function() {
							window.location.reload();
						},
						cancel: function() {
						},
						scope: this
					}
					);
				}
			}
		});
	},
	doUpload: function() {
		var form = this.ufLogoUpload.getForm();
		if (!form.isValid())
			return;
		form.submit({
			params: {
				name: 'avatars'
			},
			waitMsg: mw.message('bs-extjs-uploading').plain(),
			success: function(fp, o) {
				//console.log(o);
				var responseObj = o.result;
				bs.util.alert('bs-flexiskin-saveskin-error',
						{
							text: responseObj.msg,
							titleMsg: 'bs-extjs-hint'
						}, {
					ok: function() {
						if (responseObj.success === true) {
							location.reload();
						}
					},
					scope: this
				}
				);
			},
			failure: function(fp, o) {
				//console.log(o);
				var responseObj = o.result;
				bs.util.alert('bs-flexiskin-saveskin-error',
						{
							text: responseObj.msg,
							titleMsg: 'bs-extjs-hint'
						}, {
					scope: this
				}
				);
			},
			scope: this
		});
	},
	btnUploadClick: function(el, form) {
		this.confirmOverwrite(this.doUpload);
	}
});