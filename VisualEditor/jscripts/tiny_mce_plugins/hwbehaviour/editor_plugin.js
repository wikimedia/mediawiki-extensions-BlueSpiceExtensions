/**
 * VisualEditor extension
 * 
 * Changes some behavioral peculiarities of tinymce
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    1.2.0
 * @version    $Id: editor_plugin.js 7838 2012-12-20 08:41:05Z rvogel $
 * @package    Bluespice_Extensions
 * @subpackage VisualEditor
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.2.0
 * - Fixed info
 */

(function() {
	var Event = tinymce.dom.Event;

	tinymce.create('tinymce.plugins.HwBehaviour', {
		init : function(ed, url) {
			var t = this, editClass, nonEditClass;

			t.editor = ed;
			editClass = ed.getParam("noneditable_editable_class", "mceEditable"); // Currently unused
			nonEditClass = ed.getParam("noneditable_noneditable_class", "mceNonEditable");
			
			$(document).trigger( 'hwbehavior-init', [ed, t, editClass, nonEditClass] );

			/**
			 * Allow only Cursor and Return if class is noneditable_editable_class
			 * @param TinyMCE ed Reference to current editor
			 * @param string cm Command. Not used.
			 * @param DOMNode n DOM node that was changed.
			 */
			ed.onNodeChange.addToTop(function(ed, cm, n) {
				var sc, ec;

				// Block if start or end is inside a non editable element
				sc = ed.dom.getParent(ed.selection.getStart(), function(n) {
					return ed.dom.hasClass(n, nonEditClass);
				});

				ec = ed.dom.getParent(ed.selection.getEnd(), function(n) {
					return ed.dom.hasClass(n, nonEditClass);
				});

				// Block or unblock
				if (sc || ec) {
					t._setDisabled(1, n);
					return false;
				} else
					t._setDisabled(0, n);
			});
			
			/**
			 * Changes behavior on enter at the end of headlines
			 * @param TinyMCE ed Reference to current editor
			 * @param Event e Current KeyPress event
			 * @return bool Process keystroke
			 */
			ed.onKeyPress.add(function(ed, e) {
				node = ed.selection.getNode();
				var parent = ed.dom.getParent(node, "span,h1,h2,h3,h4,h5,h6");
				if (!parent) return true;

				switch(e.keyCode) {
					case 13:
						switch(parent.nodeName.toLowerCase()) {
							case 'span':
								if (!ed.dom.hasClass(parent, "mceNonEditable")) break;
								bm = ed.selection.getBookmark();
								ed.selection.select(ed.dom.getParent(parent),true);
								bm1 = ed.selection.getBookmark();
								ed.selection.moveToBookmark(bm);
								if (bm.start == bm1.start)
								{
									ed.dom.insertAfter(parent.cloneNode(true), parent);
									newp = ed.dom.create('p', {}, '<br _moz_dirty="">');
									ed.dom.replace(newp, parent);
									ed.selection.select(newp, true);
									ed.selection.collapse(true);
									return Event.cancel(e);
								}
								else
								{
									newp = ed.dom.create('p', {id:'inserted'}, '<br _moz_dirty="">');
									ed.dom.insertAfter(newp, ed.dom.getParent(parent));
									ed.selection.select(newp,true);
									ed.selection.collapse(true);
									return Event.cancel(e);
								}
								break;

						}
						break;
				}
			});
			
			/**
			 * Makes sure doubleclick does not select additional spaces
			 * @param TinyMCE ed Reference to current editor
			 * @param Event e Current KeyPress event
			 */
			ed.onDblClick.add(function(ed, e) {
				if (document.getSelection) {
					range = ed.selection.getRng();
					hwcontent = String(range);
					firstspace = hwcontent.search(/[\s\u00A0\u48ef\u0020]*$/gi);
					selectionObject = ed.selection.getSel();
					range.setEnd(selectionObject.focusNode, selectionObject.focusOffset+(firstspace-(hwcontent.length)));
					ed.selection.setRng(range);
				}
				else if (document.selection) {
					// IE code
				}
			});
		},

		getInfo : function() {
			return {
				longname  : 'BlueSpice Edit Behaviour',
				author    : 'Hallo Welt! - Medienwerkstatt GmbH',
				authorurl : 'http://www.hallowelt.biz',
				infourl   : 'http://www.hallowelt.biz',
				version   : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		_block : function(ed, e) {
			var k = e.keyCode;

			// Don't block arrow keys, pg up/down, and F1-F12
			if ((k > 32 && k < 41) || (k > 111 && k < 124))
				return;
			//if (k == 13) return; //This would make special tags break

			Event.cancel(e);
			return;
		},

		_setDisabled : function(s,n) {
			var t = this, ed = t.editor;
			var leaveEnabledIds = [ ed.editorId+'_'+'hwwiki' ]; //Do not disable switch to wikicode; ed.editorId+'_'+c.id = i.e. wpTextbox1_hwwiki
			
			$(document).trigger( 'hwbehavior-_setDisabled', [t, s, leaveEnabledIds, n] );

			tinymce.each(ed.controlManager.controls, function(c) {
				if ( $.inArray(c.id, leaveEnabledIds) == -1 ) {
					c.setDisabled(s);
				}
			});

			if (s !== t.disabled) {
				if (s) {
					ed.onKeyDown.addToTop(t._block);
					ed.onKeyPress.addToTop(t._block);
					ed.onKeyUp.addToTop(t._block);
					ed.onPaste.addToTop(t._block);
				} else {
					ed.onKeyDown.remove(t._block);
					ed.onKeyPress.remove(t._block);
					ed.onKeyUp.remove(t._block);
					ed.onPaste.remove(t._block);
				}

				t.disabled = s;
			}
		}
	});
	// Register plugin
	tinymce.PluginManager.add('hwbehaviour', tinymce.plugins.HwBehaviour);
})();