/**
 * ExtendedEditBar extension
 *
 * Part of BlueSpice for MediaWiki
 * This file acually configures the individual buttons
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>

 * @package    Bluespice_Extensions
 * @subpackage ExtendedEditBar
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

(function( mw, $, undefined ){
	$(document).on( 'click', 'a.mw-toolbar-editbutton', function( e ){
		if( $(this).data('open') == undefined ) return true;
		e.preventDefault();
		
		mw.toolbar.insertTags( 
			$(this).data( 'open' ), 
			$(this).data( 'close' ), 
			$(this).data( 'sample' ), 
			$(this).data( 'select' )
		);
		
		return false;
	});
})(mediaWiki, jQuery);