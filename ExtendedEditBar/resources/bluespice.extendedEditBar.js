/**
 * ExtendedEditBar extension
 *
 * Part of BlueSpice for MediaWiki
 * This file acually configures the individual buttons
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: PageTemplatesAdmin.js 1799 2011-04-29 13:39:02Z mglaser $
 * @package    Bluespice_Extensions
 * @subpackage ExtendedEditBar
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_redirect.png",
	"speedTip": mw.msg('bs-extendededitbar-redirect_tip'),
	"tagOpen": "#REDIRECT [[",
	"tagClose": "]]",
	"sampleText": mw.msg('bs-extendededitbar-redirect_sample')
});

mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_strike.png",
	"speedTip": mw.msg('bs-extendededitbar-strike_tip'),
	"tagOpen": "<s>",
	"tagClose": "</s>",
	"sampleText": mw.msg('bs-extendededitbar-strike_sample')
});

mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_enter.png",
	"speedTip": mw.msg('bs-extendededitbar-enter_tip'),
	"tagOpen": "<br />",
	"tagClose": "",
	"sampleText": mw.msg('bs-extendededitbar-enter_sample')
});

mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_upper_letter.png",
	"speedTip": mw.msg('bs-extendededitbar-upper_tip'),
	"tagOpen": "<sup>",
	"tagClose": "</sup>",
	"sampleText": mw.msg('bs-extendededitbar-upper_sample')
});

mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_lower_letter.png",
	"speedTip": mw.msg('bs-extendededitbar-lower_tip'),
	"tagOpen": "<sub>",
	"tagClose": "</sub>",
	"sampleText": mw.msg('bs-extendededitbar-lower_sample')
});

mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_small.png",
	"speedTip": mw.msg('bs-extendededitbar-small_tip'),
	"tagOpen": "<small>",
	"tagClose": "</small>",
	"sampleText": mw.msg('bs-extendededitbar-small_sample')
});

mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_hide_comment.png",
	"speedTip": mw.msg('bs-extendededitbar-comment_tip'),
	"tagOpen": "<!-- ",
	"tagClose": " -->",
	"sampleText": mw.msg('bs-extendededitbar-comment_sample')
});

mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_gallery.png",
	"speedTip": mw.msg('bs-extendededitbar-gallery_tip'),
	"tagOpen": "\n<gallery>\n",
	"tagClose": "\n</gallery>",
	"sampleText": mw.msg('bs-extendededitbar-gallery_sample')
});
/*
mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_blockquote.png",
	"speedTip": mw.msg('bs-extendededitbar-quote_tip'),
	"tagOpen": "<blockquote>\n",
	"tagClose": "\n</blockquote>",
	"sampleText": mw.msg('bs-extendededitbar-quote_sample')
};
*/
mw.toolbar.addButton({
	"imageFile": bsExtendedEditBarImagePath+"button_insert_table.png",
	"speedTip": mw.msg('bs-extendededitbar-table_tip'),
	"tagOpen": '{| class="wikitable"\n|-\n',
	"tagClose": "\n|}",
	"sampleText": mw.msg('bs-extendededitbar-table_sample')
});
