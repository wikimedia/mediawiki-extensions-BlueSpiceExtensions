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
	Ext.Msg.show({
		title: warning,
		msg: msg,
		buttons: Ext.Msg.OKCANCEL,
		fn: processConfirm
	});
}

processConfirm = function( buttonId, text, opt ) {
	if ( buttonId == 'ok' ) {
		var url = BlueSpice.buildRemoteString(
			'ExtendedSearchAdmin',
			'getProgressBar',
			{
				"mode": "deleteLock"
			}
		);
		Ext.Ajax.request( {
			url: url,
			success: function( response, opts ) {
				window.location.reload();
			}
		} );
	}
	else {
		return false;
	}
}

bsExtendedSearchStartCreate = function() {
	try {
		if ( !document.getElementById( 'BsExtendedSearchProgress' ) ) {
			setTimeout( 'bsExtendedSearchStartCreate()', 100 );
			return;
		}
	} catch(e) { }

	progBar = new Ext.ProgressBar({
		renderTo:'BsExtendedSearchProgress'
	});

	Ext.Ajax.request( { url: wgScriptPath+'/index.php?action=remote&mod=ExtendedSearchAdmin&rf=getProgressBar&mode=create', timeout: 60000 } );
	bsExtendedSearchRequestProgress(0);
}

/**
 * Updates progress bar.
 * @var int count Number of progress bar iterations.
 */
bsExtendedSearchRequestProgress = function( count ) {
	Ext.Ajax.request({
		url: wgScriptPath + '/extensions/BlueSpiceExtensions/ExtendedSearch/includes/BuildIndex/index_progress.php',
		success: function(response, opts) {
			res = Ext.decode(response.responseText);
			finished = false;
			if(typeof(res) == 'object') {
				if (res[0]=="__FINISHED__") finished = true;
				else {
					if (res[0]) document.getElementById('BsExtendedSearchMode').innerHTML = res[0];
					if (res[1]) document.getElementById('BsExtendedSearchMessage').innerHTML = res[1];
					if (res[2]) progBar.updateProgress(res[2]/100);
				}
			}
			if (!finished) {
				newcount = count + 1;
				setTimeout('bsExtendedSearchRequestProgress('+newcount+')', 100);
			}
		},
		failure: function(response, opts) {}
	});
}