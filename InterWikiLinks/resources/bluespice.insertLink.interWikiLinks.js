/**
 * InterLink js for InsertLink extension
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$(document).bind('BSVisualEditorLoadContentBeforeCheckLinks', function(event, editor, internalLinksTitles, internalLinks) {
	var interWikiLinks = mw.config.get('BSInterWikiPrefixes', []);
	if( interWikiLinks.length < 1 ) {
		return;
	}

	for( var i = 0; i < internalLinksTitles.length; i++) {
		var pref = internalLinksTitles[i].split(':');
		if( pref.length < 2 ) {
			continue;
		}
		pref = pref[0];
		if( $.inArray( pref, interWikiLinks ) === -1 ) {
			continue;
		}
		internalLinksTitles.splice(i, 1);
	}
});

$(document).bind('BsInsertLinkWindowBeforeAddTabs', function( event, window, items ){
	var storeData = [];
	for(var i = 0; i < mw.config.get('BSInterWikiPrefixes', []).length; i++) {
		storeData.push({
			name: mw.config.get('BSInterWikiPrefixes', [])[i],
			label: mw.config.get('BSInterWikiPrefixes', [])[i]
		});
	}
	var storeIW = Ext.create('Ext.data.Store', {
		fields: [
			'name',
			'label'
		],
		data: storeData,
		autoLoad: false
	});

	items.push(
		Ext.create( 'BS.InterWikiLinks.InsertLink.FormPanelInterWiki', { storeIW: storeIW } )
	);
});