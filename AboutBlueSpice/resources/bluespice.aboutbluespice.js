/**
 * About BlueSpice extension
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @version    1.0.0 beta

 * @package    Bluespice_Extensions
 * @subpackage AboutBlueSpice
 * @copyright  Copyright (C) 2015 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
* Adds items for About BlueSpice to top and main menu.
*/

$(function () {
	// Add About BlueSpice link to top menu
	var sAboutBlueSpiceTopLink = mw.html.element(
			'a',
			{
				href: mw.util.getUrl('Special:AboutBlueSpice'),
				class: 'menu-item-single level-1'
			},
			mw.message('bs-aboutbluespice-about-bluespice').plain()
		);
	var sAboutBlueSpiceTopMenuItem = mw.html.element(
			'li',
			{},
			new mw.html.Raw( sAboutBlueSpiceTopLink )
		);
	$('#bs-apps').find('ul').append( sAboutBlueSpiceTopMenuItem );

	// Add About BlueSpice link to main navigation
	var sAboutBlueSpiceMainLink = mw.html.element(
			'a',
			{
				href: mw.util.getUrl('Special:About_BlueSpice'),
				class: 'menu-item-single level-1'
			},
			new mw.html.Raw(
					mw.html.element(
						'span',
						{class: 'icon24'}
					) +
					mw.html.element(
						'span',
						{class: 'bs-nav.item-text'},
						mw.message('bs-aboutbluespice-about-bluespice').plain()
					)
				)
		);
	var sAboutBlueSpiceMainMenuItem = mw.html.element(
			'li',
			{
				class: 'clearfix'
			},
			new mw.html.Raw( sAboutBlueSpiceMainLink )
		);
	$('#p-navigation').find('ul').append( sAboutBlueSpiceMainMenuItem );
});