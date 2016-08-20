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
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage WidgetBar
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for WidgetBar extension
 * @package BlueSpice_Extensions
 * @subpackage WidgetBar
 */
class WidgetBar extends BsExtensionMW {

	protected $aKeywords = array();

	/**
	 * Initialization of WidgetBar extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'BS:UserPageSettings', 'onUserPageSettings' );
		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );
		$this->setHook( 'userCan', 'onUserCan' );
		$this->setHook( 'GetPreferences' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'EditFormPreloadText' );

		BsConfig::registerVar( 'MW::WidgetBar::LinkToEdit', array ( 'href' => '', 'content' => '' ), BsConfig::LEVEL_USER | BsConfig::NO_DEFAULT, 'bs-widgetbar-userpagesettings-link-title', 'link' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 *
	 * @param OutputPage $oOutputPage
	 * @param SkinTemplate $oSkinTemplate
	 * @return boolean
	 */
	public function onBeforePageDisplay( $oOutputPage, $oSkinTemplate ) {
		$oOutputPage->addModules( 'ext.bluespice.widgetbar' );
		$oOutputPage->addModuleStyles( 'ext.bluespice.widgetbar.style' );

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
		if ( $sAction != 'edit' ) {
			return true;
		}
		if ( $oTitle->getNamespace() != NS_USER || !$oTitle->isSubpage() ){
			return true;
		}
		if ( strcasecmp( $oTitle->getSubpageText(), 'Widgetbar' ) == 0 ) {
			$oBasePage = Title::newFromText( $oTitle->getBaseText(), NS_USER );
			if ( !$oBasePage->equals( $oUser->getUserPage() ) ) {
				$bResult = false;
				return false;
			}
		}
		return true;
	}

	// TODO STM 08.10.12: Docblock
	public function onGetPreferences( $user, &$preferences ) {
		$oWidgetBarArticleTitle = Title::makeTitle( NS_USER, $user->getName().'/Widgetbar' );
		$preferences['MW_WidgetBar_LinkToEdit']['default'] = array(
			'href' => $oWidgetBarArticleTitle->getEditURL(),
			'content' => wfMessage( 'bs-widgetbar-userpagesettings-link-text' )->text()
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
		$oWidgetBarArticleTitle = Title::makeTitle( NS_USER, $oUser->getName().'/Widgetbar' );

		// TODO MRG (01.07.11 02:13): Should be put in a view in BsCore
		$oUserPageSettingsView = new ViewBaseElement();
		$oUserPageSettingsView->setAutoWrap( '<div id="bs-widgetbar-settings" class="bs-userpagesettings-item">###CONTENT###</div>' );
		$oUserPageSettingsView->setTemplate(
			'<a href="{URL}" title="{TITLE}"><img alt="{IMGALT}" src="{IMGSRC}" /><div class="bs-user-label">{TEXT}</div></a>'
		);
		$oUserPageSettingsView->addData(
			array(
				'HEADLINE' => wfMessage( 'bs-widgetbar-userpagesettings-headline' )->plain(),
				'URL' => htmlspecialchars( $oWidgetBarArticleTitle->getEditURL() ),
				'TITLE' => wfMessage( 'bs-widgetbar-userpagesettings-link-title' )->plain(),
				'TEXT' => wfMessage( 'bs-widgetbar-userpagesettings-link-text' )->text(),
				'IMGALT' => wfMessage( 'bs-widgetbar-userpagesettings-headline' )->plain(),
				'IMGSRC' => $this->getImagePath( true ).'bs-userpage-widgets.png',
			)
		);

		$aSettingViews[] = $oUserPageSettingsView;
		return true;
	}

	/**
	 * Hook-Handler for 'BlueSpiceSkin:Widgets'. Adds Widgets to the Widgetbar.
	 * @param SkinTemplate $sktemplate
	 * @param BaseTemplate $tpl
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		$oCurrentTitle = $sktemplate->getTitle();
		$oUser = $sktemplate->getUser();
		$oView = $this->getWidgets( $oCurrentTitle, $oUser );

		if ( $tpl instanceof BsBaseTemplate ) {
			$tpl->data['bs_dataBeforeContent']['bs-widgetbar'] = array(
				'position' => 10,
				'label' => wfMessage( 'prefs-widgetbar' )->text(),
				'content' => $oView
			);
		} else {
			$tpl->data['prebodyhtml'] .= $oView;
		}
		return true;
	}

	protected function getWidgets( $oCurrentTitle, $oUser ) {
		$oWidgetListView = new ViewWidgetList();
		$aWidgetViews = array();
		if ( $oCurrentTitle->userCan( 'read' ) == false ) {
			if ( $oCurrentTitle->isSpecialPage() && $oUser->isLoggedIn() ) {
				$oView = $oWidgetListView->setWidgets(
					$this->getDefaultWidgets( $aWidgetViews, $oUser, $oCurrentTitle )
				);
			} else {
				// set widget list to empty when user cannot read the page
				$oView = $oWidgetListView->setWidgets(
					array()
				);
			}
			return $oView;
		}

		$oTitle = Title::makeTitle( NS_USER, $oUser->getName().'/Widgetbar' );

		if ( $oTitle->exists() === false ) {
			$oView = $oWidgetListView->setWidgets(
				$this->getDefaultWidgets( $aWidgetViews, $oUser, $oTitle )
			);
			return $oView;
		}

		$aWidgets = BsWidgetListHelper::getInstanceForTitle( $oTitle )->getWidgets();
		if( empty($aWidgets) ) {
			$aWidgets = $this->getDefaultWidgets( $aWidgetViews, $oUser, $oTitle );
		}
		$oWidgetListView->setWidgets( $aWidgets );

		$oView = $oWidgetListView;
		return $oView;
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
		return $aViews;
	}

	/**
	 * Fills default widget list definition into user's config page
	 * @param string $text
	 * @param Title $title
	 * @return boolean Always true to keep hook running
	 */
	public function onEditFormPreloadText( &$text, &$title ) {
		if( !$title->equals(Title::makeTitle(NS_USER, $this->getUser()->getName().'/Widgetbar')) ) {
			return true;
		}
		$aViews = array();
		$aViews = $this->getDefaultWidgets($aViews, $this->getUser(), $title);
		$aDefaultWidgetKeywords = array_keys($aViews);

		$text = '* '. implode( "\n* ", $aDefaultWidgetKeywords );

		return true;
	}
}