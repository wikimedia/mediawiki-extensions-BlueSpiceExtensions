/**
 * ExtendedSearch extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.ExtendedSearch.RecentSearchTermsPortlet', {
	extend: 'BS.portal.HTMLPortlet',
	portletConfigClass: 'BS.ExtendedSearch.RecentSearchTermsPortletConfig',

	initComponent: function() {
		this.contentUrl = bs.util.getAjaxDispatcherUrl( 'ExtendedSearchBase::getRecentSearchTerms', [ this.portletItemCount, this.portletTimeSpan ] );
		this.callParent(arguments);
	},

	setPortletConfig: function( cfg ) {
		this.callParent(arguments);
		this.cContent.getLoader().load({
			url: bs.util.getAjaxDispatcherUrl( 'ExtendedSearchBase::getRecentSearchTerms', [ this.portletItemCount, cfg.portletTimeSpan ] )
		});
	}
} );