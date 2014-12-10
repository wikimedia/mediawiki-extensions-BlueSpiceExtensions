<?php
/**
* This file is part of blue spice for MediaWiki.
*
* Use MediaWiki:TopBarMenu to customize the TopMenuBar
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
* This file is part of BlueSpice for MediaWiki
* For further information visit http://www.blue-spice.org
*
* @author     Patric Wirth <wirth@hallowelt.biz>
* @version    2.23.0
* @package    Bluespice_Extensions
* @subpackage TopMenuBarCustomizer
* @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
* @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
* @filesource
*/

/**
 * v1.20.0
 * - MediaWiki I18N
 * v2.23.0
 * - Complete makeover
 */

class TopMenuBarCustomizer extends BsExtensionMW {
	/**
	 *
	 * @var array
	 */
	public static $aNavigationSiteTemplate = array(
		'id' => '',
		'href' => '',
		'text' => '',
		'active' => false,
		'level' => 1,
		'containsactive' => false,
		'external' => false,
	);

	/**
	 *
	 * @var array
	 */
	private static $aNavigationSites = null;

	/**
	 * Constructor of TopMenuBarCustomizer class
	 */
	public function __construct() {
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'TopMenuBarCustomizer',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-topmenubarcustomizer-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Patric Wirth',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.23.0' )
		);

		$this->mExtensionKey = 'TopMenuBarCustomizer';
	}

	/**
	 * Initialization of TopMenuBarCustomizer class
	 */
	public function initExt() {
		//TODO: Add some error massages on article save (more than 5 entrys etc.)
		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'EditFormPreloadText' );

		$this->setHook( 'PageContentSaveComplete', 'invalidateCacheOnArticleChange' );
		$this->setHook( 'ArticleDeleteComplete', 'invalidateCacheOnArticleChange' );
		$this->setHook( 'TitleMoveComplete', 'invalidateCacheOnTitleChange' );

		BsConfig::registerVar('MW::TopMenuBarCustomizer::NuberOfLevels', 2, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-topmenubarcustomizer-pref-numberoflevels' );
		BsConfig::registerVar('MW::TopMenuBarCustomizer::NumberOfMainEntries', 10, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-topmenubarcustomizer-pref-numberofmainentries', 'int' );
		BsConfig::registerVar('MW::TopMenuBarCustomizer::NumberOfSubEntries', 25, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-topmenubarcustomizer-pref-numberofsubentries', 'int' );
	}

	/**
	 * Getter for the $aNavigationSites array - either from hook, TopBarMenu title or cache
	 * @global string $wgSitename
	 * @return array
	 */
	public static function getNavigationSites() {
		if( !is_null(self::$aNavigationSites) ) return self::$aNavigationSites;

		$sKey = BsExtensionManager::getExtension('TopMenuBarCustomizer')
					->getCacheKey( 'NavigationSitesData' );

		self::$aNavigationSites = BsCacheHelper::get( $sKey );
		if( self::$aNavigationSites !== false ) {
			return self::$aNavigationSites;
		}
		self::$aNavigationSites = array();

		$oTopBarMenuTitle = Title::makeTitle( NS_MEDIAWIKI, 'TopBarMenu' );

		if( !is_null($oTopBarMenuTitle) && $oTopBarMenuTitle->exists() ) {
			$sContent = BsPageContentProvider::getInstance()
				->getContentFromTitle( $oTopBarMenuTitle );

			// Force unset of all menu items by creating an empty page
			if( !empty($sContent) ) {
				self::$aNavigationSites = TopMenuBarCustomizerParser::getNavigationSites();
			}
			BsCacheHelper::set( $sKey , self::$aNavigationSites, 60*1440 );//max cache time 24h
			return self::$aNavigationSites;
		}

		global $wgSitename;
		$oCurrentTitle = RequestContext::getMain()->getTitle();
		$oMainPage = Title::newMainPage();

		self::$aNavigationSites[] = array(
			'id' => 'wiki',
			'href' => $oMainPage->getFullURL(),
			'text' => $wgSitename,
			'active' => $oCurrentTitle->equals( $oMainPage ),
			'level' => 1,
			'containsactive' => false,
			'external' => false,
			'children' => array(),
		);

		wfRunHooks('BSTopMenuBarCustomizerRegisterNavigationSites', array( &self::$aNavigationSites ));

		BsCacheHelper::set( $sKey , self::$aNavigationSites, 60*1440 );//max cache time 24h
		return self::$aNavigationSites;
	}

	/**
	 * Hook-Handle for MW hook EditFormPreloadText
	 * @param string $sText
	 * @param Title $oTitle
	 * @return boolean - always true
	 */
	public function onEditFormPreloadText( &$sText, $oTitle ) {
		$oTopBarMenuTitle = Title::makeTitle( NS_MEDIAWIKI, 'TopBarMenu' );
		if( !$oTopBarMenuTitle || !$oTitle->equals($oTopBarMenuTitle) ) return true;

		$aNavigationSites = self::getNavigationSites();
		if( empty($aNavigationSites) ) {
			return true;
		}

		$sText = TopMenuBarCustomizerParser::toWikiText( $aNavigationSites, $sText );

		return true;
	}

	/**
	 * Hook-Handle for MW hook BeforePageDisplay - Sets modules if needed
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean - always true
	 */
	public function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$aNavigationSites = self::getNavigationSites();
		if( empty($aNavigationSites) ) {
			return true;
		}

		$out->addModules( 'ext.bluespice.topmenubarcustomizer' );
		$out->addModuleStyles( 'ext.bluespice.topmenubarcustomizer.styles' );
		return true;
	}

	/**
	 * Overrides existing bs_navigation_topbar
	 * @param SkinTemplate $sktemplate
	 * @param BaseTemplate $tpl
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ){
		if( !isset($tpl->data['bs_navigation_sites']) ) return true;

		$aNavigationSites = self::getNavigationSites();
		if( empty($aNavigationSites) ) {
			unset( $tpl->data['bs_navigation_sites'] );
			return true;
		}

		wfRunHooks('BSTopMenuBarCustomizerBeforeRenderNavigationSites', array( &$aNavigationSites ));

		$aOut= array();
		$aOut[] = HTML::openElement( 'ul' );
		foreach( self::getNavigationSites() as $aApp ) {
			$aApp = array_merge(self::$aNavigationSiteTemplate, $aApp);
			$oMainItem = new ViewTopMenuItem();
			$oMainItem->setLevel( $aApp['level'] );
			$oMainItem->setName( $aApp['id'] );
			$oMainItem->setLink( $aApp['href'] );
			$oMainItem->setDisplaytitle( $aApp['text'] );
			$oMainItem->setActive( $aApp['active'] );
			$oMainItem->setContainsActive( $aApp['containsactive'] );
			$oMainItem->setExternal( $aApp['external'] );
			if( !empty($aApp['children']) ) {
				$oMainItem->setChildren( $aApp['children'] );
			}
			$aOut[] = $oMainItem->execute();
		}
		$aOut[] = HTML::closeElement( 'ul' );

		$tpl->data['bs_navigation_sites'] = implode( "\n", $aOut );

		return true;
	}

	public function invalidateCacheOnArticleChange( $oArticle ) {
		return $this->invalidateCacheOnTitleChange( $oArticle->getTitle() );
	}

	public function invalidateCacheOnTitleChange( $oTitle ) {
		if( !$oTitle->equals(Title::makeTitle(NS_MEDIAWIKI, 'TopBarMenu')) ) return true;
		$this->invalidateCache();
		return true;
	}

	public function invalidateCache() {
		BsCacheHelper::invalidateCache(
			$this->getCacheKey( 'NavigationSitesData' )
		);
	}
}