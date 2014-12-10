/**
 * Js for ArticleInfo extension
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>

 * @package    Bluespice_Extensions
 * @subpackage ArticleInfo
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for all ArticleInfo related methods and properties
 */
BsArticleInfo = {
	checkRevisionInterval: 0,

	lastEditInterval: 10000,
	lastEditTimestamp: undefined,
	lastEditedID: undefined,

	init: function () {
		BsArticleInfo.lastEditedID = $('#sb-LastEdited');
		BsArticleInfo.lastEditTimestamp = $('#sb-LastEdited').attr('data-timestamp');
		if(BsArticleInfo.lastEditTimestamp !== undefined && BsArticleInfo.lastEditedID !== undefined) {
			BsArticleInfo.updateLastEdited();
		}

		BsArticleInfo.checkRevisionInterval = bsArticleInfoCheckRevisionInterval*1000;
		if( BsArticleInfo.checkRevisionInterval < 1000 ) return;
		if( wgCurRevisionId < 1 ) return;

		if( $('#bs-statebar').length < 1 ) return;
		BSPing.registerListener('ArticleInfo', BsArticleInfo.checkRevisionInterval, ['checkRevision', wgAction], BsArticleInfo.checkRevisionListener);
	},

	updateLastEdited: function() {
		var sDateTimeOut = bs.util.timestampToAgeString(BsArticleInfo.lastEditTimestamp);
		$('#sb-LastEdited-link').text(sDateTimeOut);
		BsArticleInfo.timeout = setTimeout("BsArticleInfo.updateLastEdited()", BsArticleInfo.lastEditInterval);
	},

	checkRevisionListener: function( result, Listener) {
		if( result.success !== true ) return;
		if( result.newRevision !== true ) {
			BSPing.registerListener('ArticleInfo', BsArticleInfo.checkRevisionInterval, ['checkRevision', wgAction], BsArticleInfo.checkRevisionListener);
			return;
		}

		$('#bs-statebar').find('#bs-statebar-view').before(result.checkRevisionView);
	}
};

mw.loader.using( [ 'ext.bluespice', 'mediawiki.jqueryMsg' ], function() {
	BsArticleInfo.init();
});