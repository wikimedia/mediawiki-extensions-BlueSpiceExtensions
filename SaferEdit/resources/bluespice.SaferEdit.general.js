/**
 * SaferEdit extension
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>

 * @package    Bluespice_Extensions
 * @subpackage SaferEdit
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Base class for all general safer edit related methods and properties
 */
BsSaferEdit = {
	/**
	 * Time between two intermediate saves
	 * @var integer time in seconds
	 */
	interval: 0,

	/**
	 * Conducts neccessary preparations of edit form and starts intermediate saving
	 */
	init: function() {
		this.interval = mw.config.get( 'bsSaferEditInterval' ) * 1000;

		if ( this.interval < 1000 || $( '#bs-statebar' ).length < 1 ) {
			return;
		}
		BSPing.registerListener( 'SaferEditIsSomeoneEditing', BsSaferEdit.interval, [], BsSaferEdit.someoneIsEditingListener );
	},

	someoneIsEditingListener: function(result, Listener) {
		if(result.success !== true) return;

		if( $('#sb-SaferEditSomeoneEditing').length > 0 ) {
			$('#sb-SaferEditSomeoneEditing').replaceWith(result.someoneEditingView);
		} else {
			$('#bs-statebar').find('#bs-statebar-view').before(result.someoneEditingView);
		}

		if( $('#sb-SaferEdit').length > 0 ) {
			$('#sb-SaferEdit').replaceWith(result.safereditView);
		} else {
			$('#bs-statebar').find('#bs-statebar-view').before(result.safereditView);
		}

		BSPing.registerListener( 'SaferEditIsSomeoneEditing', BsSaferEdit.interval, [], BsSaferEdit.someoneIsEditingListener );
	}
};

mw.loader.using( 'ext.bluespice', function() {
	BsSaferEdit.init();
});