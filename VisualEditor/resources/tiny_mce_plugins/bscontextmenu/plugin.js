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

(function() {
	var Event = tinymce.dom.Event, each = tinymce.each, DOM = tinymce.DOM;

	/**
	 * This plugin adds a context menu to TinyMCE editor instances.
	 *
	 * @class tinymce.plugins.HWContextMenu
	 */
	tinymce.create('tinymce.plugins.BsContextMenu', {
		/**
		 * @method init
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init: function BsContextMenu(ed) {
			var t = this, showMenu, bsContextMenuNeverUseNative, realCtrlKey;

			
			t.editor = ed;

			bsContextMenuNeverUseNative = ed.settings.hwcontextmenu_never_use_native
				|| ed.settings.contextmenu_never_use_native;

			/**
			 * This event gets fired when the context menu is shown.
			 *
			 * @event onContextMenu
			 * @param {tinymce.plugins.HWContextMenu} sender Plugin instance sending the event.
			 * @param {tinymce.ui.DropMenu} menu Drop down menu to fill with more items if needed.
			 */
			t.onContextMenu = new tinymce.util.Dispatcher(this);

			//This is a workaround to allow other plugins (i.e. "table") to add entries to the context menu
			if (!ed.plugins.contextmenu) {
				ed.plugins.contextmenu = {
					onContextMenu: t.onContextMenu
				};
			}

			showMenu = ed.onContextMenu.add(function(ed, e) {
				// Block TinyMCE menu on ctrlKey and work around Safari issue
				if ((realCtrlKey !== 0 ? realCtrlKey : e.ctrlKey) && !bsContextMenuNeverUseNative)
					return;

				Event.cancel(e);

				// Select the image if it's clicked. WebKit would other wise expand the selection
				if (e.target.nodeName === 'IMG')
					ed.selection.select(e.target);

				t._getMenu(ed).showMenu(e.clientX || e.pageX, e.clientY || e.pageY);
				Event.add(ed.getDoc(), 'click', function(e) {
					hide(ed, e);
				});

				ed.nodeChanged();
			});

			ed.onRemove.add(function() {
				if (t._menu)
					t._menu.removeAll();
			});

			function hide(ed, e) {
				realCtrlKey = 0;

				// Since the hwcontextmenu event moves
				// the selection we need to store it away
				if (e && e.button === 2) {
					realCtrlKey = e.ctrlKey;
					return;
				}

				if (t._menu) {
					t._menu.removeAll();
					t._menu.destroy();
					Event.remove(ed.getDoc(), 'click', hide);
					t._menu = null;
				}
			}

			ed.onMouseDown.add(hide);
			ed.onKeyDown.add(hide);
			ed.onKeyDown.add(function(ed, e) {
				if (e.shiftKey && !e.ctrlKey && !e.altKey && e.keyCode === 121) {
					Event.cancel(e);
					showMenu(ed, e);
				}
			});
		},
		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @method getInfo
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo: function() {
			return {
				longname: 'HwContextmenu Plugin',
				author: 'Hallo Welt! Medienwerkstatt GmbH',
				authorurl: 'http://www.hallowelt.biz',
				infourl: 'http://www.blue-spice.org',
				version: tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},
		_getMenu: function(ed) {
			var t = this,
				m = t._menu,
				se = ed.selection,
				col = se.isCollapsed(),
				el = se.getNode() || ed.getBody(),
				am,
				p;

			if (m) {
				m.removeAll();
				m.destroy();
			}

			p = DOM.getPos(ed.getContentAreaContainer());

			m = ed.controlManager.createDropMenu('hwcontextmenu', {
				offset_x: p.x + ed.getParam('contextmenu_offset_x', 0),
				offset_y: p.y + ed.getParam('contextmenu_offset_y', 0),
				constrain: 1,
				keyboard_focus: true
			});

			t._menu = m;

			m.add({title: 'advanced.cut_desc', icon: 'cut', cmd: 'Cut'}).setDisabled(col);
			m.add({title: 'advanced.copy_desc', icon: 'copy', cmd: 'Copy'}).setDisabled(col);
			m.add({title: 'advanced.paste_desc', icon: 'paste', cmd: 'Paste'});

			if ((el.nodeName === 'A' && !ed.dom.getAttrib(el, 'name')) || !col) {
				m.addSeparator();
				// HW call mceHwLink instead of mceAdvLink
				m.add({title: 'advanced.link_desc', icon: 'link', cmd: 'mceHwLink', ui: true}); //TODO: decouple!
				m.add({title: 'advanced.unlink_desc', icon: 'unlink', cmd: 'UnLink'});
			}

			m.addSeparator();
			// HW call mceHwImage instead of mceAdvImage
			m.add({title: 'advanced.image_desc', icon: 'image', cmd: 'mceHwImage', ui: true}); //TODO: decouple!

			m.addSeparator();
			am = m.addMenu({title: 'contextmenu.align'});
			am.add({title: 'contextmenu.left', icon: 'justifyleft', cmd: 'JustifyLeft'});
			am.add({title: 'contextmenu.center', icon: 'justifycenter', cmd: 'JustifyCenter'});
			am.add({title: 'contextmenu.right', icon: 'justifyright', cmd: 'JustifyRight'});
			am.add({title: 'contextmenu.full', icon: 'justifyfull', cmd: 'JustifyFull'});

			t.onContextMenu.dispatch(t, m, el, col);

			/*
			 //For future releases --BEGIN
			 var contextMenu = [
			 
			 ];
			 $(document).trigger( 'BSVisualEditorContextMenu', [this, contextMenu] );
			 for( var i = 0; i < contextMenu.length; i++ ){
			 var menuItem = contextMenu[i];
			 if( menuItem == 'seperator' ) {
			 m.addSeparator();
			 continue;
			 }
			 m.add( menuItem )
			 }
			 //For future releases --END 
			 */

			return m;
		}
	});

	// Register plugin
	tinymce.PluginManager.add('bscontextmenu', tinymce.plugins.BsContextMenu);
})();