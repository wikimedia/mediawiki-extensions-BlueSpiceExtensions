//Wire up buttons in ExtendedEditbar
$(document).on( 'click', '#bs-editbutton-insertfile', function( e ){
	e.preventDefault();
	var me = this;
	Ext.require('BS.InsertFile.FileDialog', function(){
		BS.InsertFile.FileDialog.clearListeners();
		BS.InsertFile.FileDialog.on( 'cancel', bs.util.selection.reset );
		BS.InsertFile.FileDialog.on( 'ok', function( dialog, data ) {
			var formattedNamespaces = mw.config.get('wgFormattedNamespaces');
			if( data.nsText == 'media' ) {
				data.nsText = formattedNamespaces[bs.ns.NS_MEDIA];
			} else {
				data.nsText = formattedNamespaces[bs.ns.NS_FILE];
			}
			data.caption = data.displayText;
			delete( data.src );
			var wikiLink = new bs.wikiText.Link( data );
			bs.util.selection.restore( wikiLink.toString() );
			BS.InsertFile.FileDialog.setData({});
		});

		var data = {};
		var selection = bs.util.selection.save();
		if( selection !== '' ) {
			var checkIsWikiLink = selection.match(/\[\[.*?\]\]/);
			var wikiLink = new bs.wikiText.Link( selection );
			var wikiLinkNs = wikiLink.getNsId();

			if( checkIsWikiLink === null ){
				data = {
					title: selection,
					displayText: selection,
					caption: selection //Same as getDisplayText()
				};
			}
			else if( wikiLinkNs === bs.ns.NS_MEDIA || wikiLinkNs === bs.ns.NS_FILE ) {
				data = {
					title: wikiLink.getTitle(),
					displayText: wikiLink.getDisplayText(),
					caption: wikiLink.getCaption(), //Same as getDisplayText()
					nsText: wikiLinkNs === bs.ns.NS_MEDIA ? "media" : "file"
				};
			}
			else {
				bs.util.alert(
					'bs-insertfile-selection-alert',
					{
						textMsg: 'bs-insertfile-error-no-medialink'
					}
				);
				return;
			}
		}

		BS.InsertFile.FileDialog.show( me );
		BS.InsertFile.FileDialog.setData( data );
	});

	return false;
});

$(document).on( 'click', '#bs-editbutton-insertimage', function( e ){
	e.preventDefault();
	var me = this;
	Ext.require('BS.InsertFile.ImageDialog', function(){
		BS.InsertFile.ImageDialog.clearListeners();
		BS.InsertFile.ImageDialog.on( 'cancel', bs.util.selection.reset );
		BS.InsertFile.ImageDialog.on( 'ok',function( dialog, data ) {
			var formattedNamespaces = mw.config.get('wgFormattedNamespaces');
			if( data.nsText == 'media' ) {
				data.nsText = formattedNamespaces[bs.ns.NS_MEDIA];
			} else {
				data.nsText = formattedNamespaces[bs.ns.NS_IMAGE];
			}
			delete( data.imagename ); //Not recognized by wikiText.Link
			delete( data.src );
			if( data.align === 'no-align' )
				delete( data.align );
			var wikiLink = new bs.wikiText.Link( data );
			bs.util.selection.restore( wikiLink.toString() );
			BS.InsertFile.ImageDialog.setData({});
		});

		var data = {};
		var selection = bs.util.selection.save();
		if( selection !== '' ) {
			var wikiLink = new bs.wikiText.Link( selection );
			if( wikiLink.getNsId() !== bs.ns.NS_IMAGE && wikiLink.getNsId() !== bs.ns.NS_MEDIA ) {
				bs.util.alert(
					'bs-insertfile-selection-alert',
					{
						textMsg: 'bs-insertfile-error-no-imagelink'
					}
				);
				return;
			}
			data = wikiLink.getRawProperties();
			data.nsText = wikiLink.getNsId()  === bs.ns.NS_MEDIA ? "media" : "file";
		}
		if( data.align == '' && data.none == false ) {
			data.align = 'no-align';
		}

		BS.InsertFile.ImageDialog.show( me );
		BS.InsertFile.ImageDialog.setData( data );
	});
	return false;
});

// Register buttons with hwactions plugin of VisualEditor
$(document).bind('BsVisualEditorActionsInit', function( event, plugin, buttons, commands, menus ){
	var t = plugin;
	var ed = t.editor;

	//Insert mage
	menus.push({
		menuId: 'bsContextImage',
		menuConfig: {
			text: mw.message('bs-insertfile-button-image-title').plain(),
			icon: 'image',
			cmd : 'mceBsImage'
		}
	});
	buttons.push({
		buttonId: 'bsimage',
		buttonConfig: {
			title : mw.message('bs-insertfile-button-image-title').plain(),
			cmd : 'mceBsImage',
			icon: 'image',
			onPostRender: function() {
				var self = this;

				tinyMCE.activeEditor.on('NodeChange', function(evt) {
					self.disabled(false);
					$(evt.parents).each(function(){
						if ( this.tagName.toLowerCase() == 'pre' ) {
							self.disabled(true);
						}
					});
				});
			}
		}
	});

	//Insert file
	buttons.push({
		buttonId: 'bsfile',
		buttonConfig: {
			title : mw.message('bs-insertfile-button-file-title').plain(),
			cmd : 'mceBsFile',
			onPostRender: function() {
				var self = this;

				tinyMCE.activeEditor.on('NodeChange', function(evt) {
					self.disabled(false);
					$(evt.parents).each(function(){
						if ( this.tagName.toLowerCase() == 'pre' ) {
							self.disabled(true);
						}
					});
				});
			}
		}
	});

	commands.push({
		commandId: 'mceBsImage',
		commandCallback: function() {
			var editor = plugin.getEditor();
			var bookmark = editor.selection.getBookmark();
			var image = this.selection.getNode();
			var params = {
				caption: '',
				alt: ''
			};

			if( image.nodeName.toLowerCase() === 'img' ) {
				var data = bs.util.makeAttributeObject( image );
				params = bs.util.unprefixDataAttributeObject(data);

				// Hint: params.border='0' set by unprefixDataAttributeObject().
				// Value of image attribute "data-bs-border" is stored in variable "border".
				// A few steps later the value of image attribute "border"
				// is stored in variable "border".
				// Now border can be "0" and this is not equal 'false'!
				if ( params.mwborder ) {
					params.border = params.mwborder;
				}
				else {
					params.border = 'false';
				}
			} else if( image.nodeName.toLowerCase() === 'a' ) {
				var data = bs.util.makeAttributeObject( image );
				params = bs.util.unprefixDataAttributeObject(data);
				params.imagename = params.title;
				var href = image.getAttribute( 'href' );
				var prefixedTitle = decodeURIComponent( href.replace( /^bs:\/\//i, '' ) );
				var wikiLink = new bs.wikiText.Link( '[['+prefixedTitle+']]');
				params.nsText = wikiLink.getNsId() === bs.ns.NS_MEDIA ? "media" : "file"
			}

			Ext.require('BS.InsertFile.ImageDialog', function(){
				BS.InsertFile.ImageDialog.clearListeners();
				BS.InsertFile.ImageDialog.on( 'ok', function( sender, data ) {
					editor.focus();
					editor.selection.moveToBookmark(bookmark);
					var imgAttrs = this.plugins.bswikicode.makeDefaultImageAttributesObject();
					var formattedNamespaces = mw.config.get('wgFormattedNamespaces');

					var nsText = formattedNamespaces[bs.ns.NS_IMAGE];
					if( data.nsText == 'media' ) {
						nsText = formattedNamespaces[bs.ns.NS_MEDIA];
					}
					data.imagename = formattedNamespaces[bs.ns.NS_IMAGE]+':'+data.imagename;
					data.mwborder = data.border;
					var classAddition = '';
					var styleAddition = '';
					if( data.sizeheight ) {
						styleAddition += ' height: '+data.sizeheight+'px;';
						imgAttrs['height'] = data.sizeheight;
					}
					if( data.sizewidth ) {
						styleAddition += ' width: '+data.sizewidth+'px;';
						imgAttrs['width'] = data.sizewidth;
					}
					//TODO: This is ugly stuff from "bswikicode". Find better
					//solution in the year 2017.
					if( data.thumb === true || data.frame === true ) {
						imgAttrs['data-bs-width'] = ( imgAttrs['width'] ) ? imgAttrs['width'] : 180; //HARDCODED 180px --> we should use user option!
						classAddition += ' thumb';
						styleAddition += ' border: 1px solid #CCC;'; //HARDCODED 180px
						if ( !data.sizewidth ) {
							styleAddition += ' width: '+imgAttrs['data-bs-width']+'px;';
						}
						//A thumb floats right by default
						if( data.align == 'no-align' ) {
							styleAddition += ' float: right; clear:right; margin-left: 1.4em';
						}
					}
					if( data.align == 'center' || data.center == true ) {
						classAddition += ' center';
						styleAddition += ' display: block; margin-left: auto; margin-right: auto;';
					}
					if( data.align == 'right' || data.right == true ) {
						classAddition += ' tright';
						styleAddition += ' float: right; clear: right; margin-left: 1.4em;';
					}
					if( data.align == 'left' || data.left == true ) {
						classAddition += ' tleft';
						styleAddition += ' float: left; clear: left; margin-right: 1.4em;';
					}
					if( data.align == 'none' ) {
						styleAddition += ' display: block;';
					}
					imgAttrs.src = data.src;
					imgAttrs['class'] += classAddition;
					imgAttrs.style += styleAddition;

					var dataAttrs = bs.util.makeDataAttributeObject( data );
					$.extend(imgAttrs, dataAttrs);
					var newNode = null;
					var newNodeHTML = null;
					var sourceNodeName = image.nodeName.toLowerCase();
					var oldNode = null;
					if( sourceNodeName === 'img' || sourceNodeName === 'a' ) {
						oldNode = image;
					}

					if( nsText === formattedNamespaces[bs.ns.NS_MEDIA] ) {
						var prefixedTitle = nsText + ':' + data.title;
						var anchorAttrs = {
							'title': prefixedTitle,
							'href': prefixedTitle,
							'class': 'internal bs-internal-link',
							'data-bs-type' : 'internal_link'
						};
						newNode = this.dom.create( 'a', anchorAttrs, prefixedTitle );
						newNodeHTML = this.dom.createHTML( 'a', anchorAttrs, data.imagename );
					} else {
						newNode = this.dom.create( 'img', imgAttrs );
						newNodeHTML = this.dom.createHTML( 'img', imgAttrs );
					}

					if( oldNode ) {
						this.dom.replace( newNode, oldNode );
						//Place cursor to end
						this.selection.select( newNode, false );
					} else {
						editor.insertContent( newNodeHTML );
					}

					this.selection.collapse(false);
				}, this);

				BS.InsertFile.ImageDialog.show();
				if( 'caption' in params )
					params.caption = params.caption.replace("@@PIPE@@", "|");
				if( 'alt' in params )
					params.alt = params.alt.replace("@@PIPE@@", "|");
				if( params.align == '' )
					params.align = 'no-align';
				BS.InsertFile.ImageDialog.setData( params );
			}, this);
		}
	});

	commands.push({
		commandId: 'mceBsFile',
		commandCallback: function() {
			var anchor = this.selection.getNode();
			var editor = plugin.getEditor();
			var bookmark = editor.selection.getBookmark();
			var params = {
				caption: this.selection.getContent(),
				displayText: this.selection.getContent()
			};

			if( anchor.nodeName.toLowerCase() === 'a' ) {
				var href = anchor.getAttribute( 'href' ); //With pseudo protocol, e.g. "bs://Media:Somefile.pdf"
				var prefixedTitle = decodeURIComponent( href.replace( /^bs:\/\//i, '' ) );
				var wikiLink = new bs.wikiText.Link( '[['+prefixedTitle+']]');
				params = {
					title: wikiLink.getTitle(),
					displayText: anchor.getAttribute( 'title' ),
					caption:     anchor.getAttribute( 'title' ),
					nsText: wikiLink.getNsId() === bs.ns.NS_MEDIA ? "media" : "file"
				};
			}

			Ext.require('BS.InsertFile.FileDialog', function(){
				BS.InsertFile.FileDialog.clearListeners();
				BS.InsertFile.FileDialog.on( 'ok', function(sender, data) {
					editor.focus();
					editor.selection.moveToBookmark(bookmark);
					var formattedNamespaces = mw.config.get('wgFormattedNamespaces');
					var nsText = formattedNamespaces[bs.ns.NS_MEDIA];
					if( data.nsText == 'file' ) {
						nsText = formattedNamespaces[bs.ns.NS_FILE];
					}
					var prefixedTitle = nsText + ':' + data.title;
					var newAnchor = null;
					var displayText = data.displayText;
					if ( displayText == '' ) displayText = data.title;
					var anchorAttrs = {
						'title': displayText,
						'href': prefixedTitle,
						'class': 'internal bs-internal-link',
						'data-bs-type' : 'internal_link'
					};
					try {
						if( anchor.nodeName.toLowerCase() === 'a' ) {
							newAnchor = this.dom.create( 'a', anchorAttrs, displayText );
							this.dom.replace(newAnchor, anchor);
							//Place cursor to end
							this.selection.select(newAnchor, false);
						}
						else {
							newAnchor = this.dom.createHTML( 'a', anchorAttrs, displayText );
							this.selection.setContent( newAnchor );
						}
						this.selection.collapse(false);
					}
					catch( e ) {
						//Internet Explorer sometimes throws exceptions when
						//TinyMCE operates on a 'Range' object. It doesn't mess
						//up the DOM in any way, but will break script
						//execution if not catched.
					}
				}, this);

				BS.InsertFile.FileDialog.show();
				BS.InsertFile.FileDialog.setData( params );
			}, this);
		}
	});

	//Override default command "mceImage"
	commands.push({
		commandId: 'mceImage',
		commandCallback: function( ui, v ) {
			this.execCommand( 'mceHwImage', ui );
		}
	});

	//Override default command "mceAdvImage"
	commands.push({
		commandId: 'mceAdvImage',
		commandCallback: function( ui, v ) {
			this.execCommand( 'mceHwImage', ui );
		}
	});

	return;

	//This is old code. Not sure if needed for TinyMCE 4
	ed.onNodeChange.add(function(ed, cm, element, c, o) {
		cm.setActive(  'bsimage', element.nodeName == 'IMG');
		cm.setDisabled('bsimage', element.nodeName == 'A');
		if (element.nodeName == 'A') {
			if ( t.elementIsCategoryAnchor( element ) ) {
				cm.setActive( 'bsfile', false);
				cm.setDisabled('bsfile', true);
			} else if ( t.elementIsMediaAnchor( element ) ) {
				cm.setActive( 'bsfile', true);
				cm.setDisabled('bsfile', false);
			} else {
				cm.setDisabled('bsfile', true);
			}
		}
	});
});
