var pasteImage = {
	i18n : {
		textName : "Please enter a name for the picture to upload:",
		button_title: "Paste Image",
		title_confirm: "Name picture",
		btn_ok: "Ok",
		error_title: "Error",
		too_big: "The given picture is too big. Please use the file upload.",
		corrupted_file: "Could not read the given image as it seems to be corrupted.",
		error_upload: "Could not upload the given image. Please try again.",
		stack_fail: "Could not get an image from clipboard. Please try copying again.",
		unexpected_error: "An unexpected error occured. Please try again.",
		wrong_version: "Your Java version is too old to run Paste Image. Please update your JRE to at least version 1.7."
	},
	images : {},
	appletResult : {},
	confirmName : {},
	applet : document.pasteImageApplet,
	pasteDiv : "",
	pasteDivLoader : "",
	init : function(){
		pasteImage.confirmName = new Ext.Window({
			title: this.i18n.title_confirm,
			items: [{
				xtype: 'form',
				id: 'panelFormCheck',
				layout:'table',
				layoutConfig: {columns: 2},
				labelWidth: 130,
				padding: 10,
				items: [{
						xtype: 'displayfield',
						html: this.i18n.textName,
						name: 'changetextmsg',
						width: 350,
						colspan:2,
						style: {
							marginBottom: "10px"
						}
					},{
						xtype: 'textfield',
						name: 'changetextmsg',
						id: 'pasteImage_input',
						width: 350,
						colspan:2,
						style: {
							marginBottom: "10px"
						}
					}
				]
			}],
			buttons : [{
				id : 'ok',
				text: pasteImage.i18n.btn_ok,
				handler: function(){
					var text = Ext.getCmp("pasteImage_input").getValue();
					pasteImage.confirmName.hide();
					$.ajax({
						url : BsCore.buildRemoteString('PasteImage', 'pasteImageCheckImagename'),
						type : "POST",
						data : {name : text},
						async : false,
						complete : function ( sResponseData ){
							result = $.parseJSON(sResponseData.responseText);
							if (result.success == "false")
								Ext.Msg.alert("Error", result.message, function(){
									pasteImage.confirmName.show();
								});
							else{
								pasteImage.appletResult = pasteImage.applet.uploadTmpImage(text);
								pasteImage.appletResult = $.parseJSON(pasteImage.appletResult);
								if (pasteImage.appletResult.success == "true"){
									pasteImage.setPreviewImg(pasteImage.appletResult.previewurl, pasteImage.appletResult.name);
									pasteImage.insertImageEditor(pasteImage.appletResult.name, pasteImage.appletResult.thumburl, pasteImage.appletResult.fullurl);
									pasteImage.images [pasteImage.appletResult.name] = pasteImage.appletResult;
									Ext.getCmp("pasteImage_input").setValue('');
									pasteImage.confirmName.hide();
								}
								else
									pasteImage.errorHandler(pasteImage.appletResult);
							}
						}
					});
				},
				scope: this
			}],
			width: 400,
			autoScroll: true,
			constrainHeader: true,
			closable : false,
			modal:true
		})
	},
	setVisible : function(bShow){
		if (bShow){
			$('#pasteImage').css('display', 'block'); 
		}
		else{
			$('#pasteImage').css('display', 'none');
			$('#pasteImage_loader').css('display', 'none');
		}
	},
	imagePaste : function(){
		result = $.parseJSON(pasteImage.applet.pasteImage());        
		if (result.success != "true")
			pasteImage.errorHandler(result);
	},
	showNameConfirm : function (){
		pasteImage.confirmName.show();
		pasteImage.confirmName.center();
	},
	insertImageEditor : function(name, thumburl, fullurl){
		var imgSrc = "";
		if (thumburl != undefined && thumburl != "")
			imgSrc = thumburl;
		else
			imgSrc = fullurl;
		tinymce.execCommand('mceInsertContent', false, "<img src='"+imgSrc+"' alt='"+name+"' id='"+name+"'/>");
		return;
	},
	setPreviewImg : function(path, alt){
		var div = $('<div>');
		var deleteDiv = $('<div>');
		deleteDiv.addClass('delete_img');
		var img = $('<img>');
		img.attr('src', path);
		img.attr('alt', alt);
		img.attr('id', alt);
		$("#images > div").each(function(i, v){
			$(v).removeClass("selectedImg");
		});
		div.addClass("selectedImg");
		img.load(function(){
			div.append(img);
			div.append(deleteDiv);
			pasteImage.bindPreviewEvents(div);
			$("#images").append(div);
			pasteImage.reloadSlider();
			pasteImage.hideWaitMessage();
		});
		return;
	},
	bindPreviewEvents : function(div){
		$(div).hover(function(){
			$(this).find("div.delete_img").css('display', 'block');
		}, function(){
			$(this).find("div.delete_img").css('display', 'none');
		});
		$(div).find("div.delete_img").click(function(){
			var image = pasteImage.images[$(this).parent().find('img').attr('alt')];
			while (tinyMCE.activeEditor.dom.get(image.name)){
				tinymce.activeEditor.dom.remove(image.name);
			}
			pasteImage.deleteImage(image.name);
			$(this).parent().remove();
		});
	},
	deleteImage : function(imgName){
		$.ajax({
				url : BsCore.buildRemoteString('PasteImage', 'pasteImageRemoveImage'),
				data : {name : imgName},
				async : true,
				complete : function ( sResponseData ){}
		});
		return;
	},
	sResponseData : null,
	getSelectedImage : function(){
		var imgAlt = $("#images > div.selectedImg > img").attr('alt');
		if (imgAlt == undefined)
			return null;
		return pasteImage.images[imgAlt] != undefined ? pasteImage.images[imgAlt] : null;
	},
	showWaitMessage: function() {
		$("#pasteImage_loader").css("display", "block");
	},
	hideWaitMessage: function() {
		$("#pasteImage_loader").css("display", "none");
	},
	pasteImage2html: function (text){
		var images = text.match(/<pasteImage>(.*?)<\/pasteImage>/gi);
		if (images){
			for (var i=0; i<images.length; i++){
				var image = images[i].replace('<pasteImage>', '');
				image = image.replace('</pasteImage>', '');
				if (pasteImage.images [image] ['thumburl'] != undefined && pasteImage.images [image] ['thumburl'] != "")
					var sImageUrl = pasteImage.images [image] ['thumburl'];
				else
					var sImageUrl = pasteImage.images [image] ['fullurl'];
				var sLink = '<img src="'+sImageUrl+'" data-mce-src="'+sImageUrl+'" alt="'+pasteImage.images [image]['name']+'" id="'+pasteImage.images [image]['name']+'">';
				text = text.replace(images[i], sLink);
			}
		}
		return text;
	},
	pasteImage2wiki: function (text){
		var images = text.match(/(<a([^>]*?)>)?<img([^>]*?)\/>(<\/a>)?/gi);
		if (images){
			for (var i=0; i<images.length; i++){
				var image = images[i];
				var srcAttr = image.match(/src="(.*?)"/i);
				if (srcAttr && srcAttr[1]){
					if (srcAttr[0].indexOf("UploadStash") != -1 ||
						srcAttr[0].indexOf("Hochladespeicher") != -1){
						var aUrl = srcAttr[0].split("/");
						var sUrl = aUrl[aUrl.length - 1].slice(0,-1);
						if (sUrl.slice(0, 6) == "500px-")
							sUrl = sUrl.slice(6, sUrl.length);
						var pasteImageTag = "<pasteImage>"+sUrl+"</pasteImage>";
						text = text.replace(image, pasteImageTag);
						continue;
					}
				}
			}
		}
		return text;
	},
	reloadSlider : function(){
		var iMiddleImg = 0;
		$("#images > div").each(function(i, v){
			if ($(v).hasClass('selectedImg')){
				iMiddleImg = i;
				return;
			}
		});
		var new_margin = "0px";
		if (iMiddleImg == 0)
			new_margin = (($("#pastedImages").width()/2-$('#images > div.selectedImg').width()/2))+"px";
		else
			new_margin = ((($("#pastedImages").width()/2)-$('#images > div.selectedImg').width()/2)-(iMiddleImg*110))+"px";
		if ($("#images > div").length > 1)
			$("#images").stop(false, true).animate({'margin-left' : new_margin}, 1000);
		else
			$("#images").css('margin-left', new_margin);
	},
	errorHandler : function(errorObj){
		var message = "";
		if (errorObj.code != undefined){
			switch(errorObj.code){
				case "0":
					message = pasteImage.i18n.too_big;
					break;
				case "1":
					message = pasteImage.i18n.corrupted_file;
					break;
				case "2":
					message = pasteImage.i18n.upload_error;
					break;
				case "3":
					message = pasteImage.i18n.stack_fail;
					break;
				default:
					message = pasteImage.i18n.unexpected_error;
					break;
			}
		}
		else if (errorObj.message != undefined){
			message = errorObj.message;
		}
		else
			message = this.i18n.unexpected_error;
		pasteImage.hideWaitMessage();
		pasteImage.confirmName.hide();
		Ext.getCmp("pasteImage_input").setValue('');
		Ext.Msg.alert(this.i18n.error_title, message);
	},
	wrongJavaVersion: function(){
		$("#pasteImage").replaceWith("<div id='pasteImage_wrong_version'>"+pasteImage.i18n.wrong_version+"</div>");
		$("#pasteImage_loader").remove();
	}
};
$(document).bind('hwactions-init', function( event, plugin, buttons, commands ){
	if (!deployJava.versionCheck("1.7.0+")){
		pasteImage.wrongJavaVersion();
		return;
	}
	var t = plugin;
	var ed = t.editor;
	pasteImage.init();
	//bind to mceAddControl?
	ed.onPostRender.add(function(ed, cm) {
		$("#wpTextbox1_toolbargroup > span").append($("#pasteImage"));
		$("#wpTextbox1_toolbargroup > span").append($("#pasteImage_loader"));
		pasteImage.setVisible(true);
		  //tinyMCE.activeEditor.dom.add(tinyMCE.activeEditor.get("wpTextbox1_toolbargroup"), 'p', {title : 'my title'}, 'Some content');
	  });
	ed.onRemove.add(function(ed, cm){
		$("#editform").before($("#pasteImage"));
		$("#editform").before($("#pasteImage_loader"));
		pasteImage.setVisible(false);
	});
	ed.onPaste.addToTop(function(ed, e){
		var resulttext = $.parseJSON(pasteImage.applet.checkClipboard());
		if (resulttext.text == "true")
			return;
		pasteImage.showWaitMessage();
		pasteImage.showNameConfirm();
		pasteImage.imagePaste();
		//need to abort firefox, otherwise it's pasting base64 to tinymce...
		/*if (bRes)*/
		tinymce.dom.Event.cancel(e);
		return false;
	});
	buttons.push({
		buttonId: 'hwpasteImage',
		buttonConfig: {
			title : pasteImage.i18n.button_title,
			cmd : 'pasteImage',
			image : wgScriptPath+'/extensions/BlueSpiceExtensions/PasteImage/images/copy.gif'
		}
	});
	commands.push({
		commandId: 'pasteImage',
		commandCallback: function() {
			var selectedImg = pasteImage.getSelectedImage();
			pasteImage.insertImageEditor(selectedImg.name, selectedImg.thumburl, selectedImg.fullurl);
		}
	});
});
$(document).bind('BSVisualEditorBeforeWikiToHtml', function (event, textObject){
	textObject.text = pasteImage.pasteImage2html(textObject.text);
});

$(document).bind('BSVisualEditorBeforeHtmlToWiki', function (event, textObject){
	textObject.text = pasteImage.pasteImage2wiki(textObject.text);
});
$("#pasteImage_left").bind('click', function(event){
	if ($("#images > div.selectedImg") == undefined)
		return
	$("#images > div.selectedImg").prev().addClass("selectedImg");
	$("#images > div.selectedImg").next().removeClass("selectedImg");
	pasteImage.reloadSlider()
});
$("#pasteImage_right").bind('click', function(event){
	if ($("#images > div.selectedImg") == undefined)
		return
	$("#images > div.selectedImg").next().addClass("selectedImg");
	$("#images > div.selectedImg").prev().removeClass("selectedImg");
	pasteImage.reloadSlider()
});
$(document).live('drop', function(e){
	alert('Please drop to the provided field.');
	e.preventDefault();
	tinymce.dom.Event.cancel(e);
});

$("#images div").live('click', function(e) {
		if ($(this).hasClass('selectedImg'))
			return;
		$("#images div").each(function(i, v){
			$(v).removeClass("selectedImg");
		});
		//$(this).css("border", "black solid 1px");
		$(this).addClass("selectedImg");
		pasteImage.reloadSlider();
});
$(document).live('dropStart', function(){
		pasteImage.showWaitMessage();
});


