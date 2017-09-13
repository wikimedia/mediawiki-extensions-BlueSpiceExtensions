<?php
/**
 * ExtensionInfo extension for BlueSpice
 *
 * Information about active Hallo Welt! extensions.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @version    2.23.1
 * @package    Bluespice_Extensions
 * @subpackage ExtensionInfo
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for ExtensionInfo extension
 * @package BlueSpice_Extensions
 * @subpackage ExtensionInfo
 */
class ExtensionInfo extends BsExtensionMW {

	/**
	 * Adds a link to WikiAdmin menu
	 * @param array $aOutSortable
	 * @param \User The user in which context the menu is rendered
	 * @return boolean Alway true to keep hook running
	 */
	public static function onBSWikiAdminMenuItems( &$aOutSortable, $oUser ) {
		if( !$oUser->isAllowed( 'wikiadmin' ) ) {
			return true;
		}
		$sLabel = wfMessage( 'bs-extensioninfo-label' )->plain();
		$aOutSortable[$sLabel] = Html::rawElement( 'li', array(),
			Linker::link( SpecialPage::getTitleFor( 'ExtensionInfo' ), $sLabel, array( 'class' => 'bs-admin-link bs-icon-puzzle' ) )
		);
		return true;
	}

}
