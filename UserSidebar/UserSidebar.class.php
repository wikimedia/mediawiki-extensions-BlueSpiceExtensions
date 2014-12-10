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
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v2.23.0
 */

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
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER;
		$this->mInfo = array(
			EXTINFO::NAME        => 'UserSidebar',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-usersidebar-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Sebastian Ulbricht, Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
				'bluespice' => '2.22.0',
				'WidgetBar' => 'default'
			)
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
		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );
		$this->setHook( 'userCan', 'onUserCan' );
		$this->setHook( 'GetPreferences' );
		$this->setHook( 'EditFormPreloadText' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'PageContentSaveComplete' );

		$wgAPIModules['sidebar'] = 'ApiSidebar';

		BsConfig::registerVar( 'MW::UserSidebar::LinkToEdit', array('href' => '', 'content' => ''), BsConfig::LEVEL_USER, 'bs-usersidebar-userpagesettings-link-title', 'link' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 *
	 * @param OutputPage $oOutputPage
	 * @param SkinTemplate $oSkinTemplate
	 * @return boolean
	 */
	public function onBeforePageDisplay( $oOutputPage, $oSkinTemplate ) {
		//Removed module to restore old functionality
		//$oOutputPage->addModules( 'ext.bluespice.usersidebar' );

		return true;
	}

	/**
	 * Hook-Handler for 'userCan', prevents foreign access to a users sidebar settings
	 * @param Title $oTitle Title object being checked against
	 * @param User $oUser Current user object
	 * @param string $sAction Action being checked
	 * @param bool $bResult Pointer to result returned if hook returns false. If null is returned,	userCan checks are continued by internal code.
	 * @return bool false if the user accesses a UserSidebar Title of another user, true in all other cases.
	 */
	public function onUserCan( $oTitle, $oUser, $sAction, $bResult ) {
		if( $sAction != 'edit' ) {
			return true;
		}
		if( $oTitle->getNamespace() != NS_USER || !$oTitle->isSubpage() ) {
			return true;
		}
		if( strcasecmp( $oTitle->getSubpageText(), 'Sidebar' ) == 0 ) {
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
		$oUserSidebarArticleTitle = Title::makeTitle( NS_USER, $oUser->getName().'/Sidebar' );
		$aPreferences['MW_UserSidebar_LinkToEdit']['default'] = array(
			'href' => $oUserSidebarArticleTitle->getEditURL(),
			'content' => wfMessage( 'bs-usersidebar-userpagesettings-link-text' )->text()
		);
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
		$oUserSidebarArticleTitle = Title::makeTitle( NS_USER, $oUser->getName().'/Sidebar' );

		$oUserPageSettingsView = new ViewBaseElement();
		$oUserPageSettingsView->setAutoWrap( '<div id="bs-usersidebar-settings" class="bs-userpagesettings-item">###CONTENT###</div>' );
		$oUserPageSettingsView->setTemplate(
			'<a href="{URL}" title="{TITLE}"><img alt="{IMGALT}" src="{IMGSRC}" /><div class="bs-user-label">{TEXT}</div></a>'
		);
		$oUserPageSettingsView->addData(
			array(
				'URL' => htmlspecialchars( $oUserSidebarArticleTitle->getEditURL() ),
				'TITLE' => wfMessage( 'bs-usersidebar-userpagesettings-link-title' )->plain(),
				'TEXT' => wfMessage( 'bs-usersidebar-userpagesettings-link-text' )->text(),
				'IMGALT' => wfMessage( 'bs-tab_focus' )->plain(),
				'IMGSRC' => $this->getImagePath().'bs-userpage-sidebar.png',
			)
		);

		$aSettingViews[] = $oUserPageSettingsView;
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
		if( $oUser->isLoggedIn() ) {
			$sKey = BsCacheHelper::getCacheKey( 'BlueSpice', 'UserSidebar', $oTitle->getPrefixedDBkey() );
		} else {
			$sKey = BsCacheHelper::getCacheKey( 'BlueSpice', 'UserSidebar', 'default' );
		}

		$aData = BsCacheHelper::get( $sKey );

		if( $aData !== false ) {
			wfDebugLog( 'BsMemcached', __CLASS__.': Fetching Widget views from cache' );
			$aViews = $aData;
		} else {
			wfDebugLog( 'BsMemcached', __CLASS__.': Fetching Widget views from DB' );
			wfRunHooks( 'BSUserSidebarDefaultWidgets', array( &$aViews, $oUser, $oTitle ) );
			BsCacheHelper::set( $sKey , $aViews, 60*15 );// invalidate cache after 15 minutes
		}

		return true;
	}

	/**
	 * Adds Focus tab to main navigation
	 * @param SkinTemplate $sktemplate
	 * @param BaseTemplate $tpl
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		$aViews = array();
		$oUser = $sktemplate->getUser();
		$oCurrentTitle = $sktemplate->getTitle();
		$sEditLink = '';
		if ( $oUser->isLoggedIn() === false ) {
			$this->getDefaultWidgets( $aViews, $oUser, $oCurrentTitle );
		} else {
			$oTitle = Title::makeTitle( NS_USER, $oUser->getName().'/Sidebar' );

			$sEditLink = Linker::link(
				$oTitle,
				'',
				array(
					'id' => 'bs-usersidebar-edit',
					'class' => 'icon-pencil'
				),
				array(
					'action' => 'edit',
					'preload' => ''
				)
			);

			if ( $oTitle->exists() === false ) {
				$this->getDefaultWidgets( $aViews, $oUser, $oTitle );
			}else {
				$aWidgets = BsWidgetListHelper::getInstanceForTitle( $oTitle )->getWidgets();
				if ( empty($aWidgets) ) {
					$this->getDefaultWidgets( $aViews, $oUser, $oTitle );
				}

				$aViews = array_merge( $aViews, $aWidgets );
			}
		}
		$aOut = array();
		$aOut[] = $sEditLink;
		foreach ( $aViews as $oView ) {
			if ( $oView instanceof ViewBaseElement ) {
				$aOut[] = $oView->execute();
			}
		}

		if ( $tpl instanceof BsBaseTemplate ) {
			$tpl->data['bs_navigation_main']['bs-usersidebar'] = array(
				'position' => 20,
				'label' => wfMessage( 'bs-tab_focus' )->plain(),
				'class' => 'icon-clipboard',
				'content' => implode( "\n", $aOut )
			);
		} else {
			$tpl->data['sidebar'][wfMessage( 'bs-tab_focus' )->plain()] = implode( "\n", $aOut );
		}

		return true;
	}

	/**
	 * Fills default widget list definition into user's config page
	 * @param string $text
	 * @param Title $title
	 * @return boolean Always true to keep hook running
	 */
	public function onEditFormPreloadText( &$text, &$title ) {
		if( !$title->equals(Title::makeTitle(NS_USER, $this->getUser()->getName().'/Sidebar')) ) {
			return true;
		}
		$aViews = array();
		$this->getDefaultWidgets($aViews, $this->getUser(), $title);
		$aDefaultWidgetKeywords = array_keys($aViews);

		$text = '* '. implode( "\n* ", $aDefaultWidgetKeywords );

		return true;
	}

	/**
	 * Invalidates user sidebar cache
	 * @param Article $article
	 * @param User $user
	 * @param Content $content
	 * @param type $summary
	 * @param type $isMinor
	 * @param type $isWatch
	 * @param type $section
	 * @param type $flags
	 * @param Revision $revision
	 * @param Status $status
	 * @param type $baseRevId
	 * @return boolean
	 */
	public static function onPageContentSaveComplete( $article, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId ) {
		if( !$article->getTitle()->equals( Title::newFromText( $user->getName().'/Sidebar', NS_USER) ) ) return true;

		$aKeys = array(
			BsCacheHelper::getCacheKey( 'BlueSpice', 'UserSidebar', $article->getTitle()->getPrefixedDBkey() ),
			BsCacheHelper::getCacheKey( 'BlueSpice', 'UserSidebar', 'default' ),
		);
		BsCacheHelper::invalidateCache( $aKeys );

		return true;
	}
}