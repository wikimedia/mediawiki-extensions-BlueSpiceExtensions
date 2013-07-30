/**
 * Review extension
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    1.0.0 beta
 * @version    $Id: review.js 8903 2013-03-14 14:17:41Z pwirth $
 * @package    Bluespice_Extensions
 * @subpackage Review
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

//Last Code Review RBV (30.06.2011)

/**
 * Base class for all Review related methods and properties
 */
BsReview = {
	i18n: {
		dialogTitle: 'Delegate vote to user'
	},
	dialog: false,
	store : false,
	combo : false,
	button: false,
	
	/**
     * Send accept vote to server.
     */
	submitAccept : function() {
		var url = BlueSpice.buildRemoteString('Review', 'getVoteResponse', {
			"pid":wgArticleId,
			"vote":"yes"
		} );
		BlueSpice.requestWithAnswerAndReload(url);
	},
	
	/**
     * Send reject vote to server.
     */
	submitReject : function() {
		var url = BlueSpice.buildRemoteString('Review', 'getVoteResponse', {
			"pid":wgArticleId,
			"vote":"no"
		} );
		BlueSpice.requestWithAnswerAndReload(url);
	}
}

Ext.onReady(function() {
	//Give other extensions the chance to alter the object
	$(document).trigger( 'bsreviewconfigready', [BsReview] );
});

/**
* Open status bar if review is clicked.
*/
$(document).bind( 'BsStateBarRegisterToggleClickElements', function(event, aRegisteredToggleClickElements) {
	aRegisteredToggleClickElements.push($('#sb-Review'));
})
