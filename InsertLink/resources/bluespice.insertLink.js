// Register buttons with hwactions plugin of VisualEditor
$(document).bind('hwactions-init', function( event, plugin, buttons, commands ){
	var t = plugin;
	var ed = t.editor;

	buttons.push({
		buttonId: 'hwlink',
		buttonConfig: {
			title : mw.message('bs-insertlink-button_title').plain(),
			cmd : 'mceHwLink',
			image : wgScriptPath+'/extensions/BlueSpiceExtensions/InsertLink/resources/images/hwlink.gif'
		}
	});

	commands.push({
		commandId: 'mceHwLink',
		commandCallback: function() {
			LinkChooser.data.href=false;
			LinkChooser.data.raw=false;
			LinkChooser.data.content=false;
			LinkChooser.data.type=false;
			LinkChooser.data.selection=false;

			var node = t.editor.selection.getNode();

			var link = t.editor.dom.getParent(node, "a");
			if ( !link && node ) {
				// Maybe link is already included in selection
				var nodeName = node.nodeName.toLowerCase();
				if ( nodeName == 'a' ) link = node;
				/*
				else {
					// Sometimes tinyMCE puts in marker spans before and after the link
					var theChild = node.childNodes[1];
					if ( theChild && theChild.nodeName.toLowerCase() == 'a' ) {
						link = theChild;
					}
				}
				*/
			}
			if (link) {
				t.editor.selection.select(link);
				var outer = t.editor.dom.getOuterHTML(link);
				LinkChooser.data.href=t.editor.dom.getAttrib(link, "href");
				LinkChooser.data.raw=outer;
				LinkChooser.data.type=t.editor.dom.getAttrib(link, "type");
				LinkChooser.data.content=link.innerHTML;
				LinkChooser.data.link = link;
			}
			else {
				var hwcontent = t.editor.selection.getContent();
				LinkChooser.data.content=hwcontent.replace(/<.*?>/ig, '');
			}
			LinkChooser.data.selection = t.editor.selection.getBookmark();

			// Fix bug with cursor after table. IE will place everything within the table otherwise.
			// Solution: move selectionstart one step further
			var parentTag = ed.dom.getParent(node);
			if (parentTag.nodeName.toLowerCase() == 'body') {
				LinkChooser.data.selection.start++;
			}

			LinkChooser.show();
		}
	});
	
	//Override default command "mceLink"
	commands.push({
		commandId: 'mceLink',
		commandCallback: function( ui, v ) {
			this.execCommand( 'mceHwLink', ui );
		}
	});
	
	//Override default command "mceAdvLink"
	commands.push({
		commandId: 'mceAdvLink',
		commandCallback: function( ui, v ) {
			this.execCommand( 'mceHwLink', ui );
		}
	});
	
	plugin.editor.onNodeChange.add(function(ed, cm, element, c, o) {
		if (element.nodeName == 'A') {
			if ( t.elementIsCategoryAnchor( element ) ) {
				cm.setActive(  'hwlink', false);
				cm.setDisabled('hwlink', true);
			}
			else if ( t.elementIsMediaAnchor( element ) ) {
				cm.setActive(  'hwlink', false);
				cm.setDisabled('hwlink', true);
			}
			else {
				cm.setActive(  'hwlink', true);
				cm.setDisabled('hwlink', false);
			}
		}
		else {
			cm.setActive('hwlink', false);
		}

		var selectedInnerHTML = ed.selection.getContent();
		// in the very last text line, IE wraps selection in p-tag. so we need to get rid of this
		selectedInnerHTML = selectedInnerHTML.replace(/^<p>(.*)<\/p>$/mi, "");
		if (selectedInnerHTML.match(/<[^a].*?>/gmi)) {
			cm.setDisabled('hwlink', true);
			cm.setDisabled('unlink', true);
		}
	});
});

if (bsInsertLinkUseFilelinkApplet) {
	if ( navigator.appName == 'Microsoft Internet Explorer' ) {
		bsInsertLinkUseFilelinkApplet = false;
	}
}

LinkChooser = {
	win: false,
	tab : 'norm',
	data: {},
	config: {
		urlPages: BlueSpice.buildRemoteString('InsertLink', 'getPage', {
			ns: 0
		}),
		urlNS: BlueSpice.buildRemoteString('InsertLink', 'getNamespace'),
		urlIW: BlueSpice.buildRemoteString('InsertLink', 'getInterwiki'),
		width:630
	},
	actionDone: false,
	appletCreated: false,
	show: function() {
		//this.win = false;
		if(!this.win) {
			// TODO MRG (27.09.10 14:11): das könnte man an den tab "Link auf Datei" binden, oder besser
			// auf den klick auf den button, wenn das geht.
			// RE MRG (17.11.10 10:34): Geht leider (noch) nicht, weil der IE da nicht mitmacht
			if (bsInsertLinkUseFilelinkApplet && !this.appletCreated)
			{
				this.appletCreated = true;
				oApplet = document.createElement('applet');
				oBody = document.getElementsByTagName('body')[0];
				oApplet.setAttribute('code', 'HWFileChooserApplet.class');
				oApplet.setAttribute('id', 'HWFileChooserApplet');
				oApplet.setAttribute('name', 'HWFileChooserApplet');
				oApplet.setAttribute('scriptable', 'true');
				oApplet.setAttribute('mayscript', 'true');
				oApplet.setAttribute('codebase', wgScriptPath+'/extensions/BlueSpiceExtensions/InsertLink/resources/');
				oApplet.setAttribute('style', 'width:0px;height:0px;padding:0;margin:0;');
				oApplet.setAttribute('height', '1');
				oApplet.setAttribute('width', '1');
				oBody.appendChild(oApplet);
			}
			this.storePages = new Ext.data.JsonStore({
				url: this.config.urlPages,
				root: 'items',
				fields: ['name', 'label', 'ns']
			});
			this.storePages.load();

			this.storeNS = new Ext.data.JsonStore({
				url: this.config.urlNS,
				root: 'items',
				fields: ['name', 'label', 'ns']
			});
			this.storeNS.load();

			this.storeIW = new Ext.data.JsonStore({
				url: this.config.urlIW,
				root: 'items',
				fields: ['name', 'label']
			});
			this.storeIW.load();

			var cfg = {
				title: mw.message('bs-insertlink-dlg_title').plain(),
				id: 'link_chooser_dlg',
				layout: 'form',
				minWidth: 700,
				autoHeight: true,
				modal: true,
				closeAction: 'hide',
				border: false,
				style: {
					textAlign:'left'
				},
				items:[{
					id: 'link_chooser_tab_panel',
					xtype: 'tabpanel',
					activeTab: 0,
					defaults: {
						bodyStyle: 'height:0px'
					},
					items:[{
						title: mw.message('bs-insertlink-tab_wiki_page').plain(),
						listeners: {
							'activate': {
								fn:function() {
									tab = 'ns';
								}
							}
						},
						tbar: {
							layout: 'hbox',
							items :[mw.message('bs-insertlink-label_namespace').plain(), {
								xtype: 'tbspacer',
								width: 10
							}, {
								xtype: 'combo',
								flex: 1,
								// TODO MRG (27.09.10 14:13): bs...
								id: 'hw_insertNamespace',
								store: this.storeNS,
								displayField:'name',
								typeAhead: true,
								mode: 'local',
								triggerAction: 'all',
								forceSelection: true,
								emptyText:mw.message('bs-insertlink-select_a_namespace').plain(),
								listeners: {
									'select': {
										fn:function(sel, value) {
											//console.log(value);
											/*var selPage = Ext.getCmp('hw_pageNameNs');
											selPage.setValue('');
											selPage.store.filter('ns', sel.getValue());*/
											//console.log(sel);
											//console.log(value);
											this.storePages.load({
												params:{
													ns: value.data.ns
												}
											});
										},
										scope:this
									}
								}
							}, {
								xtype: 'tbspacer',
								width: 20
							}, mw.message('bs-insertlink-label_page').plain(),
							{
								xtype: 'tbspacer',
								width: 10
							}, {
								xtype: 'combo',
								flex: 1,
								// TODO MRG (27.09.10 14:13): bs...
								id: 'hw_pageNameNs',
								store: this.storePages,
								displayField:'name',
								typeAhead: true,
								mode: 'local',
								triggerAction: 'all',
								emptyText:mw.message('bs-insertlink-select_a_page').plain(),
								lastQuery: ''
							}]
						}
					}, {
						title: mw.message('bs-insertlink-tab_external_link').plain(),
						listeners: {
							'activate': {
								fn:function() {
									tab = 'ext';
								}
							}
						},
						tbar:{
							layout: 'hbox',
							items: [
							mw.message('bs-insertlink-label_link').plain(), {
								xtype: 'tbspacer',
								width: 10
							},{
								xtype: 'textfield',
								// TODO MRG (27.09.10 14:13): bsInsertLinkTargetUrl?
								id: 'hw_targetUrl',
								value: 'http://',
								flex: 1,
								listeners: {
									focus: {
										fn: function(field) {
											if ( field.getValue() == '' ) {
												field.setValue('http://');
											}
										}
									},
									render: function(c) {
										c.getEl().on('keyup', function(field, e) {
											// prevent double http: when pasting urls
											var v = Ext.getCmp('hw_targetUrl').getValue();
											if (v.match( 'http://http://' )) {
												v = v.replace( 'http://http://', 'http://' );
												Ext.getCmp('hw_targetUrl').setValue( v );
											}
											if (v.match( 'http://https://' )) {
												v = v.replace( 'http://https://', 'https://' );
												Ext.getCmp('hw_targetUrl').setValue( v );
											}
										})
									}
								}
							}]
						}
					}, {
						title: mw.message('bs-insertlink-tab3_title').plain(),
						listeners: {
							'activate': {
								fn:function() {
									tab = 'mail';
								}
							}
						},
						tbar:{
							layout: 'hbox',
							items: [mw.message('bs-insertlink-label_mail').plain(), {
								xtype: 'tbspacer',
								width: 10
							}, {
								xtype: 'textfield',
								// TODO MRG (27.09.10 14:13): bs...
								id: 'hw_email',
								flex: 1
							}]
						}
					}, {
						title: mw.message('bs-insertlink-tab5_title').plain(),
						listeners: {
							'activate': {
								fn:function() {
									tab = 'iw';
								}
							}
						},
						tbar:{
							layout: 'hbox',
							items: [mw.message('bs-insertlink-label_prefix').plain(), {
								xtype: 'tbspacer',
								width: 10
							}, {
								xtype: 'combo',
								flex: 1,
								// TODO MRG (27.09.10 14:13): bs...
								id: 'hw_insertInterwiki',
								store: this.storeIW,
								displayField:'name',
								typeAhead: true,
								mode: 'local',
								triggerAction: 'all',
								emptyText:mw.message('bs-insertlink-select_a_page').plain()
							}, {
								xtype: 'tbspacer',
								width: 15
							}, mw.message('bs-insertlink-label_page').plain(), {
								xtype: 'tbspacer',
								width: 10
							}, {
								xtype: 'textfield',
								// TODO MRG (27.09.10 14:13): bs...
								id: 'hw_pageNameIl',
								flex: 1
							}]
						}
					}, {
						title: mw.message('bs-insertlink-tab6_title').plain(),
						listeners: {
							'activate': {
								fn:function() {
									tab = 'file';
								}
							}
						},
						tbar:{
							layout: 'hbox',
							items: [mw.message('bs-insertlink-label_file').plain(), {
								xtype: 'tbspacer',
								width: 10
							}, {
								xtype: 'textfield',
								inputType: 'file',
								// TODO MRG (27.09.10 14:13): bs...
								id: 'hw_pageNameFile'
								//,
								//flex: 1
							}]
						}
					}]
				}, {
					bodyStyle: 'height:0px',
					tbar: {
						layout: 'hbox',
						items: [ mw.message('bs-insertlink-label_description').plain(), {
							xtype: 'tbspacer',
							width: 10
						}, {
							xtype: 'textfield',
							// TODO MRG (27.09.10 14:13): bs...
							id: 'hw_pageAlias',
							flex: 1
						}]
					},
					buttons: [{
						id: 'ok-btn',
						text: mw.message('bs-insertlink-label_ok').plain(),
						handler: this.doCallback,
						scope: this
					},{
						text: mw.message('bs-insertlink-label_cancel').plain(),
						handler: function(){
							this.win.hide();
						},
						scope: this
					}]
				}]
			}

			//cfg.items[0][4].tbar[1].width=20;
			if (bsInsertLinkUseFilelinkApplet) {
				cfg.items[0].items[4].tbar = {
					layout: 'hbox',
					items: [mw.message('bs-insertlink-label_file').plain(), {
						xtype: 'tbspacer',
						width: 10
					}, {
						xtype: 'textfield',
						inputType: 'text',
						// TODO MRG (27.09.10 14:13): bs...
						id: 'hw_pageNameFile',
						flex: 1
					}, {
						xtype: 'button',
						inputType: 'button',
						// TODO MRG (27.09.10 14:13): bs...
						id: 'hw_searchFile',
						text: mw.message('bs-insertlink-label_searchfile').plain(),
						handler: function(){
							document.HWFileChooserApplet.openDialog('onFileDialogFile', 'onFileDialogCancel');
						},
						width: 150
					}]
				}
			}
			//if (!bsInsertLinkShowFilelink) {
			//    cfg.items[0]. .items[5] = null;
			//}
			Ext.apply(cfg, this.config);
			//Ext.getCmp('link_chooser_tab_panel').activate(5);
			this.win = new Ext.Window(cfg);
			this.win.on( 'hide', function(){
				if(!this.actionDone) {
					BsCore.restoreSelection();
					BsCore.restoreScrollPosition();
				}
			}, this );
		}
		if (!bsInsertLinkShowFilelink) Ext.getCmp('link_chooser_tab_panel').remove(5);
		//if (!bsUseFilelinkApplet) {
		//Ext.getCmp('hw_pageNameFile').;
		//Ext.getCmp('hw_pageNameFile').inputType = "file";
		//}
		this.init();
		this.win.show();
	},
	
	init : function() {
		//txtarea.focus();
		this.actionDone = false;
		if ((typeof(VisualEditorMode)=="undefined") || !VisualEditorMode )
		{
			BsCore.saveScrollPosition();
			this.data.content = BsCore.saveSelection();
			// CR MRG (30.06.11 23:32): this.data.href festlegen
		}
		Ext.getCmp('link_chooser_tab_panel').activate(0);
		//if(Ext.getCmp('hw_pageName')) Ext.getCmp('hw_pageName').clearValue();
		if(Ext.getCmp('hw_targetUrl')) {
			Ext.getCmp('hw_targetUrl').reset();
			Ext.getCmp('hw_targetUrl').setValue('http://');
		}
		if(Ext.getCmp('hw_email')) Ext.getCmp('hw_email').reset();
		if(Ext.getCmp('hw_insertNamespace')) Ext.getCmp('hw_insertNamespace').clearValue();
		if(Ext.getCmp('hw_pageNameNs')) Ext.getCmp('hw_pageNameNs').clearValue();
		if(Ext.getCmp('hw_insertInterwiki')) Ext.getCmp('hw_insertInterwiki').clearValue();
		if(Ext.getCmp('hw_pageNameIl')) Ext.getCmp('hw_pageNameIl').reset();
		if(Ext.getCmp('hw_pageNameFile')) Ext.getCmp('hw_pageNameFile').reset();

		if (this.data.content) {
			Ext.getCmp('hw_pageAlias').setValue(this.data.content);
		}
		else {
			Ext.getCmp('hw_pageAlias').setValue('');
		}

		if (this.data.href) {
			if (this.data.type == "hw_internal_link") {
				link = String(this.data.href);
				link = link.replace(wgServer+"/", "");
				//link = link.replace("", "");
				link = unescape(link);
				pagealias = this.data.content.split( '|' );
				if( pagealias.length > 1 ) {
					pagealias = pagealias[1];
					pagealias = pagealias.replace( ']]', '' );
				} else {
					pagealias = pagealias[0];
				}

				if ( link.match( ':' ) ) {
					parts = link.split( ':' );
					if( parts.length == 3 ) parts.shift();
					nsText = parts.shift();
					pageTitle = parts.join( ':' );
				} else {
					nsText = false;
					pageTitle = link;
				}

				Ext.getCmp('link_chooser_tab_panel').activate(0);
				Ext.getCmp('hw_pageNameNs').setValue(pageTitle);
				Ext.getCmp('hw_pageAlias').setValue(pagealias);
				if (nsText) Ext.getCmp('hw_insertNamespace').setValue(nsText);
			}
			else if (String(this.data.href).indexOf('mailto:')>-1) {
				//TODO: alert???
				//alert(this.data.href);
				link = String(this.data.href).replace("mailto://", "");
				link = unescape(link);
				Ext.getCmp('link_chooser_tab_panel').activate(2);
				Ext.getCmp('hw_email').setValue(link);
			}
			else if (String(this.data.href).indexOf('file:')>-1) {
				//TODO: alert???
				//alert(this.data.href);
				link = String(this.data.href).replace("file://", "");
				link = unescape(link);
				Ext.getCmp('link_chooser_tab_panel').activate(5);
				Ext.getCmp('hw_pageNameFile').setValue(link);
			}
			else {
				link = this.data.href;
				link = unescape(link);
				Ext.getCmp('link_chooser_tab_panel').activate(1);
				Ext.getCmp('hw_targetUrl').setValue(link);
			}
		}
		else if(this.data.content) {
			if(this.data.content.match(/\[\[(.*?)\]\]/)){
				link = this.data.content;
				link = link.replace( '[[', '' );
				link = link.replace( ']]', '' );
				parts = link.split( '|' );
				nspage = parts[0].split( ':' );
				if( nspage.length > 1 ) {
					if( nspage.length == 3 ) nspage.shift();
					Ext.getCmp('hw_insertNamespace').setValue(nspage[0]);
					Ext.getCmp('hw_pageNameNs').setValue(nspage[1]);
					Ext.getCmp('hw_pageAlias').setValue(parts[1]);
				} else {
					Ext.getCmp('hw_pageNameNs').setValue(nspage[0]);
					Ext.getCmp('hw_pageAlias').setValue(parts[1]);
				}
				//Ext.getCmp('hw_targetUrl').setValue(link);
			}
		}
	},

	filterNS : function() {
		var ns = Ext.getCmp('hw_insertNamespace').getValue();
		Ext.getCmp('hw_pageNameNs').store.filter('ns', ns);
	},

	doCallback : function() {
		this.insertLink(tab);
		this.win.hide();
	},
	insertLink : function(tab) {
		this.actionDone = true;
		if(tab == 'norm') {
			linktext = Ext.getCmp('hw_pageNameNs').getValue();
			alias = Ext.getCmp('hw_pageAlias').getValue();
			if (alias != "") alias = '|'+alias;
			lOpener = '[[';
			lCloser = alias+']]';
			text = linktext;
		}
		if(tab == 'ext') {
			linktext = Ext.getCmp('hw_targetUrl').getValue();
			if(linktext == 'http://') {
				linktext = '';
			}
			alias = Ext.getCmp('hw_pageAlias').getValue();
			if (alias != "") alias = ' '+alias;
			lOpener = '[';
			lCloser = alias+']';
			text = linktext;
			if(!text.match(/:\/\//) && text != '') {
				text = 'http://' + text;
			}
		}
		if(tab == 'mail') {
			linktext = Ext.getCmp('hw_email').getValue();
			alias = Ext.getCmp('hw_pageAlias').getValue();
			if (alias != "") alias = ' '+alias;
			lOpener = '[mailto:';
			lCloser = alias+']';
			text = linktext;
		}
		if(tab == 'ns') {
			linktext = Ext.getCmp('hw_pageNameNs').getValue();
			alias = Ext.getCmp('hw_pageAlias').getValue();
			prefix = Ext.getCmp('hw_insertNamespace').getValue();
			nsPos = Ext.getCmp('hw_insertNamespace').store.find( 'label', prefix );
			if ( nsPos != -1 ) {
				nsIndex = Ext.getCmp('hw_insertNamespace').store.getAt( nsPos ).get( 'ns' );
			};
			if ( nsPos == -1 || nsIndex == 0 ) {
				prefix = '';
			}
			
			if(bsInsertLinkEscapeNs.indexOf(prefix) != -1) {
				prefix = ':'+prefix;
			}
			if (prefix && prefix != '' ) prefix += ':';
			if (alias != "") alias = '|'+alias;
			lOpener = '[['+prefix;
			lCloser = alias+']]';
			text = linktext;
		}
		if(tab == 'iw') {
			linktext = Ext.getCmp('hw_pageNameIl').getValue();
			alias = Ext.getCmp('hw_pageAlias').getValue();
			prefix = Ext.getCmp('hw_insertInterwiki').getValue();
			if (prefix) prefix += ':';
			if (alias != "") alias = '|'+alias;
			lOpener = '[['+prefix;
			lCloser = alias+']]';
			text = linktext;
		}
		if(tab == 'file') {
			linktext = Ext.getCmp('hw_pageNameFile').getValue();
        
			// TODO MRG (27.09.10 14:15): das sollte ein konfigurierbares array sein LEVEL_PUBLIC
			// array("s:" => "//ipbrick/daten"
			if ((typeof(drivelist)!="undefined") && drivelist )
			{
				for (d in drivelist)
				{
					linktext = linktext.replace(drivelist[d].drive, drivelist[d].path);
				}
			}
			alias = Ext.getCmp('hw_pageAlias').getValue();
			if (alias != "") alias = ' '+alias;
			//This might be up tp five slashes.
			lOpener = '[file:///';
			lCloser = alias+']';
			//text = encodeURI(linktext.replace(/\\/g, '/')); // That's not so good, Al.
			//According to http://blogs.msdn.com/b/ie/archive/2006/12/06/file-uris-in-windows.aspx
			//in order to represent a non-US-ASCII character you should use that character directly
			//so encodeURI() does a little too much - just encoding spaces to %20 should be enough:
			text = linktext.replace(/\\/g, '/');
			text = text.replace(/ /g, '%20');
		}

		if ((typeof(VisualEditorMode)=="undefined") || !VisualEditorMode )
		{
			//insertTags(lOpener, lCloser, text);
			if(text == '') {
				BsCore.restoreSelection();
			}
			else {
				BsCore.restoreSelection(lOpener+text+lCloser);
			}
			BsCore.restoreScrollPosition();
		}
		else
		{
			if(text != '') {
				//tinyMCE.activeEditor.focus();
				//tinyMCE.tinyMCEPopup.restoreSelection();
				tinyMCE.activeEditor.selection.moveToBookmark(this.data.selection);
				//This is important for IE
				tinyMCE.activeEditor.dom.setOuterHTML(this.data.link, "");
				//alert(tinyMCE.activeEditor.selection.getContent({format : 'text'}));
				tinyMCE.execCommand('mceInsertRawHTML', false, lOpener+text+lCloser);
			}
			tinyMCE.activeEditor.selection.moveToBookmark(this.data.selection);
			tinyMCE.activeEditor.selection.collapse(true);
	    
		}
	}
};

onFileDialogFile = function(path) {
	document.getElementById('hw_pageNameFile').value = path;
//newpath = "file:///"+path.replace("\\", "/");
//alert(newpath);
}

onFileDialogCancel = function() {
	//alert('you cancelled');
	}
