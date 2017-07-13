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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Sebastian Ulbricht
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage UserSidebar
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for UserSidebar extension
 * @package BlueSpice_Extensions
 * @subpackage UserSidebar
 */
class UserSidebar extends BsExtensionMW {

	protected $aKeywords = array();
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

		BsConfig::registerVar( 'MW::UserSidebar::LinkToEdit', array ( 'href' => '', 'content' => '' ), BsConfig::LEVEL_USER | BsConfig::NO_DEFAULT, 'bs-usersidebar-userpagesettings-link-title', 'link' );

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
	 * Returns the basic mediawiki links for the global actions widget
	 * @param array $aLinks
	 * @return array
	 */
	public static function getMediaWikiGlobalActions( $aLinks = array() ) {
		if( $oSpecialUpload = SpecialPageFactory::getPage( 'Upload' ) ) {
			$aLinks[] = array(
				'target' => $oSpecialUpload->getPageTitle(),
				'text' => $oSpecialUpload->getDescription(),
				'attr' => array(),
				'position' => 100,
				'permissions' => array(
					'read',
					'upload'
				),
			);
		}

		if( $oSpecialListFiles = SpecialPageFactory::getPage( 'ListFiles' ) ) {
			$aLinks[] = array(
				'target' => $oSpecialListFiles->getPageTitle(),
				'text' => $oSpecialListFiles->getDescription(),
				'attr' => array(),
				'position' => 200,
				'permissions' => array( 'read' ),
			);
		}

		if( $oSpecialWatchlist = SpecialPageFactory::getPage( 'Watchlist' ) ) {
			$aLinks[] = array(
				'target' => $oSpecialWatchlist->getPageTitle(),
				'text' => $oSpecialWatchlist->getDescription(),
				'attr' => array(),
				'position' => 300,
				'permissions' => array(
					'read',
					'viewmywatchlist'
				),
			);
		}

		$oSpecialSpecialPages = SpecialPageFactory::getPage( 'SpecialPages' );
		if( $oSpecialSpecialPages ) {
			$aLinks[] = array(
				'target' => $oSpecialSpecialPages->getPageTitle(),
				'text' => $oSpecialSpecialPages->getDescription(),
				'attr' => array(),
				'position' => 400,
				'permissions' => array( 'read' ),
			);
		}

		return $aLinks;
	}

	/**
	 * Returns the view for the global actions widget
	 * @param User $oUser
	 * @return \ViewWidget
	 */
	public function getGlobalActionsWidget( User $oUser, $aLinks = array() ) {
		$aLinks = static::getMediaWikiGlobalActions( $aLinks );

		$sWidgetTitle = wfMessage(
			'bs-usersidebar-globalactionswidget-title'
		)->plain();

		Hooks::run( 'BSUserSidebarGlobalActionsWidgetGlobalActions', array(
			$this,
			$oUser,
			&$aLinks,
			&$sWidgetTitle,
		));

		$sBody = "";
		if( empty($aLinks) ) {
			return $sBody;
		}

		uasort( $aLinks, function( &$e1, &$e2 ) {
			if( empty($e1['position']) || !is_int($e1['position']) ) {
				$e1['position'] = 10000;
			}
			if( empty($e2['position']) || !is_int($e2['position']) ) {
				$e2['position'] = 10000;
			}
			if( $e1['position'] === $e2['position'] ) {
				return 0;
			}
			return $e1['position'] < $e2['position'] ? -1 : 1;
		});

		foreach( $aLinks as $aLink ) {
			$oTitle = $aLink['target'];
			if( !isset($oTitle) || !$oTitle instanceof Title ) {
				continue;
			}
			$b = true;
			if( !empty($aLink['permissions']) ) {
				foreach( $aLink['permissions'] as $sPermission ) {
					//UserCan on special pages only works for read
					$b = $oTitle->isSpecialPage() && $sPermission != 'read'
						? $oUser->isAllowed( $sPermission )
						: $oTitle->userCan( $sPermission, $oUser )
					;
					if( !$b ) {
						break;
					}
				}
			}
			if( !$b ) {
				continue;
			}

			if( $oTitle->isSpecialPage() ) {
				$sClass = strtolower( get_class( SpecialPageFactory::getPage( $oTitle->getText() ) ) );
				$sClass .= ' bs-globalaction-specialpage ';
			}
			else {
				$sClass = $oTitle->getDBkey();
			}
			if( !isset($aLink['attr']) || !is_array($aLink['attr']) ) {
				$aLink['attr'] = array();
			}
			if( !isset( $aLink['attr']['class'] ) ) {
				$aLink['attr']['class'] = '';
			}
			$aLink['attr']['class'] .= ' bs-globalactions-items';
			$aLink['attr']['class'] .= ' bs-globalaction-';
			$aLink['attr']['class'] .= strtolower( NamespaceManager::getNamespaceConstName( $oTitle->getNamespace(), array() ) );
			$aLink['attr']['class'] .=  '-' . $sClass;

			if( empty($aLink['text']) ) {
				$aLink['text'] = $oTitle->getText();
			}

			$sBody .= Html::openElement( 'li' )
				.Linker::link( $oTitle, $aLink['text'], $aLink['attr'] )
				.Html::closeElement( 'li' )
				."\n"
			;
		}

		$sBody = Html::openElement( 'ul' )
			.$sBody
			.Html::closeElement( 'ul' )
		;

		$oWidgetView = new ViewWidget();
		return $oWidgetView
			->setBody( $sBody )
			->setTitle( $sWidgetTitle )
			->setAdditionalBodyClasses( array( 'bs-nav-links' ) )
		;
	}

	/**
	 * Returns the widgets for the global actions tab
	 * @param User $oUser The current MediaWiki User object
	 * @param array $aViews of WidgetView objects
	 * @return array of WidgetView objects
	 */
	private function getGlobalActionsWidgets( User $oUser, $aViews = array() ) {
		//Each user needs a separate cache due to differences in permissions
		//etc.
		$sContext = $oUser->isLoggedIn()
			? $oUser->getName()
			: 'default'
		;
		$sKey = BsCacheHelper::getCacheKey(
			'BlueSpice',
			'GlobalActionsWidgets',
			$sContext
		);
		$aData = BsCacheHelper::get( $sKey );

		if( $aData !== false ) {
			wfDebugLog(
				'UserSidebar',
				__CLASS__.': Fetching GlobalActionsWidgets views from cache'
			);
			return $aData;
		}
		wfDebugLog(
			'UserSidebar',
			__CLASS__.': Fetching GlobalActionsWidgets views from DB'
		);

		$aViews[] = $this->getGlobalActionsWidget( $oUser );
		Hooks::run( 'BSUserSidebarGlobalActionsWidgets', array(
			&$aViews,
			$oUser,
		));

		//Max cache time 24h
		BsCacheHelper::set( $sKey , $aViews, 60*1440 );
		return $aViews;
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
		$oUser = $sktemplate->getUser();
		if( !$oUser->isLoggedIn() ) {
			return true;
		}
		$aViews = array();
		$oCurrentTitle = $sktemplate->getTitle();
		$sEditLink = '';

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
		$aOut = array();
		$aOut[] = $sEditLink;
		foreach ( $aViews as $oView ) {
			if ( $oView instanceof ViewBaseElement ) {
				$aOut[] = $oView->execute();
			}
		}

		$sMsg = wfMessage( 'bs-usersidebar-tab-focus' )->plain();
		if ( $tpl instanceof BsBaseTemplate ) {
			$tpl->data['bs_navigation_main']['bs-usersidebar'] = array(
				'position' => 20,
				'label' => $sMsg,
				'class' => 'bs-icon-clipboard',
				'content' => implode( "\n", $aOut )
			);
		} else {
			$tpl->data['sidebar'][$sMsg] = implode( "\n", $aOut );
		}

		$aOut = array();
		$aViews = $this->getGlobalActionsWidgets( $oUser );
		foreach ( $aViews as $oView ) {
			if ( $oView instanceof ViewBaseElement ) {
				$aOut[] = $oView->execute();
			}
		}

		$sMsg = wfMessage( 'bs-usersidebar-tab-globalactions' )->plain();
		if ( $tpl instanceof BsBaseTemplate ) {
			$tpl->data['bs_navigation_main']['bs-globalactions'] = array(
				'position' => 100,
				'label' => $sMsg,
				'class' => 'bs-icon-cog',
				'content' => implode( "\n", $aOut )
			);
		} else {
			$tpl->data['sidebar'][$sMsg] = implode( "\n", $aOut );
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

	/**
	 * Hook handler for UserSaveSettings - invalidate widget caches
	 * @param User $user
	 * @return boolean
	 */
	public static function onUserSaveSettings( $user ) {
		$aKeys = [];
		$aKeys[] = BsCacheHelper::getCacheKey(
			'BlueSpice',
			'GlobalActionsWidgets',
			$user->getName()
		);

		$oTitle = Title::makeTitle( NS_USER, $user->getName().'/Sidebar' );
		if( $oTitle->exists() ) {
			$aKeys[] = BsCacheHelper::getCacheKey(
				'BlueSpice',
				'WidgetListHelper',
				$oTitle->getPrefixedDBkey()
			);
		} else {
			$aKeys[] = BsCacheHelper::getCacheKey(
				'BlueSpice',
				'UserSidebar',
				$oTitle->getPrefixedDBkey()
			);
		}
		foreach( $aKeys as $sKey ) {
			BsCacheHelper::invalidateCache( $sKey );
		}
		return true;
	}
}