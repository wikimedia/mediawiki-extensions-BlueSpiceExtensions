/**
 * InterWikiManager extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    0.1 beta
 * @package    Bluespice_Extensions
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

 /* Changelog
  * v0.1
  * - initial commit
  */

Ext.onReady( function(){
	Ext.create( 'BS.InterWikiLinks.Panel', {
		renderTo: 'InterWikiLinksGrid'
	} );
} );