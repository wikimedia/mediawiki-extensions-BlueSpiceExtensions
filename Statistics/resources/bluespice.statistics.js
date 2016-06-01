/**
 * Statistics extension
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
Ext.onReady(function() {
	Ext.Loader.setPath( 'BS.Statistics', bs.em.paths.get('Statistics') + '/resources/BS.Statistics');
	Ext.create('BS.Statistics.Panel', {
		renderTo: 'bs-statistics-panel'
	});
});

