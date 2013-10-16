/**
 * RSSFeeder extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage RSSFeeder
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.RSSFeeder.RSSPortlet', {
	extend: 'BS.portal.HTMLPortlet',
	portletConfigClass: 'BS.RSSFeeder.RSSPortletConfig',

	constructor: function() {
		this.autoLoadPortlet = false;
		this.callParent(arguments);
		this.cContent.getLoader().load({
			url: bs.util.getAjaxDispatcherUrl( 'RSSFeeder::getRSS', [ this.portletItemCount, this.rssurl ] )
		});
	},
	setPortletConfig: function( cfg ) {
		this.rssurl = cfg.rssurl;
		this.callParent(arguments);
		this.cContent.getLoader().load({
			url: bs.util.getAjaxDispatcherUrl( 'RSSFeeder::getRSS', [ this.portletItemCount, cfg.rssurl ] )
		});
	},
	getPortletConfig: function() {
		//There is no method like Panel::getTitle()!
		return {
			title: this.title,
			height: this.height || 0,
			portletItemCount: this.portletItemCount,
			portletTimeSpan: this.portletTimeSpan,
			collapsed: this.getCollapsed(),
			rssurl: this.rssurl
		};
	}
} );