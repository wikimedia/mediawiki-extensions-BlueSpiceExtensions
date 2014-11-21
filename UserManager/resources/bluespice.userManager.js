/**
 * UserManager extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

(function( mw, $, bs, d, undefined){
	Ext.create( 'BS.UserManager.panel.Manager', {
		renderTo: 'bs-usermanager-grid'
	} );

	$(d).on( 'click', '.bs-um-more-groups', function() {
		$(this).parent('li').hide();
		$(this).parents('ul').next('.bs-um-hidden-groups').show();
		return false;
	});
})(mediaWiki, jQuery, blueSpice, document );