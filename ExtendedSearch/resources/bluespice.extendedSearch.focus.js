/**
 * ExtendedSearch extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
mw.loader.using( 'ext.bluespice.extendedsearch.form', function() {
		var bsExtendedSearchSetFocus = mw.config.get( 'bsExtendedSearchSetFocus' );
		if ( typeof bsExtendedSearchSetFocus === "boolean" ) {
		//$(document).scrollTop(): prevent loosing last scroll position on history back
		if ( mw.config.get( "wgIsArticle" ) === true && bsExtendedSearchSetFocus === true && $( document ).scrollTop() < 1 ) {
			if ( window.location.hash === '' ) {
				$( '#bs-extendedsearch-input' ).focus();
			}
		}
	}
} );