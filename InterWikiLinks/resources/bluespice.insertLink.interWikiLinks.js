/**
 * InterWikiManager extension
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    0.1 beta
 * @package    Bluespice_Extensions
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$(document).bind('BsInsertLinkWindowBeforeAddTabs', function( event, window, items ){
	Ext.Loader.setPath( 'BS.InsertLinkInterWiki', wgScriptPath+'/extensions/BlueSpiceExtensions/InterWikiLinks/resources/BS.InsertLinkInterWiki');

	var storeData = []
	for(var i = 0; i < mw.config.get('BSInterWikiPrefixes', []).length; i++) {
		storeData.push({
			name: mw.config.get('BSInterWikiPrefixes', [])[i],
			label: mw.config.get('BSInterWikiPrefixes', [])[i]
		});
	}
	var storeIW = Ext.create('Ext.data.Store', {
		fields: [
			'name',
			'label',
		],
		data: storeData,
		autoLoad: false
	});

	items.push(
		Ext.create( 'BS.InsertLinkInterWiki.FormPanelInterWiki', { storeIW: storeIW } )
	);
});