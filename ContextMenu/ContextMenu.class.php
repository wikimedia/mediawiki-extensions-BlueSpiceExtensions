<?php


/**
 * BlueSpice MediaWiki
 * Extension: ContextMenu
 * Description: Provides context menus for various MediaWiki links
 * Authors: Tobias Weichart, Robert Vogel
 *
 * Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit http://www.bluespice.com
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage ContextMenu
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class ContextMenu extends BsExtensionMW {

	/**
	 * Initialization of ContextMenu extension
	 */
	protected function initExt() {
		$this->setHook('BeforePageDisplay');

		BsConfig::registerVar( 'MW::ContextMenu::Modus', 'ctrl', BsConfig::LEVEL_USER|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-contextmenu-pref-modus', 'radio' );
	}

	/**
	 * Called by Preferences and UserPreferences
	 * @param string $sAdapterName Name of the adapter. Probably MW.
	 * @param BsConfig $oVariable The variable that is to be specified.
	 * @return array Option array of specifications.
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		return array(
			'options' => array(
				wfMessage( 'bs-contextmenu-pref-modus-ctrl-and-right-mouse' )->text() => 'ctrl',
				wfMessage( 'bs-contextmenu-pref-modus-just-right-mouse' )->text() => 'no-ctrl'
			),
		);
	}

	/**
	 * Adds resources to ResourceLoader
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean Always true to keep hook running
	 */
	public function onBeforePageDisplay(&$out, &$skin) {
		$out->addModules('ext.bluespice.contextmenu');

		//We check if the current user can send Mails trough the wiki
		//TODO: Maybe move to BSF?
		$mEMailPermissioErrors = SpecialEmailUser::getPermissionsError(
			$this->getUser(), $this->getUser()->getEditToken()
		);

		$bUserCanSendMail = false;
		if ($mEMailPermissioErrors === null) {
			$bUserCanSendMail = true;
		}

		$out->addJsConfigVars( 'bsUserCanSendMail', $bUserCanSendMail );

		return true;
	}

	/**
	 * UnitTestsList allows registration of additional test suites to execute
	 * under PHPUnit. Extensions can append paths to files to the $paths array,
	 * and since MediaWiki 1.24, can specify paths to directories, which will
	 * be scanned recursively for any test case files with the suffix "Test.php".
	 * @param array $paths
	 */
	public static function onUnitTestsList ( array &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}
}
