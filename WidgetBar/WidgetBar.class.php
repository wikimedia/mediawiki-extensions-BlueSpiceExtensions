<?php
/**
 * WidgetBar extension for BlueSpice
 *
 * Adds the widget flyout to the skin.
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
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @version    1.22.0
 * @version    $Id: WidgetBar.class.php 9758 2013-06-17 08:58:01Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage WidgetBar
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * v1.0.0
 * - initial release
 */

// Last review MRG (01.07.11 02:11) 

/**
 * Base class for WidgetBar extension
 * @package BlueSpice_Extensions
 * @subpackage WidgetBar
 */
class WidgetBar extends BsExtensionMW {

	protected $aKeywords = array();

	/**
	 * Contructor of the WidgetBar class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['WidgetBar'] = dirname( __FILE__ ) . '/WidgetBar.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER;
		$this->mInfo = array(
			EXTINFO::NAME        => 'WidgetBar',
			EXTINFO::DESCRIPTION => 'Adds the widget flyout to the skin.',
			EXTINFO::AUTHOR      => 'Robert Vogel',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9758 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array('bluespice' => '1.22.0')
		);
		$this->mExtensionKey = 'MW::WidgetBar';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of WidgetBar extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'BS:UserPageSettings', 'onUserPageSettings' );
		$this->setHook( 'BlueSpiceSkin:Widgets', 'onWidgets' );
		$this->setHook( 'userCan', 'onUserCan' );
		$this->setHook( 'GetPreferences' );
		$this->setHook( 'BeforePageDisplay' );

		BsConfig::registerVar( 'MW::WidgetBar::UserPageSubPageTitle', 'Widgetbar', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING, 'bs-widgetbar-pref-UserPageSubPageTitle' );
		BsConfig::registerVar( 'MW::WidgetBar::LinkToEdit', array('href' => '', 'content' => ''), BsConfig::LEVEL_USER, 'bs-widgetbar-userpagesettings-link-title', 'link' );
		BsConfig::registerVar('MW::WidgetBar::SkinWidgetDirection', 'left', BsConfig::LEVEL_USER | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS , 'bs-pref-SkinWidgetDirection', 'select');
		
		$this->registerView( 'ViewWidget' );
		$this->registerView( 'ViewWidgetError' );
		$this->registerView( 'ViewWidgetErrorList' );
		$this->registerView( 'ViewWidgetList' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}
	
	/**
	 * 
	 * @param OutputPage $oOutputPage
	 * @param SkinTemplate $oSkinTemplate
	 * @return boolean
	 */
	public function onBeforePageDisplay( $oOutputPage, $oSkinTemplate ) {
		$oOutputPage->addModules('ext.bluespice.widgetbar');
		return true;
	}

	/**
	 * Hook-Handler for 'userCan', prevents foreign access to a users widgetbar settings
	 * @param Title $oTitle Title object being checked against
	 * @param User $oUser Current user object
	 * @param string $sAction Action being checked
	 * @param bool $bResult Pointer to result returned if hook returns false. If null is returned,	userCan checks are continued by internal code.
	 * @return bool false if the user accesses a UserSidebar Title of another user, true in all other cases.
	 */
	public function onUserCan( $oTitle, $oUser, $sAction, $bResult ){
		if( $sAction != 'edit' ) return true;
		if( $oTitle->getNamespace() != NS_USER || !$oTitle->isSubpage() ) return true;
		if( strcasecmp( $oTitle->getSubpageText(), BsConfig::get( 'MW::WidgetBar::UserPageSubPageTitle' ) ) == 0 ) {
			$oBasePage = Title::newFromText( $oTitle->getBaseText(), NS_USER );
			if( !$oBasePage->equals( $oUser->getUserPage() ) ) {
				$bResult = false;
				return false;
			}
		}
		return true;
	}

	// TODO STM 08.10.12: Docblock
	public function onGetPreferences ( $user, &$preferences ) {
		$sUserPageSubPageTitle  = BsConfig::get( 'MW::WidgetBar::UserPageSubPageTitle' );
		$oWidgetBarArticleTitle = Title::makeTitle( NS_USER, $user->getName().'/'.$sUserPageSubPageTitle );
		$preferences['MW_WidgetBar_LinkToEdit']['default'] = array( 
			'href' => $oWidgetBarArticleTitle->getEditURL(), 
			'content' => wfMsg( 'bs-widgetbar-userpagesettings-link-text' )
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
		$sUserPageSubPageTitle  = BsConfig::get('MW::WidgetBar::UserPageSubPageTitle');
		$oWidgetBarArticleTitle = Title::makeTitle( NS_USER, $oUser->getName().'/'.$sUserPageSubPageTitle );

		// TODO MRG (01.07.11 02:13): Should be put in a view in BsCore
		$oUserPageSettingsView = new ViewBaseElement();
		$oUserPageSettingsView->setAutoWrap( '<div id="bs-widgetbar-settings" class="bs-userpagesettings-item">###CONTENT###</div>' );
		$oUserPageSettingsView->setTemplate(
			'<a href="{URL}" title="{TITLE}"><img alt="{IMGALT}" src="{IMGSRC}" /><div class="bs-user-label">{TEXT}</div></a>'
		);
		$oUserPageSettingsView->addData(
			array(
				'HEADLINE' => wfMsg( 'bs-widgetbar-userpagesettings-headline' ),
				'URL'      => htmlspecialchars( $oWidgetBarArticleTitle->getEditURL() ),
				'TITLE'    => wfMsg( 'bs-widgetbar-userpagesettings-link-title' ),
				'TEXT'     => wfMsg( 'bs-widgetbar-userpagesettings-link-text' ),
				'IMGALT'   => wfMsg( 'bs-widgetbar-userpagesettings-headline' ),
				'IMGSRC'   => $this->getImagePath().'bs-userpage-widgets.png',
			)
		);

		$aSettingViews[] = $oUserPageSettingsView;
		return true;
	}

	/**
	 * Hook-Handler for 'BlueSpiceSkin:Widgets'. Adds Widgets to the Widgetbar.
	 * @param array $aViews Array of views to be rendered in skin
	 * @param User $oUser Current User object
	 * @param QuickTemplate $oQuickTemplate Current QuickTemplate object
	 * @return boolean Always true to keep the hook running
	 */
	public function onWidgets( &$aViews, $oUser, $oQuickTemplate ) {
		global $wgTitle;
		$oWidgetListView = new ViewWidgetList();
		$aWidgetViews = array();
		if( $wgTitle->userCan('read') == false) {
			if( $wgTitle->getNamespace() == -1 && $oUser->isLoggedIn() ) {
				$aViews[] = $oWidgetListView->setWidgets( $this->getDefaultWidgets( $aWidgetViews, $oUser, $oTitle ) );
				return $aViews;
			}
			return $aViews;
		}

		$sTitle = BsConfig::get('MW::WidgetBar::UserPageSubPageTitle');
		$oTitle = Title::makeTitle( NS_USER, $oUser->getName().'/'.$sTitle );

		if($oTitle->exists() === false ) {
			$aViews[] = $oWidgetListView->setWidgets( $this->getDefaultWidgets( $aWidgetViews, $oUser, $oTitle ) );
			return $aViews;
		}

		$oWidgetListView->setWidgets( 
			BsWidgetListHelper::getInstanceForTitle( $oTitle )->getWidgets()
		);
		$aViews[] = $oWidgetListView;
		return $aViews;
	}

	/**
	 * Fires event if user is not logged in or UserSidebar Article does not exist.
	 * @param array $aViews of WidgetView objects
	 * @param User $oUser The current MediaWiki User object
	 * @param Title $oTitle The UserSidebar Title object, containing the UserSidebar list
	 * @return array of WidgetView objects
	 */
	private function getDefaultWidgets( &$aViews, $oUser, $oTitle) {
		wfRunHooks( 'BSWidgetBarGetDefaultWidgets', array( &$aViews, $oUser, $oTitle ) );
		/*$oEvent = new BsEvent(
			$this,
			'MW::WidgetBar::DefaultWidgets',
			array(
				'user'          => $oUser,
				'sidebar-title' => $oTitle
				)
			);
		BsEventDispatcher::getInstance( 'MW' )->filter( $oEvent, $aViews );*/
		return $aViews;
	}
	
	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array(
			'options' => array(
				wfMsg( 'bs-pref-SkinWidgetDirection-left' ) => 'left',
				wfMsg( 'bs-pref-SkinWidgetDirection-right' ) => 'right',
			)
		);
		return $aPrefs;
	}
}