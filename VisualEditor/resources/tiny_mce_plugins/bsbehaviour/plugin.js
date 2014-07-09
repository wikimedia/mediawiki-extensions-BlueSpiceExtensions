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

var BsBehaviour = function() {
	"use strict";

	var
		_editor,
		_event = tinymce.dom.Event,
		_disabled;


	this.getInfo = function() {
		var info = {
			longname: 'BlueSpice Edit Behaviour',
			author: 'Hallo Welt! - Medienwerkstatt GmbH',
			authorurl: 'http://www.hallowelt.biz',
			infourl: 'http://www.hallowelt.biz'
		};
		return info;
	};

	function _block(ed, e) {
		var k = e.keyCode;

		// Don't block arrow keys, pg up/down, and F1-F12
		if ((k > 32 && k < 41) || (k > 111 && k < 124)) {
			return;
		}

		_event.cancel(e);
		return;
	}

	function _setDisabled(s) {
		var ed = _editor,
			leaveEnabledIds = [ed.editorId + '_' + 'hwwiki']; //Do not disable switch to wikicode; ed.editorId+'_'+c.id = i.e. wpTextbox1_hwwiki

		$(document).trigger('hwbehavior-_setDisabled', [this, s, leaveEnabledIds]);
//		console.log(ed.menuItems);
		tinymce.each(ed.menuItems, function(button, id, buttonCollection) {
			if($.inArray(id, leaveEnabledIds) === -1) {
				button.disabled(true);
			}
		});

		if (s !== _disabled) {
			if (s) {
				ed.onKeyDown.addToTop(_block);
				ed.onKeyPress.addToTop(_block);
				ed.onKeyUp.addToTop(_block);
				ed.onPaste.addToTop(_block);
			} else {
				ed.onKeyDown.remove(_block);
				ed.onKeyPress.remove(_block);
				ed.onKeyUp.remove(_block);
				ed.onPaste.remove(_block);
			}

			_disabled = s;
		}
	}

	this.init = function(ed, url) {
		var editClass, nonEditClass;

		_editor = ed;
		editClass = ed.getParam("noneditable_editable_class", "mceEditable"); // Currently unused
		nonEditClass = ed.getParam("noneditable_noneditable_class", "mceNonEditable");

		$(document).trigger('hwbehavior-init', [ed, this, editClass, nonEditClass]);

		/**
		 * Allow only Cursor and Return if class is noneditable_editable_class
		 * @param TinyMCE ed Reference to current editor
		 * @param string cm Command. Not used.
		 * @param DOMNode n DOM node that was changed.
		 */
		ed.on('NodeChange', function(evt) {
			var sc, ec, ed;
			ed = tinymce.activeEditor;
			if(typeof(ed) === 'undefined') {
				return;
			}

			// Block if start or end is inside a non editable element
			sc = ed.dom.getParent(ed.selection.getStart(), function(n) {
				return ed.dom.hasClass(n, nonEditClass);
			});

			ec = ed.dom.getParent(ed.selection.getEnd(), function(n) {
				return ed.dom.hasClass(n, nonEditClass);
			});

			// Block or unblock
			/*if (sc || ec) {
				_setDisabled(1);
				return false;
			} else {
				_setDisabled(0);
			}*/
		});

		/**
		 * Changes behavior on enter at the end of headlines
		 * @param TinyMCE ed Reference to current editor
		 * @param Event e Current KeyPress event
		 * @return bool Process keystroke
		 */
		ed.on('KeyPress', function(e) {
			var parent, bm, bm1, newp;
			ed = tinymce.activeEditor;
			var node = ed.selection.getNode();
			parent = ed.dom.getParent(node, "span,h1,h2,h3,h4,h5,h6");

			if (!parent) {
				return true;
			}

			switch (e.keyCode) {
				case 13:
					switch (parent.nodeName.toLowerCase()) {
						case 'span':
							if (!ed.dom.hasClass(parent, "mceNonEditable")) {
								break;
							}
							bm = ed.selection.getBookmark();
							ed.selection.select(ed.dom.getParent(parent), true);

							bm1 = ed.selection.getBookmark();
							ed.selection.moveToBookmark(bm);

							if (bm.start === bm1.start) {
								ed.dom.insertAfter(parent.cloneNode(true), parent);
								newp = ed.dom.create('p', {}, '<br _moz_dirty="">');
								ed.dom.replace(newp, parent);
								ed.selection.select(newp, true);
								ed.selection.collapse(true);
								return _event.cancel(e);
							}
							else
							{
								newp = ed.dom.create('p', {id: 'inserted'}, '<br _moz_dirty="">');
								ed.dom.insertAfter(newp, ed.dom.getParent(parent));
								ed.selection.select(newp, true);
								ed.selection.collapse(true);
								return _event.cancel(e);
							}
							break;

					}
					break;
			}
		});

		/**
		 * Makes sure doubleclick does not select additional spaces
		 * @param Event e Current KeyPress event
		 */
		ed.on('DblClick', function(e) {
			var range, bsContent, firstSpace, selectionObject;

			if (document.getSelection) {
				range = this.selection.getRng();
				bsContent = String(range);
				firstSpace = bsContent.search(/[\s\u00A0\u48ef\u0020]*$/gi);
				selectionObject = this.selection.getSel();
				range.setEnd(selectionObject.focusNode, selectionObject.focusOffset + (firstSpace - (bsContent.length)));
				this.selection.setRng(range);
			}
			else if (document.selection) {
				// IE code
			}
		});
	};
};

tinymce.PluginManager.add('bsbehaviour', BsBehaviour);