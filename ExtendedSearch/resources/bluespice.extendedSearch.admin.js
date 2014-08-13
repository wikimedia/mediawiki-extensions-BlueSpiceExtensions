/**
 * ExtendedSearch admin extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

 /**
 * Triggers Re-indexing of search index.
 */
bsExtendedSearchConfirm = function( warning, msg ) {
	bs.util.confirm( 'ESAdmin', { text: msg, title: warning }, { ok: processConfirm, cancel: function() {}, scope: this } );
};

processConfirm = function() {
	Ext.Ajax.request( {
		url: bs.util.getAjaxDispatcherUrl( 'ExtendedSearchAdmin::getProgressBar', [ "deleteLock" ] ),
		method: 'post',
		scope: this,
		success: function( response, opts ) {
			var x = true;
			window.location.reload();
		}
	} );
};

bsExtendedSearchStartCreate = function() {
	try {
		if ( !document.getElementById( 'BsExtendedSearchProgress' ) ) {
			setTimeout( bsExtendedSearchStartCreate, 100 );
			return;
		}
	} catch(e) {}

	progBar = new Ext.ProgressBar({
		renderTo:'BsExtendedSearchProgress'
	});

	Ext.Ajax.request( { url: bs.util.getAjaxDispatcherUrl( 'ExtendedSearchAdmin::getProgressBar', [ "create" ] ), timeout: 60000 } );
	setTimeout( bsExtendedSearchRequestProgress, 500 );
};

/**
 * Updates progress bar.
 * @var int count Number of progress bar iterations.
 */
bsExtendedSearchRequestProgress = function() {
	Ext.Ajax.request({
		url: wgScriptPath + '/extensions/BlueSpiceExtensions/ExtendedSearch/includes/BuildIndex/index_progress.php',
		success: function( response, opts ) {
			var res = "";
			if ( response.responseText !== "" ) {
				res = Ext.decode( response.responseText );
			}
			var finished = false;
			if ( typeof( res ) === 'object' ) {
				if ( res[0] === "__FINISHED__" ) {
					finished = true;
				} else {
					if ( res[0] ) document.getElementById( 'BsExtendedSearchMode' ).innerHTML = res[0];
					if ( res[1] ) document.getElementById( 'BsExtendedSearchMessage' ).innerHTML = res[1];
					if ( res[2] ) progBar.updateProgress( res[2] / 100 );
				}
			}
			if ( !finished ) {
				setTimeout( bsExtendedSearchRequestProgress, 300 );
			}
		},
		failure: function(response, opts) {}
	});
};