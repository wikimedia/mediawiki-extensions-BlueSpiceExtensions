/**
 * VisualEditor extension
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    1.2.0
 * @version    $Id: editor_plugin.js 8384 2013-01-31 09:32:18Z rvogel $
 * @package    Bluespice_Extensions
 * @subpackage VisualEditor
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.2.0
 * - Added loose coupling mechanism for button registration
 * v1.1.4
 * - fixed bug where heading would disappear on enter
 * v1.1.3
 * - enable / disable controls according to selection status
 * v1.1.2
 * - fixed bug when an element has no parent
 * v1.1.1
 * - allow tags and wikicode within comments
 * v1.1.0
 * - proper i18n
 * - improved behaviour for enter at beginning of headings
 * - improved behaviour for enter at readonly spans
 */
(function() {
	// This does not work with old editors. Maybe the version could be asked before
	tinymce.PluginManager.requireLangPack('hwactions');
	tinymce.create('tinymce.plugins.Hwactions', {
		init : function(ed, url) {
			var t = this;
			t.editor = ed;
			
			var currentImagePath = wgScriptPath+'/extensions/BlueSpiceExtensions/VisualEditor/jscripts/tiny_mce_plugins/hwactions/images';
			
			var buttons = [
				{
					buttonId: 'hwswitch',
					buttonConfig: {
						title : 'hwactions.switchgui',
						cmd : 'mceHwSwitch',
						image : currentImagePath+'/hwswitch.gif'
					}
				},
				{
					buttonId: 'hwsignature',
					buttonConfig: {
						title : 'hwactions.signature',
						cmd : 'mceHwSignature',
						image : currentImagePath+'/hwsignature.gif'
					}
				},
				{
					buttonId: 'hwwiki',
					buttonConfig: {
						title : 'hwactions.wiki',
						cmd : 'mceHwWiki',
						image : currentImagePath+'/hwwiki.gif'
					}
				},
				{
					buttonId: 'hwlinebreak',
					buttonConfig: {
						title : 'hwactions.linebreak',
						cmd : 'mceHwLinebreak',
						image : currentImagePath+'/hwlinebreak.gif'
					}
				}
			];

			var commands = [
				{
					commandId: 'mceSave',
					commandCallback: function() { t.doSaveArticle(); }
				},
				{
					commandId: 'mceHwSwitch',
					commandCallback: function() { toggleGuiMode(); } // Toggle full / restricted version of editor
				},
				{
					commandId: 'mceHwSignature',
					commandCallback: function() { 
						t.editor.selection.setContent('--~~~~');
					} //Inserts a signature
				},
				{
					commandId: 'mceHwWiki',
					commandCallback: function() { toggleEditorMode(ed.id); } //Toggles the editor
				},
				{
					commandId: 'mceHwLinebreak',
					commandCallback: function() {
						t.editor.selection.setContent('<br />');
					} //Inserts a Linebreak
				}
			];

			//Give other extensions the chance to alter buttons and commands
			$(document).trigger( 'hwactions-init', [t, buttons, commands] );
			//TODO: Decouple ContextMenu 
			//HINT: http://stackoverflow.com/questions/6585326/tinymce-add-item-to-context-menu
			// --> Create own hwcontextmenu-plugin
			// --> Fire global event hwcontextmenu-show when rendering the menu
			// --> Allow extenions to register menu entry items like the table-plugin does now with current contextmenu-plugin

			// Register buttons
			for( var i = 0; i < buttons.length; i++ ) {
				var button = buttons[i];
				ed.addButton(
					button.buttonId,
					button.buttonConfig
				);
			}
			
			// Register commands
			for( var j = 0; j < commands.length; j++ ) {
				var command = commands[j];
				ed.addCommand(
					command.commandId,
					command.commandCallback
				);
			}

			/**
			* Enable / disable controls according to selection / cursor context
			* ed: editor
			* cm: commands
			* e: current element
			* c: selection collapsed?
			* o: dom object
			*/
			ed.onNodeChange.add(function(ed, cm, e, c, o) {
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
				
			});
		},
		
		elementIsCategoryAnchor: function ( element ) {
			if( element.nodeName != 'A' ) return false;
			var href = unescape( element.getAttribute('href').toLowerCase() );
			//TODO: i18n || mw client framework
			return (href.indexOf('kategorie:') != -1) || (href.indexOf('category:') != -1)
		},
		
		elementIsMediaAnchor: function ( element ) {
			if( element.nodeName != 'A' ) return false;
			var href = unescape( element.getAttribute('href').toLowerCase() );
			//TODO: i18n || mw client framework
			return (href.indexOf('medium:') != -1) || (href.indexOf('media:') != -1)
		},

		getInfo : function() {
			return {
				longname  : 'BlueSpice Edit Dialogs',
				author    : 'Hallo Welt! - Medienwerkstatt GmbH',
				authorurl : 'http://www.hallowelt.biz',
				infourl   : 'http://www.hallowelt.biz',
				version   : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},
		
		doSaveArticle : function() {
			var text = '';
			if ( VisualEditorMode ) {
				text = tinyMCE.activeEditor.getContent( {save : true} );
			} else {
				alert( 'Only in visual mode!' ); //TODO: Neccessary? RE: not yet! ;)
			}
			if( text == '' ) return; //TODO: Nothing to save. Disable button instead.
			
			var summary = $('#wpSummary').val();
			if (summary == '' ) {
				summary = 'false';
			}
			if( summary == null ) return; //User has clicked "cancel"
			
			var ajaxParams = {
				articleId: wgArticleId,
				username: escape( wgUserName ),
				pageName: wgPageName,
				namespace: wgNamespaceNumber,
				starttime: $("input[name=wpStarttime]").val(),
				edittime: $("input[name=wpEdittime]").val(),
				editsection: $("input[name=wpSection]").val(),
				text: text,
				summary: summary
			}
			var ajaxUrl = BlueSpice.buildRemoteString('VisualEditor', 'doSaveArticle');
			
			$(document).trigger( 'BSVisualEditorBeforeArticleSave', [ this, ajaxParams, ajaxUrl ] );
			Ext.Ajax.request({
				method: 'post',
				params: ajaxParams,
				url: ajaxUrl,
				success: function(response, opts) {
					$(document).trigger( 'BSVisualEditorAfterArticleSave', [ this, true, response, opts ] );
					var json = Ext.decode(response.responseText);
					$("input[name=wpEdittime]").val(  json.edittime );
					$("input[name=wpStarttime]").val( json.starttime );
					$('#mw-js-message').html('<div>'+json.message+'</div>').show(); //TODO: Use jsMsg() or newer interfaces (message bubbles)
					$('#mw-js-message').stop().css("background-color", "#FFFF9C").animate({ backgroundColor: "#FCFCFC"}, 1500);
					$('#wpSummary').val( json.summary );
					$(document).trigger('BSVisualEditorSavedText');
				},
				failure: function( response, opts ) {
					$(document).trigger( 'BSVisualEditorAfterArticleSave', [ this, false, response, opts ] );
					//TODO: handle error.
				},
				scope: this
			});
		}

	// Private methods
	});

	// Register plugin
	tinymce.PluginManager.add('hwactions', tinymce.plugins.Hwactions);
})();