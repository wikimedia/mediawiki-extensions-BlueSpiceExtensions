/**
 * FormattingHelp extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage FormattingHelp
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

(function( mw, $, undefined ){
	$(document).on( 'click', 'a#bs-editbutton-formattinghelp', function(e){
		e.preventDefault();

		Ext.require( 'BS.FormattingHelp.Window', function() {
			BS.FormattingHelp.Window.show();
		});

		return false;
	});
})(mediaWiki, jQuery);