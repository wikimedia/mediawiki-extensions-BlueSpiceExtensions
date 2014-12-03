/**
 * ExtendedSearch form
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$(document).ready( function() {
	var input = $( '#searchInput' ).clone().attr( {
		id: 'bs-extendedsearch-input',
		name: 'q' }
	);
	$( '#searchInput' ).replaceWith( input );
} );