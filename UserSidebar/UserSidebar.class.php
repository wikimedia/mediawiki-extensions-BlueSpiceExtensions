<?php
/**
 * UserSidebar extension for BlueSpice
 *
 * Adds the focus tab to sidebar.
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
 *
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Sebastian Ulbricht
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @version    2.22.0

 * @package    BlueSpice_Extensions
 * @subpackage UserSidebar
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * v1.0.0
 * - Implemented keywords
 * - Raised to stable
 * v0.1
 * - initial release
 */

// Last review MRG (01.07.11 02:24)

/**
 * Base class for UserSidebar extension
 * @package BlueSpice_Extensions
 * @subpackage UserSidebar
 */
class UserSidebar extends BsExtensionMW {

	protected $aKeywords = array();

	/**
	 * Contructor of the UserSidebar class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['UserSidebar'] = __DIR__ . '/UserSidebar.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER;
		$this->mInfo = array(
			EXTINFO::NAME        => 'UserSidebar',
			EXTINFO::DESCRIPTION => 'Adds the focus tab to sidebar.',
			EXTINFO::AUTHOR      => 'Sebastian Ulbricht, Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array('bluespice' => '2.22.0')
		);
		$this->mExtensionKey = 'MW::UserSidebar';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of UserSidebar extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		global $wgAPIModules;
		$this->setHook( 'BS:UserPageSettings', 'onUserPageSettings' );
		$this->setHook( 'BSBlueSpiceSkinFocusSidebar' );
		$this->setHook( 'userCan', 'onUserCan' );
		$this->setHook( 'GetPreferences' );

		BsConfig::registerVar( 'MW::UserSidebar::UserPageSubPageTitle', 'Sidebar', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING, 'bs-usersidebar-pref-UserPageSubPageTitle' );
		BsConfig::registerVar( 'MW::UserSidebar::LinkToEdit', array('href' => '', 'content' => ''), BsConfig::LEVEL_USER, 'bs-usersidebar-userpagesettings-link-title', 'link' );

		$wgAPIModules['sidebar'] = 'ApiSidebar';

		wfProfileOut( 'BS::'.__METHOD__ );
	}


	/**
	 * Hook-Handler for 'userCan', prevents foreign access to a users sidebar settings
	 * @param Title $oTitle Title object being checked against
	 * @param User $oUser Current user object
	 * @param string $sAction Action being checked
	 * @param bool $bResult Pointer to result returned if hook returns false. If null is returned,	userCan checks are continued by internal code.
	 * @return bool false if the user accesses a UserSidebar Title of another user, true in all other cases.
	 */
	public function onUserCan( $oTitle, $oUser, $sAction, $bResult ){
		if( $sAction != 'edit' ) return true;
		if( $oTitle->getNamespace() != NS_USER || !$oTitle->isSubpage() ) return true;
		if( strcasecmp( $oTitle->getSubpageText(), BsConfig::get( 'MW::UserSidebar::UserPageSubPageTitle' ) ) == 0 ){
			$oBasePage = Title::newFromText( $oTitle->getBaseText(), NS_USER );
			if( !$oBasePage->equals( $oUser->getUserPage() ) ) {
				$bResult = false;
				return false;
			}
		}
		return true;
	}

	/**
	 * Hook-Handler for GetPreferences
	 * @param User $oUser Current user object
	 * @param array $aPreferences reference of Preferences array
	 * @return bool true always true to keep hook alive
	 */
	public function onGetPreferences( $oUser, &$aPreferences ) {
		$sUserPageSubPageTitle    = BsConfig::get( 'MW::UserSidebar::UserPageSubPageTitle' );
		$oUserSidebarArticleTitle = Title::makeTitle( NS_USER, $oUser->getName().'/'.$sUserPageSubPageTitle );
		$aPreferences['MW_UserSidebar_LinkToEdit']['default'] = array( 'href' => $oUserSidebarArticleTitle->getEditURL(), 'content' => wfMessage( 'bs-usersidebar-userpagesettings-link-text' )->plain() );
		return true;
	}

	/**
	 * Hook-handler for 'BS:UserPageSettings'
	 * @param User $oUser The current MediaWiki User object
	 * @param Title $oTitle The current MediaWiki Title object
	 * @param array $aSettingViews A list of View objects
	 * @return array The SettingsViews array with an andditional View object
	 */
	public function onUserPageSettings( $oUser, $oTitle, &$aSettingViews ){
		$sUserPageSubPageTitle    = BsConfig::get( 'MW::UserSidebar::UserPageSubPageTitle' );
		$oUserSidebarArticleTitle = Title::makeTitle( NS_USER, $oUser->getName().'/'.$sUserPageSubPageTitle );

		$oUserPageSettingsView = new ViewBaseElement();
		$oUserPageSettingsView->setAutoWrap( '<div id="bs-usersidebar-settings" class="bs-userpagesettings-item">###CONTENT###</div>' );
		$oUserPageSettingsView->setTemplate(
			'<a href="{URL}" title="{TITLE}"><img alt="{IMGALT}" src="{IMGSRC}" /><div class="bs-user-label">{TEXT}</div></a>'
		);
		$oUserPageSettingsView->addData(
			array(
				'URL'      => htmlspecialchars( $oUserSidebarArticleTitle->getEditURL() ),
				'TITLE'    => wfMessage( 'bs-usersidebar-userpagesettings-link-title' )->plain(),
				'TEXT'     => wfMessage( 'bs-usersidebar-userpagesettings-link-text' )->plain(),
				'IMGALT'   => wfMessage( 'bs-usersidebar-userpagesettings-headline' )->plain(),
				'IMGSRC'   => $this->getImagePath().'bs-userpage-sidebar.png',
			)
		);

		$aSettingViews[] = $oUserPageSettingsView;
		return true;
	}

	/**
	 * Filter-Event-Handler for 'BSBlueSpiceSkinFocusSidebar'. Adds Widgets to the focus sidebar.
	 * @param array $aViews Array of views to be rendered in skin
	 * @param User $oUser Current User object
	 * @param QuickTemplate $oQuickTemplate Current QuickTemplate object
	 * @return array Filtered $aViews collection
	 */
	public function onBSBlueSpiceSkinFocusSidebar( &$aViews, $oUser, $oSkin ) {
		if( $oUser->isLoggedIn() === false ) {
			$this->getDefaultWidgets( $aViews, $oUser, $oTitle );
			return true;
		}

		//$bEnableSidebarCache   = $this->mAdapter->get( 'EnableSidebarCache' ); //Currently not in use
		//$bSidebarCacheExpiry   = $this->mAdapter->get( 'SidebarCacheExpiry' ); // TODO RBV (27.06.11 13:25): Use them for caching
		$sTitle = BsConfig::get( 'MW::UserSidebar::UserPageSubPageTitle' );
		$oTitle = Title::makeTitle( NS_USER, $oUser->getName().'/'.$sTitle );
		if( $oTitle->exists() === false ) {
			$this->getDefaultWidgets( $aViews, $oUser, $oTitle );
			return true;
		}
		
		$aWidgets = BsWidgetListHelper::getInstanceForTitle( $oTitle )->getWidgets();
		if( empty($aWidgets) ) {
			$this->getDefaultWidgets( $aViews, $oUser, $oTitle );
		}

		$aViews = array_merge( $aViews, $aWidgets );
		return true;
	}

	/**
	 * Fires event if user is not logged in or UserSidebar Article does not exist.
	 * @param array $aViews of WidgetView objects
	 * @param User $oUser The current MediaWiki User object
	 * @param Title $oTitle The UserSidebar Title object, containing the UserSidebar list
	 * @return array of WidgetView objects
	 */
	private function getDefaultWidgets( &$aViews, $oUser, $oTitle ) {
		wfRunHooks( 'BSUserSidebarDefaultWidgets', array( &$aViews, $oUser, $oTitle ) );
		return true;
	}

}