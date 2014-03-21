/**
 * VisualEditor extension
 * 
 * Wiki code to HTML and vice versa parser
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbricht
 * @version    2.22.0
 
 * @package    Bluespice_Extensions
 * @subpackage VisualEditor
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/*global tinymce:true */
/*global mw:true */
/*global BlueSpice:true */

var BsActions = function() {
	"use strict";

	var
		_editor,
		_currentImagePath;

	function handleDisabledState(ctrl, selector) {
		var editor = tinyMCE.activeEditor;
		function bindStateListener() {
			ctrl.disabled(!editor.dom.getParent(editor.selection.getStart(), selector));

			editor.selection.selectorChanged(selector, function(state) {
				ctrl.disabled(!state);
			});
		}

		if (editor.initialized) {
			bindStateListener();
		} else {
			editor.on('init', bindStateListener);
		}
	}
	
	function handleVisibilityState(ctrl, selector) {
		var editor = tinyMCE.activeEditor;
		function bindStateListener() {
			ctrl.visible(editor.dom.getParent(editor.selection.getStart(), selector));

			editor.selection.selectorChanged(selector, function(state) {
				ctrl.visible(state);
			});
		}

		if (editor.initialized) {
			bindStateListener();
		} else {
			editor.on('init', bindStateListener);
		}
	}

	function postRender() {
		/*jshint validthis:true*/
		handleDisabledState(this, 'table');
	}
	
	function postRenderVisibilityTable() {
		handleVisibilityState(this, 'TABLE');
	}
	
	function postRenderSave() {
		var self = this;

		tinyMCE.activeEditor.on('nodeChange', function() {
			self.disabled(tinyMCE.activeEditor.getParam("save_enablewhendirty", true) && !tinyMCE.activeEditor.isDirty());
		});
	}

	function postRenderCell() {
		/*jshint validthis:true*/
		handleDisabledState(this, 'td,th');
	}

	function _doSaveArticle() {
		var text, summary, ajaxParams, ajaxUrl;

		text = tinyMCE.activeEditor.getContent({save: true});

		if (text === '') {
			return; // @todo Nothing to save. Disable button instead.
		}

		summary = $('#wpSummary').val();
		if (summary === '') {
			summary = 'false';
		}
		if (summary === null) {
			return; //User has clicked "cancel"
		}

		ajaxParams = {
			articleId: wgArticleId,
			username: escape(wgUserName),
			pageName: wgPageName,
			namespace: wgNamespaceNumber,
			starttime: $("input[name=wpStarttime]").val(),
			edittime: $("input[name=wpEdittime]").val(),
			editsection: $("input[name=wpSection]").val(),
			text: text,
			summary: summary
		};

		ajaxUrl = bs.util.getAjaxDispatcherUrl('VisualEditor::doSaveArticle');

		$(document).trigger('BSVisualEditorBeforeArticleSave', [this, ajaxParams, ajaxUrl]);
		Ext.Ajax.request({
			method: 'post',
			params: ajaxParams,
			url: ajaxUrl,
			success: function(response, opts) {
				$(document).trigger('BSVisualEditorAfterArticleSave', [this, true, response, opts]);
				var json = Ext.decode(response.responseText);
				$("input[name=wpEdittime]").val(json.edittime);
				$("input[name=wpStarttime]").val(json.starttime);
				mw.notify( json.message );
				$('#mw-js-message').html('<div>' + json.message + '</div>').show(); //TODO: Use jsMsg() or newer interfaces (message bubbles)
				$('#mw-js-message').stop().css("background-color", "#FFFF9C").animate({backgroundColor: "#FCFCFC"}, 1500);
				$('#wpSummary').val(json.summary);
				$(document).trigger('BSVisualEditorSavedText');
			},
			failure: function(response, opts) {
				$(document).trigger('BSVisualEditorAfterArticleSave', [this, false, response, opts]);
				//TODO: handle error.
			},
			scope: this
		});
	}

	/**
	 * Enable / disable controls according to selection / cursor context
	 * ed: editor
	 * cm: commands
	 * e: current element
	 * c: selection collapsed?
	 * o: dom object
	 */
	function _onNodeChange(ed, cm, e, c, o) {
		cm.setDisabled('hwsignature', e.nodeName == 'A'); //No signature if cursor on an anchor tag

		if (ed.getParam('fullscreen_is_enabled')) {
			cm.setDisabled('hwwiki', true);
		}

		var selectedInnerHTML = ed.selection.getContent();
		if (selectedInnerHTML.match(/<[^a].*?>/gmi)) {
			cm.setDisabled('unlink', true);
			// this is bold but helps. Otherwise, disabling unlink has no real effect
			return false;
		}

	}

	this.elementIsCategoryAnchor = function(element) {
		if (element.nodeName !== 'A') {
			return false;
		}
		var href = unescape(element.getAttribute('href').toLowerCase());
		//TODO: i18n || mw client framework
		return (href.indexOf('kategorie:') != -1) || (href.indexOf('category:') != -1)
	};

	this.elementIsMediaAnchor = function(element) {
		if (element.nodeName != 'A') {
			return false;
		}
		var href = unescape(element.getAttribute('href').toLowerCase());
		//TODO: i18n || mw client framework
		return (href.indexOf('medium:') != -1) || (href.indexOf('media:') != -1)
	};

	this.getInfo = function() {
		var info = {
			longname: 'BlueSpice Edit Actions',
			author: 'Hallo Welt! - Medienwerkstatt GmbH',
			authorurl: 'http://www.hallowelt.biz',
			infourl: 'http://www.hallowelt.biz'
		};
		return info;
	};
	
	/**
	 * There is a incompatibility between fullscreen and autoresize plugin. So after changing
	 * to fullscreen, we need to restore the scrollbar
	 * @param {type} state
	 * @returns {undefined}
	 */
	function _addFullScreenScrollbar( state ) {
		_editor.getDoc().documentElement.style.overflowY = "auto";
	}

	this.getEditor = function() {
		return _editor;
	};

	this.init = function(ed, url) {
		var buttons, commands, menus;

		_editor = ed;
		_currentImagePath = mw.config.get('wgScriptPath')
			+ '/extensions/BlueSpiceExtensions/VisualEditor/resources/tiny_mce_plugins/bsactions/images';

		var headingsMenuItems = [];

		headingsMenuItems.push({
			text: mw.message('bs-visualeditor-bsactions-paragraph').plain(),
			onclick : function() { ed.execCommand('FormatBlock', false, 'p'); }
		});

		headingsMenuItems.push({
			text: mw.message('bs-visualeditor-bsactions-heading2').plain(),
			onclick : function() { ed.execCommand('FormatBlock', false, 'h2'); }
		});

		headingsMenuItems.push({
			text: mw.message('bs-visualeditor-bsactions-heading3').plain(),
			onclick : function() { ed.execCommand('FormatBlock', false, 'h3'); }
		});

		headingsMenuItems.push({
			text: mw.message('bs-visualeditor-bsactions-heading4').plain(),
			onclick : function() { ed.execCommand('FormatBlock', false, 'h4'); }
		});

		headingsMenuItems.push({
			text: mw.message('bs-visualeditor-bsactions-heading5').plain(),
			onclick : function() { ed.execCommand('FormatBlock', false, 'h5'); }
		});

		headingsMenuItems.push({
			text: mw.message('bs-visualeditor-bsactions-heading6').plain(),
			onclick : function() { ed.execCommand('FormatBlock', false, 'h6'); }
		});
		
		menus = [{
				menuId: 'bstableprops',
				menuConfig: {
					text: 'Table properties',
					context: 'table',
					onPostRender:  postRenderVisibilityTable,
					cmd: 'mceInsertTable'
				}
			}, {
				menuId: 'bsdeletetable',
				menuConfig: {
					text: 'Delete table',
					context: 'table',
					onPostRender:  postRenderVisibilityTable,
					cmd: 'mceTableDelete'
				}
			}, {
				menuId: 'bscell',
				menuItem: ed.menuItems['cell'],
				menuOnPostRender: postRenderVisibilityTable
			}, {
				menuId: 'bsrow',
				menuItem: ed.menuItems['row'],
				menuOnPostRender: postRenderVisibilityTable
			}, {
				menuId: 'bscolumn',
				menuItem: ed.menuItems['column'],
				menuOnPostRender: postRenderVisibilityTable
			}];
	
		//HINT: TinyMCE I18N seems not wo work. Using MediaWiki I18N
		buttons = [{
				buttonId: 'bssave',
				buttonConfig: {
					title: mw.message('bs-visualeditor-bsactions-save').plain(), //'bsactions.switchgui',
					cmd: 'mceBsSave',
					role: 'save',
					icon: 'save',
					disabled: true,
					onPostRender: postRenderSave
				}
			}, {
				buttonId: 'bsswitch',
				buttonConfig: {
					title: mw.message('bs-visualeditor-bsactions-switchgui').plain(), //'bsactions.switchgui',
					cmd: 'mceBsSwitch',
					role: 'switchgui',
					image: _currentImagePath + '/hwswitch.gif'
				}
			}, {
				buttonId: 'bssignature',
				buttonConfig: {
					title: mw.message('bs-visualeditor-bsactions-signature').plain(), //'bsactions.signature',
					cmd: 'mceBsSignature',
					role: 'signature',
					image: _currentImagePath + '/hwsignature.gif'
				}
			}, {
				buttonId: 'bswiki',
				buttonConfig: {
					title: mw.message('bs-visualeditor-bsactions-wiki').plain(), //'bsactions.wiki',
					//cmd: 'mceBsWiki',
					//we don't use tinymce cmd here
					//as the result would be some ugly js-errors
					//due to the fact, that tinymce isn't that much happy about
					//beeing destroyed by it's own cmds
					onClick: function(){
						// unbind resize event .-- did not help
						// ed.off("change setcontent paste keyup", ed.plugins.autoresize.resize);
						VisualEditor.toggleEditor();
						return true;
					},
					role: 'editor_switcher',
					image: _currentImagePath + '/hwwiki.gif'
				}
			}, {
				buttonId: 'bslinebreak',
				buttonConfig: {
					title: mw.message('bs-visualeditor-bsactions-linebreak').plain(), //'bsactions.linebreak',
					cmd: 'mceBsLinebreak',
					role: 'linebreak',
					image: _currentImagePath + '/hwlinebreak.gif'
				}
			}, {
				buttonId: 'bstableaddrowbefore',
				buttonConfig: {
					title: 'Insert row before',
					cmd: 'mceTableInsertRowBefore',
					image: _currentImagePath + '/hwtableinsertrowbefore.gif',
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstableaddrowafter',
				buttonConfig: {
					title: 'Insert row after',
					cmd: 'mceTableInsertRowAfter',
					image: _currentImagePath + '/hwtableinsertrowafter.gif',
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstabledeleterow',
				buttonConfig: {
					title: 'Delete row',
					cmd: 'mceTableDeleteRow',
					image: _currentImagePath + '/hwtabledeleterow.gif',
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstableaddcolumnbefore',
				buttonConfig: {
					title: 'Insert column before',
					cmd: 'mceTableInsertColBefore',
					image: _currentImagePath + '/hwtableinsertcolumnbefore.gif',
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstableaddcolumnafter',
				buttonConfig: {
					title: 'Insert column after',
					cmd: 'mceTableInsertColAfter',
					image: _currentImagePath + '/hwtableinsertcolumnafter.gif',
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstabledeletecolumn',
				buttonConfig: {
					title: 'Delete column',
					cmd: 'mceTableDeleteCol',
					image: _currentImagePath + '/hwtabledeletecolumn.gif',
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bsheadings', // name to add to toolbar button list
				buttonConfig: {
					title : mw.message('bs-visualeditor-bsactions-headings').plain(), // tooltip text seen on mouseover
					text : mw.message('bs-visualeditor-bsactions-headings').plain(),
					menu : headingsMenuItems,
					type: 'menubutton'
				}
			}];

		commands = [{
				commandId: 'mceBsSave',
				commandCallback: function() {
					_doSaveArticle();
				}
			}, {
				commandId: 'mceBsSwitch',
				commandCallback: function() {
					VisualEditor.toggleGui();
				} // Toggle full / restricted version of editor
			}, {
				commandId: 'mceBsSignature',
				commandCallback: function() {
					_editor.selection.setContent('--~~~~');
				} //Inserts a signature
			}, {
				commandId: 'mceBsLinebreak',
				commandCallback: function() {
					_editor.selection.getBookmark();
					//only insert if selection is collapsed
					if ( _editor.selection.isCollapsed() ) {
						var node =  _editor.dom.create( 'br' );
						_editor.dom.insertAfter(node, _editor.selection.getNode());
						//Place cursor to end
						_editor.selection.select(node, false);
						_editor.selection.collapse(false);
					}
				}	
			}];

		//Give other extensions the chance to alter buttons and commands
		$(document).trigger('BsVisualEditorActionsInit', [this, buttons, commands, menus]);

		// Register buttons
		for (var i = 0; i < buttons.length; i++) {
			var button = buttons[i];
			ed.addButton(button.buttonId, button.buttonConfig);
		}

		// Register commands
		for (var j = 0; j < commands.length; j++) {
			var command = commands[j];
			ed.addCommand(command.commandId, command.commandCallback);
		}

		// Register menus
		for (var j = 0; j < menus.length; j++) {
			var menu = menus[j];
			if ( menu.menuItem ) {
				menu.menuItem.onPostRender = menu.menuOnPostRender;
				ed.addMenuItem(menu.menuId, menu.menuItem);
			} else {
				ed.addMenuItem(menu.menuId, menu.menuConfig);
			}
		}
		//ed.on('NodeChange', _onNodeChange);
		ed.on( 'FullscreenStateChanged', _addFullScreenScrollbar );
	};
};

tinymce.PluginManager.add('bsactions', BsActions);
//tinymce.PluginManager.requireLangPack('bsactions'); //Seems not to work