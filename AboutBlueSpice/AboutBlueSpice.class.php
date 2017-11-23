<?php
/**
 * BlueSpice MediaWiki
 * Extension: AboutBlueSpice
 * Description: Show user additional options of the pro version.
 * Authors: Markus Glaser
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage AboutBlueSpice
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

class AboutBlueSpice extends BsExtensionMW {

	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Hooks
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSWikiAdminMenuItems' );
		$this->setHook( 'BSTopMenuBarCustomizerRegisterNavigationSites' );
		$this->setHook( 'SkinBuildSidebar' );

		BsConfig::registerVar( 'MW::AboutBlueSpice::ShowMenuLinks', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-aboutbluespice-show-menu-links', 'toggle' );

		$this->mCore->registerPermission( 'aboutbluespice-viewspecialpage', array('user'), array( 'type' => 'global' ) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public static function onBeforePageDisplay( &$out, &$skin ) {
		if ( BsConfig::get( 'MW::AboutBlueSpice::ShowMenuLinks' )) {
			$out->addModules( 'ext.bluespice.aboutbluespice' );
		}
		return true;
	}

	/**
	 * Returns a list item with a link to the "About BlueSpice" special page
	 * @param array $aOutSortable Indexed list of menu items. Add item in HTML form.
	 * @param \User The user in which context the menu is rendered
	 * @return string Link to the "About BlueSpice" special page
	 */
	public static function onBSWikiAdminMenuItems( &$aOutSortable, $oUser ) {
		if ( !BsConfig::get( 'MW::AboutBlueSpice::ShowMenuLinks' )) {
			return true;
		}
		if( !$oUser->isAllowed( 'aboutbluespice-viewspecialpage' ) ) {
			return true;
		}
		$oSpecialPage = SpecialPage::getTitleFor( 'AboutBlueSpice' );
		$sLink = Html::element(
				'a',
				array (
					'id' => 'bs-admin-aboutbluespice',
					'href' => $oSpecialPage->getLocalURL(),
					'title' => wfMessage( 'bs-aboutbluespice-about-bluespice' )->plain(),
					'class' => 'bs-admin-link'
				),
				wfMessage( 'bs-aboutbluespice-about-bluespice' )->plain()
		);
		$aOutSortable[wfMessage( 'bs-aboutbluespice-about-bluespice' )->escaped()] = '<li>' . $sLink . '</li>';
		return true;
	}

	/**
	 * Adds entry to navigation sites
	 * @param array $aNavigationSites
	 * @return boolean - always true
	 */
	public function onBSTopMenuBarCustomizerRegisterNavigationSites( &$aNavigationSites ) {
		if ( !BsConfig::get( 'MW::AboutBlueSpice::ShowMenuLinks' )) {
			return true;
		}

		$oSpecialPage = SpecialPage::getTitleFor( 'AboutBlueSpice' );

		$aNavigationSites[] = array(
			'id' => 'nt-aboutbluespice',
			'href' => $oSpecialPage->getLocalURL(),
			'active' => false,
			'level' => 1,
			'text' => wfMessage( 'bs-aboutbluespice-about-bluespice' )->plain()
		);
		return true;
	}

	/**
	 * Adds entry to main navigation
	 * @param object $oSkinTemplate - not used
	 * @param array $aLinks - unrendered list of links
	 * @return boolean - always true
	 */
	public function onSkinBuildSidebar( $oSkinTemplate, &$aLinks ) {
		if ( !BsConfig::get( 'MW::AboutBlueSpice::ShowMenuLinks' )) {
			return true;
		}
		$oSpecialPage = SpecialPage::getTitleFor( 'AboutBlueSpice' );

		$aLinks[ "navigation" ][] = array(
			'id' => 'n-aboutbluespice',
			'href' => $oSpecialPage->getLocalURL(),
			'text' => wfMessage( 'bs-aboutbluespice-about-bluespice' )->plain()
		);

		return true;
	}
}