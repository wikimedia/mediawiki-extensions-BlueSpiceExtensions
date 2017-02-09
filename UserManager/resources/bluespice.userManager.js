/**
 * UserManager extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

(function( mw, $, bs, d, undefined){
	Ext.onReady( function(){
		Ext.create( 'BS.UserManager.panel.Manager', {
			renderTo: 'bs-usermanager-grid',
			operationPermissions: {
                "create": true,
                "update": true,
                "delete": true,
                // Those are not available in REL1_23 and has set to true;
				'disableuser': true,
				'usergroups': true,
				'editpassword': true,
				'enableuser': true
			}
		} );
	} );

	$(d).on( 'click', '.bs-um-more-groups', function() {
		$(this).parent('li').hide();
		$(this).parents('ul').next('.bs-um-hidden-groups').show();
		return false;
	});
})(mediaWiki, jQuery, blueSpice, document );
