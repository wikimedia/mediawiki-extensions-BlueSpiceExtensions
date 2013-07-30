Ext.form.Action.SubmitWikiUpload = function(form, options){
	Ext.form.Action.Submit.superclass.constructor.call(this, form, options);
};

Ext.extend(Ext.form.Action.SubmitWikiUpload, Ext.form.Action.Submit, {
	createCallback : function(opts){
		var opts = opts || {};
		return {
			success: BsUploader.uploadFormSuccess,
			failure: BsUploader.failureRequest,
			scope: this,
			timeout: (opts.timeout*1000) || (this.form.timeout*1000),
			upload: this.form.fileUpload ? this.success : undefined
		};
	}
});

Ext.form.Action.ACTION_TYPES.submitwikiupload = Ext.form.Action.SubmitWikiUpload;


BsUploader = {
	i18n: {
		error: 'Error',
		warning: 'Warning',
		warning_exists: 'The file already exists with the name "{filename}".<br />Do you really want to proceed with this upload?',
		warning_duplicate: 'The file has been already uploaded with another name. <ul>{filenames}</ul> Do you really want to proceed with this upload?',
		waitMessage: 'Upload in progress ...',
		waitTitle: 'Please wait ...'
	},
	waitMessage: false,
	editToken: false,
	form: false,
	callback: false,
	init: function() {
		Ext.Ajax.request({
			url: wgScriptPath+'/api.php',
			success: BsUploader.initSuccess,
			failure: BsUploader.failureRequest,
			params: {
				action: 'query',
				prop: 'info',
				intoken: 'edit',
				titles: 'noexistant_page_bs_uploader',
				format: 'json'
			}
		});
	},
	showWaitMessage: function() {
		BsUploader.waitMessage = Ext.MessageBox.wait(BsUploader.i18n.waitMessage, BsUploader.i18n.waitTitle);
	},
	hideWaitMessage: function() {
		if(BsUploader.waitMessage) {
			BsUploader.waitMessage.hide();
			BsUploader.waitMessage = false;
		}
	},
	initSuccess: function(response, options) {
		var result = Ext.decode(response.responseText);
		BsUploader.editToken = result.query.pages['-1'].edittoken;
	},
	failureRequest: function(response, options) {
		//console.log('error');
		//console.log(response);
		//console.log(options);
	},
	doFormUpload: function(form, callback) {
		BsUploader.form = form
		BsUploader.callback = callback;
		BsUploader.showWaitMessage();
		BsUploader.form.doAction('submitwikiupload', {
			url: wgScriptPath+'/api.php',
			isUpload: true,
			params: {
				action: 'upload',
				token: BsUploader.editToken,
				format: 'json'
			}
		});
	},
	doFormUploadProceed: function(sessionkey) {
		BsUploader.showWaitMessage();
		Ext.Ajax.request({
			url: wgScriptPath+'/api.php',
			isUpload: true,
			params: {
				action: 'upload',
				filename: BsUploader.form.findField('filename').getValue(),
				text: BsUploader.form.findField('text').getValue(),
				watch: BsUploader.form.findField('watch').getValue(),
				ignorewarnings: BsUploader.form.findField('ignorewarnings').getValue(),
				token: BsUploader.editToken,
				sessionkey: sessionkey,
				format: 'json'
			},
			success: BsUploader.uploadFormSuccess,
			failure: BsUploader.failureRequest
		});
		
	},
	uploadFormSuccess: function(response, options) {
		BsUploader.hideWaitMessage();
		var obj = Ext.decode( response.responseText );
		if(typeof(obj.error) != 'undefined') {
			Ext.Msg.alert(BsUploader.i18n.error, obj.error.info);
		} 
		else if(obj.upload.result == 'Success') {
			var callback = BsUploader.callback;
			callback(obj.upload);
		}
		else if(obj.upload.result == 'Warning') {
			var msg = 'unknown warning';
			if(typeof(obj.upload.warnings.exists) != 'undefined') {
				msg = BsUploader.i18n.warning_exists.replace('{filename}', obj.upload.warnings.exists);
			}
			else if(typeof(obj.upload.warnings.duplicate) != 'undefined') {
				var names = obj.upload.warnings.duplicate;
				var filenames = '';
				for(var i=0; i<names.length; i++) {
					filenames = filenames + '<li>'+names[i]+'</li>'
				}
				msg = BsUploader.i18n.warning_duplicate.replace('{filenames}', filenames)
			}
			Ext.Msg.show({
				title: BsUploader.i18n.warning,
				msg: msg,
				fn: function(btn) {
					if(btn == 'yes') {
						BsUploader.doFormUploadProceed(obj.upload.sessionkey);
					}
				},
				buttons: Ext.Msg.YESNO,
				icon: Ext.MessageBox.QUESTION
			});
		}
	}
}

BsUploader.init();