/**
 * SmartList extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage SmartList
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.SmartList.MostActivePortlet', {
	extend: 'BS.portal.HTMLPortlet',
	portletConfigClass: 'BS.SmartList.MostActivePortletConfig',

	initComponent: function() {
		this.contentUrl = bs.util.getAjaxDispatcherUrl( 'SmartList::getMostActivePortlet', [ this.portletItemCount, this.portletTimeSpan ] );
		this.callParent(arguments);
	},

	setPortletConfig: function( cfg ) {
		this.callParent(arguments);
		this.cContent.getLoader().load({
			url: bs.util.getAjaxDispatcherUrl( 'SmartList::getMostActivePortlet', [ this.portletItemCount, cfg.portletTimeSpan ] )
		});
	}
} );