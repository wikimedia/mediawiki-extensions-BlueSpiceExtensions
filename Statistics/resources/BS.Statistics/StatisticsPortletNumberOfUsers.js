/**
 * Statistics portlet number of users
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define('BS.Statistics.StatisticsPortletNumberOfUsers', {
	extend: 'BS.Statistics.StatisticsPortlet',
	diagram: 'BsDiagramNumberOfUsers',
	titleKey: 'bs-statistics-portlet-numberofusers',
	filters: ['UserFilter']
});
