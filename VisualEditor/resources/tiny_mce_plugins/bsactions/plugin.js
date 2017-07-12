/**
 * VisualEditor extension
 *
 * Wiki code to HTML and vice versa parser
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Sebastian Ulbricht
 * @version    2.22.0

 * @package    Bluespice_Extensions
 * @subpackage VisualEditor
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/*global tinymce:true */
/*global mw:true */
/*global BlueSpice:true */

var BsActions = function(editor, url) {
	"use strict";

	var
		_editor,
		_currentImagePath;

	function handleVisibilityState(ctrl, selector) {
		var editor = tinyMCE.activeEditor;
		function bindStateListener() {
			// do not use the visible command, as it messes with bar layot
			//ctrl.visible(state);
			var displayStyle = 'inline-block';
			if ( ctrl.type == 'menuitem' ) {
				displayStyle = 'block';
			}
			var state = editor.dom.getParent(editor.selection.getStart(), selector)
			if ( state ) {
				ctrl.getEl().style.display = displayStyle;
			} else {
				ctrl.getEl().style.display = 'none';
			}
			editor.selection.selectorChanged(selector, function(state) {
				if ( state ) {
					ctrl.getEl().style.display = displayStyle;
				} else {
					ctrl.getEl().style.display = 'none';
				}
			});
		}

		if (editor.initialized) {
			bindStateListener();
		} else {
			editor.on('init', bindStateListener);
		}
	}

	function postRenderVisibilityTable() {
		handleVisibilityState(this, 'table');
	}

	function postRenderMenuItem() {
		var self = this;

		self.parent().on('show', function () {
			var formatName, command;

			formatName = self.settings.format;
			if (formatName) {
				self.disabled(!editor.formatter.canApply(formatName));
				self.active(editor.formatter.match(formatName));
			}

			command = self.settings.cmd;
			if (command) {
				self.active(editor.queryCommandState(command));
			}
		});
	}

	function postRenderSave() {
		var self = this;

		tinyMCE.activeEditor.on('nodeChange', function() {
		    //HW: LV - Save nodeChange state between MW Editor and BSVisualEditor for active save button
		    $(tinyMCE.activeEditor.getElement()).data("text-changed", true);
		    self.disabled(
				tinyMCE.activeEditor.getParam("save_enablewhendirty", true) && !tinyMCE.activeEditor.isDirty()
		    );
		});
		//HW: LV - Save nodeChange state between MW Editor and BSVisualEditor for active save button
		if($(tinyMCE.activeEditor.getElement()).data("text-changed")){
		    tinyMCE.activeEditor.isNotDirty = false;
		}
	}

	function _doSaveArticle() {
		var text, summary, ajaxParams, ajaxUrl;

		text = tinyMCE.activeEditor.getContent({save: true});

		if ( typeof text === 'undefined' || text === '') {
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
			articleId: mw.config.get( "wgArticleId" ),
			username: escape( mw.config.get( "wgUserName" ) ),
			pageName: mw.config.get( "wgPageName" ),
			namespace: mw.config.get( "wgNamespaceNumber" ),
			starttime: $("input[name=wpStarttime]").val(),
			edittime: $("input[name=wpEdittime]").val(),
			editsection: $("input[name=wpSection]").val(),
			text: text,
			summary: summary
		};

		$(document).trigger('BSVisualEditorBeforeArticleSave', [this, ajaxParams]);

		// BSP 2.23.3: Using postWithToken instead of tasks.exec since we want
		// unobtrusive feedback and no popups during editing.
		// Maybe a later version of tasks.exec supports this
		var api = new mw.Api();
		api.postWithToken( 'edit', {
			action: 'bs-visualeditor-tasks',
			task: 'saveArticle',
			taskData: JSON.stringify( ajaxParams )
		})
		.done( function( response ){
			if ( response.success === true ) {
				$(document).trigger( 'BSVisualEditorAfterArticleSave', [this, true, response] );
				$( "input[name=wpEdittime]" ).val( response.edittime );
				$( "input[name=wpStarttime]" ).val( response.starttime );
				mw.notify( response.message );
				$( '#mw-js-message' )
						.html( '<div>' + response.message + '</div>' )
						.show(); //TODO: Use jsMsg() or newer interfaces (message bubbles)
				$( '#mw-js-message' )
						.stop()
						.css( "background-color", "#FFFF9C" )
						.animate( {backgroundColor: "#FCFCFC"}, 1500 );
				$( '#wpSummary' ).val( response.summary );
				$(document).trigger('BSVisualEditorSavedText');
			} else {
				mw.notify( response.message );
				$('#mw-js-message')
						.html('<div>' + response.message + '</div>')
						.show(); //TODO: Use jsMsg() or newer interfaces (message bubbles)
				$('#mw-js-message')
						.stop()
						.css("background-color", "#FFFF9C")
						.animate({backgroundColor: "#FCFCFC"}, 1500);
				$(document).trigger('BSVisualEditorAfterArticleSave', [this, false, response]);
			}
		}).fail( function( response ){
			$(document).trigger('BSVisualEditorAfterArticleSave', [this, false, response]);
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
			author: 'Hallo Welt! GmbH',
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

	/**
	 * When a table is inserted, add a header row.
	 */
	function _addTableHeaderRow( e, selection ) {
		if ( e.content.match( /^<table id="__mce">/ ) ) {
			var row = e.content.match( /<tr>(.*?)<\/tr>/ );
			var headerRow = row[0].replace( /td/gmi, "th" );
			e.content = e.content.replace( /<tbody>/, "<tbody>" + headerRow );
		}
	}

	this.getEditor = function() {
		return _editor;
	};

	this.makeMenuItems = function( itemlist ) {
		itemlist = itemlist || [];
		var menuItems = [];
		for ( var i = 0; i < itemlist.length; i++ ) {
			var item_format = itemlist[i];
			var item_title = item_format.title;
			// if the title contains a -, we assume it's a MW i18n key
			if ( item_title.indexOf( '-' ) !== -1 ) {
				item_title = mw.message( item_title ).plain()
			}
			menuItems.push({
				text: item_title,
				format: item_format.format
			});
		};
		return menuItems;
	}

	this.init = function(ed, url) {
		var buttons, commands, menus;

		_editor = ed;
		_currentImagePath = mw.config.get('wgScriptPath')
			+ '/extensions/BlueSpiceExtensions/VisualEditor/resources/tiny_mce_plugins/bsactions/images';

		var headingsMenuItems = this.makeMenuItems( ed.settings.bs_heading_formats );
		var tableFunctionMenuItems = this.makeMenuItems( ed.settings.bs_table_function_formats );
		var tableStylesMenuItems = this.makeMenuItems( ed.settings.bs_table_formats );

		menus = [];

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
					icon: 'tableinsertrowbefore',
					hidden: true,
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstableaddrowafter',
				buttonConfig: {
					title: 'Insert row after',
					cmd: 'mceTableInsertRowAfter',
					icon: 'tableinsertrowafter',
					hidden: true,
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstabledeleterow',
				buttonConfig: {
					title: 'Delete row',
					cmd: 'mceTableDeleteRow',
					icon: 'tabledeleterow',
					hidden: true,
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstableaddcolumnbefore',
				buttonConfig: {
					title: 'Insert column before',
					cmd: 'mceTableInsertColBefore',
					icon: 'tableinsertcolbefore',
					hidden: true,
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstableaddcolumnafter',
				buttonConfig: {
					title: 'Insert column after',
					cmd: 'mceTableInsertColAfter',
					icon: 'tableinsertcolafter',
					hidden: true,
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstabledeletecolumn',
				buttonConfig: {
					title: 'Delete column',
					cmd: 'mceTableDeleteCol',
					icon: 'tabledeleterow',
					hidden: true,
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstablefunctions', // name to add to toolbar button list
				buttonConfig: {
					title : mw.message( 'bs-visualeditor-bsactions-tablefunctions' ).plain(), // tooltip text seen on mouseover
					image: _currentImagePath + '/hwtablefunctions.png',
					menu : {
						type: 'menu',
						items: tableFunctionMenuItems,
						itemDefaults: {
							preview: true,
							onPostRender: postRenderMenuItem,
							onclick : function() {
								ed.execCommand( 'mceToggleFormat', false, this.settings.format );
							}
						}
					},
					type: 'menubutton',
					hidden: true,
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bstablestyles', // name to add to toolbar button list
				buttonConfig: {
					title : mw.message('bs-visualeditor-bsactions-tablestyles').plain(), // tooltip text seen on mouseover
					image: _currentImagePath + '/hwtablestyles.png',
					menu : {
						type: 'menu',
						items: tableStylesMenuItems,
						itemDefaults: {
							preview: true,
							onPostRender: postRenderMenuItem,
							onclick : function() {
								// Table styles are mutually exclusive. So we remove all table styles first
								for ( var i = 0; i < ed.settings.bs_table_formats.length; i++ ) {
									var table_format = ed.settings.bs_table_formats[i];
									ed.formatter.remove( table_format.format );
								};
								ed.execCommand( 'mceToggleFormat', false, this.settings.format );
							}
						},
					},
					type: 'menubutton',
					hidden: true,
					onPostRender: postRenderVisibilityTable
				}
			}, {
				buttonId: 'bsheadings', // name to add to toolbar button list
				buttonConfig: {
					title : mw.message('bs-visualeditor-bsactions-headings').plain(), // tooltip text seen on mouseover
					text : mw.message('bs-visualeditor-bsactions-headings').plain(),
					menu : {
						type: 'menu',
						items: headingsMenuItems,
						itemDefaults: {
							preview: true,
							textStyle: function () {
								if ( this.settings.format ) {
									return editor.formatter.getCssText( this.settings.format );
								}
							},
							onPostRender: postRenderMenuItem,
							onclick : function() {
								ed.execCommand( 'FormatBlock', false, this.settings.format );
							}
						}
					},
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
			button.buttonConfig.classes = button.buttonConfig.classes || '';
			button.buttonConfig.classes += " widget btn " + button.buttonId;
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

		ed.on( 'BeforeSetContent', _addTableHeaderRow );
	};
};

tinymce.PluginManager.add('bsactions', BsActions);
//tinymce.PluginManager.requireLangPack('bsactions'); //Seems not to work
