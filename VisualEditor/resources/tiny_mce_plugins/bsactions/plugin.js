/**
 * VisualEditor extension
 * 
 * Wiki code to HTML and vice versa parser
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbricht
 * @version    1.22.0
 
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

	function postRender() {
		/*jshint validthis:true*/
		handleDisabledState(this, 'table');
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
	}

	this.elementIsMediaAnchor = function(element) {
		if (element.nodeName != 'A') {
			return false;
		}
		var href = unescape(element.getAttribute('href').toLowerCase());
		//TODO: i18n || mw client framework
		return (href.indexOf('medium:') != -1) || (href.indexOf('media:') != -1)
	}

	this.getInfo = function() {
		var info = {
			longname: 'BlueSpice Edit Dialogs',
			author: 'Hallo Welt! - Medienwerkstatt GmbH',
			authorurl: 'http://www.hallowelt.biz',
			infourl: 'http://www.hallowelt.biz'
		};
		return info;
	};

	this.getEditor = function() {
		return _editor;
	}

	this.init = function(ed, url) {
		var buttons, commands;

		_editor = ed;
		_currentImagePath = mw.config.get('wgScriptPath')
			+ '/extensions/BlueSpiceExtensions/VisualEditor/resources/tiny_mce_plugins/bsactions/images';

		buttons = [{
				buttonId: 'bsswitch',
				buttonConfig: {
					title: 'bsactions.switchgui',
					cmd: 'mceBsSwitch',
					image: _currentImagePath + '/hwswitch.gif'
				}
			}, {
				buttonId: 'bssignature',
				buttonConfig: {
					title: 'bsactions.signature',
					cmd: 'mceBsSignature',
					image: _currentImagePath + '/hwsignature.gif'
				}
			}, {
				buttonId: 'bswiki',
				buttonConfig: {
					title: 'bsactions.wiki',
					cmd: 'mceBsWiki',
					role: 'editor_switcher',
					image: _currentImagePath + '/hwwiki.gif'
				}
			}, {
				buttonId: 'bslinebreak',
				buttonConfig: {
					title: 'bsactions.linebreak',
					cmd: 'mceBsLinebreak',
					image: _currentImagePath + '/hwlinebreak.gif'
				}
			}, {
				buttonId: 'bstableaddrowbefore',
				buttonConfig: {
					title: 'bsactions.tableaddrowbefore',
					cmd: 'mceTableInsertRowBefore',
					image: _currentImagePath + '/hwtableinsertrowbefore.gif'
				}
			}, {
				buttonId: 'bstableaddrowafter',
				buttonConfig: {
					title: 'bsactions.tableaddrowafter',
					cmd: 'mceTableInsertRowAfter',
					image: _currentImagePath + '/hwtableinsertrowafter.gif'
				}
			}, {
				buttonId: 'bstabledeleterow',
				buttonConfig: {
					title: 'bsactions.tabledeleterow',
					cmd: 'mceTableDeleteRow',
					image: _currentImagePath + '/hwtabledeleterow.gif'
				}
			}];

		commands = [{
				commandId: 'mceSave',
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
				commandId: 'mceBsWiki',
				commandCallback: function() {
					VisualEditor.toggleEditor();
				} //Toggles the editor
			}, {
				commandId: 'mceBsLinebreak',
				commandCallback: function() {
					_editor.selection.setContent('<br />');
				} //Inserts a Linebreak
			}];

		//Give other extensions the chance to alter buttons and commands
		$(document).trigger('BsVisualEditorActionsInit', [this, buttons, commands]);

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

		//ed.on('NodeChange', _onNodeChange);
	};
};

tinymce.PluginManager.add('bsactions', BsActions);