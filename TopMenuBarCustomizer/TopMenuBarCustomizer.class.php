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
* @version    2.22.0

* @package    Bluespice_Extensions
* @subpackage TopMenuBarCustomizer
* @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
* @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
* @filesource
*/

/**
 * v1.20.0
 * - MediaWiki I18N
 */

class TopMenuBarCustomizer extends BsExtensionMW {
	private $aOldApps = array();
	private $aApps = array();

	/**
	 * Constructor of TopMenuBarCustomizer class
	 */
	public function __construct() {
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'TopMenuBarCustomizer',
			EXTINFO::DESCRIPTION => 'Customize the Top Menu Links.',
			EXTINFO::AUTHOR      => 'Patric Wirth',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);

		$this->mExtensionKey = 'TopMenuBarCustomizer';
	}

	/**
	 * Initialization of TopMenuBarCustomizer class
	 */
	public function initExt() {
		//TODO: Add some error massages on article save (more than 5 entrys etc.)
		$this->setHook('BSBlueSpiceSkin:ApplicationList','onBlueSpiceSkinApplicationList', true);
		$this->setHook('BeforePageDisplay');
		$this->setHook('EditFormPreloadText');

		BsConfig::registerVar('MW::TopMenuBarCustomizer::NuberOfLevels',       2, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-topmenubarcustomizer-pref-NumberOfLevels' );
		BsConfig::registerVar('MW::TopMenuBarCustomizer::DataSourceTitle',     'TopBarMenu', BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING, 'bs-topmenubarcustomizer-pref-DataSourceTitle' );
		BsConfig::registerVar('MW::TopMenuBarCustomizer::NumberOfMainEntries', 10, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-topmenubarcustomizer-pref-NumberOfMainEntries', 'int' );
		BsConfig::registerVar('MW::TopMenuBarCustomizer::NumberOfSubEntries',  25, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-topmenubarcustomizer-pref-NumberOfSubEntries', 'int' );
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		$oOutputPage->addModules('ext.bluespice.topmenubarcustomizer');
		return true;
	}

	/**
	 * Hook-Handle for MW hook EditFormPreloadText
	 * @param string $sText
	 * @param Title $oTitle
	 * @return boolean - always true
	 */
	public function onEditFormPreloadText($sText, $oTitle) {
		if( !$oTitle->equals(Title::newFromText(BsConfig::get('MW::TopMenuBarCustomizer::DataSourceTitle'), NS_MEDIAWIKI)) ) return true;

		global $wgUser;
		$aApplications = BsConfig::get('MW::Applications');
		$sCurrentApplicationContext = BsConfig::get('MW::ApplicationContext');
		$aOut = array();
		wfRunHooks( 'BSBlueSpiceSkin:ApplicationList', array( &$aApplications, &$sCurrentApplicationContext, $wgUser, &$aOut, $this ) );

		foreach($aApplications as $aApplication) {
			$sText .= "*{$aApplication['name']}\n";
		}

		return true;
	}

	/**
	 * Hook-Handler for BSBlueSpiceSkin:ApplicationList
	 * @param Array $aApplications
	 * @param String $sCurrentApplicationContext
	 * @param User $wgUser
	 * @param Array $aOut
	 * @param Object $oSender
	 * @return boolean - false to stop BSBlueSpiceSkin:ApplicationList hook in skin
	 */
	public function onBlueSpiceSkinApplicationList( &$aApplications, &$sCurrentApplicationContext, $wgUser, &$aOut, $oSender){
		//re-run applications hook, so later called extensions can register applications after this method returns false
		if( $oSender === $this) return true;
		wfRunHooks( 'BSBlueSpiceSkin:ApplicationList', array( &$aApplications, &$sCurrentApplicationContext, $wgUser, &$aOut, $this ) );

		$sSourceTitle = BsConfig::get('MW::TopMenuBarCustomizer::DataSourceTitle');
		$oTopBarMenuTitle = Title::makeTitle( NS_MEDIAWIKI, $sSourceTitle );
		if( is_null($oTopBarMenuTitle ) || !$oTopBarMenuTitle->exists() ) return true;

		$newAppList = BsPageContentProvider::getInstance()->getContentFromTitle( $oTopBarMenuTitle );

		// force unset Applications by create an empty page
		if( $newAppList === "" ) {
			$aKeys = array_keys( $aApplications );
			foreach($aKeys as $key) {
				unset( $aApplications[$key] );
			}
			return false;
		}
		$this->aOldApps = $aApplications;
		$aLines = explode( "\n", trim( $newAppList ) );
		$this->aApps = $this->parseArticleContentLines( $aLines );
		
		$aOut[] = '<div id="bs-apps">';
		$aOut[] =	'<ul>';
		foreach( $this->aApps as $aApp ) {
			$oMainItem = new ViewTopMenuItemMain();
			$oMainItem->setLevel( $aApp['level'] );
			$oMainItem->setName( $aApp['name'] );
			$oMainItem->setLink( $aApp['url'] );
			$oMainItem->setDisplaytitle( $aApp['displaytitle'] );
			$oMainItem->setActive( $aApp['active'] );
			$oMainItem->setContainsActive( $aApp['containsactive'] );
			$oMainItem->setExternal( $aApp['external'] );
			if( !empty($aApp['children']) ) {
				$oMainItem->setChildren( $aApp['children'] );
			}
			$aOut[] = $oMainItem->execute();
		}
		$aOut[] =	'</ul>';
		$aOut[] = '</div>';

		return false;
	}

	/**
	 * Returns recursively all parsed menu items (apps)
	 * @param type $aLines
	 * @param type $aApps
	 * @param type $iPassed
	 * @return Array
	 */
	private function parseArticleContentLines( $aLines, $aApps = array(), $iPassed = 0 ) {
		$iAllowedLevels = BsConfig::get('MW::TopMenuBarCustomizer::NuberOfLevels');
		$iMaxEntrys = $iPassed === 0 ? BsConfig::get('MW::TopMenuBarCustomizer::NumberOfMainEntries') -1 : BsConfig::get('MW::TopMenuBarCustomizer::NumberOfSubEntries') -1;

		if($iAllowedLevels < 1 || $iMaxEntrys < 1) return $aApps;

		$iPassed++;
		$aChildLines = array();
		$i = 0;
		for( $i; $i < count($aLines); $i++ ) {
			$aLines[$i] = trim($aLines[$i]);
			//prevents from lines without * and list starts without parent item
			if ( strpos( $aLines[$i], '*' ) !== 0 || (strpos( $aLines[$i], '**' ) === 0 &&  $i == 0)) {
				continue;
			}

			if ( strpos( $aLines[$i], '**' ) === 0 ) {
				if($iPassed < $iAllowedLevels) {
					$aChildLines[] = substr($aLines[$i], 1);
				}
				continue;
			}
			if( !empty($aChildLines) ) {
				$iLastKey = key( array_slice( $aApps, -1, 1, TRUE ) );
				$aApps[$iLastKey]['children'] = $this->parseArticleContentLines( $aChildLines, array() ,$iPassed );
				foreach( $aApps[$iLastKey]['children'] as $aChildApps ) {
					if( !$aChildApps['active'] && !$aChildApps['containsactive'] ) continue;
					$aApps[$iLastKey]['containsactive'] = true;
					break;
				}
				$aChildLines = array();
			}
			
			if( count($aApps) > $iMaxEntrys) continue;

			$aApp = $this->parseSingleLine( substr($aLines[$i], 1) );
			if( empty($aApp) ) continue;
			
			$aApp['level'] = $iPassed;
			$aApps[] = $aApp;
		}
		//add childern to the last element
		if( !empty($aChildLines) ) {
			$iLastKey = key( array_slice( $aApps, -1, 1, TRUE ) );
			$aApps[$iLastKey]['children'] = $this->parseArticleContentLines( $aChildLines, array() ,$iPassed );
			foreach( $aApps[$iLastKey]['children'] as $aChildApps ) {
				if( !$aChildApps['active'] && !$aChildApps['containsactive'] ) continue;
				$aApps[$iLastKey]['containsactive'] = true;
				break;
			}
		}

		return $aApps;
	}
	
	/**
	 * Parses a single menu item
	 * @global Title $wgTitle
	 * @param String $sLine
	 * @return Array - Single parsed menu item (app)
	 */
	private function parseSingleLine( $sLine ) {
		global $wgTitle, $wgServer, $wgScriptPath;;
		$newApp = array(
			'name' => '',
			'url' => '',
			'displaytitle' => '',
			'active' => false,
			'containsactive' => false,
			'external' => false,
		);
		
		$aAppParts = explode( '|', trim ( $sLine ) );
		foreach( $aAppParts as $key => $val ) {
			$aAppParts[$key ] = trim( $val );
		}
		if( empty($aAppParts[0]) ) return array();
		$newApp['name'] = $aAppParts[0];

		if( !empty( $aAppParts[1] ) ) {
			$aParsedUrl = wfParseUrl( $aAppParts[1] );
			if( $aParsedUrl !== false ) {
				if(preg_match('# |\\*#',$aParsedUrl['host'])) {
					//$sParseError = $newApp; not in use
				}
				if( $aParsedUrl['scheme'] == 'http' || $aParsedUrl['scheme'] == 'https' ) {
					$sQuery = !empty( $aParsedUrl['query'] ) ? '?'.$aParsedUrl['query'] : '';
					$newApp['url'] = $aParsedUrl['scheme'].$aParsedUrl['delimiter'].$aParsedUrl['host'].$aParsedUrl['path'].$sQuery;
					$newApp['external'] = true;
				} 
			} else if( strpos($aAppParts[1], '?') === 0 ) { //?action=blog
				$newApp['url'] = $wgServer.$wgScriptPath.'/'.$aAppParts[1];
			} else {
				$oTitle = Title::newFromText( trim($aAppParts[1]) );
				if( is_null($oTitle) ) {
					//$sParseError = $newApp; not in use
				} else {
					$newApp['url'] = $oTitle->getFullURL();
					if( $oTitle->equals($wgTitle) ) {
						$newApp['active'] = true;
					}
				}
			}
		} else {
			$newApp['url'] = $wgServer.$wgScriptPath;
		}

		if( !empty( $aAppParts[2] ) ) {
			$newApp['displaytitle'] = $aAppParts[2];
		}

		//get old menu entries with the same id
		foreach($this->aOldApps as $key => $aOldApp) {
			if( $aOldApp['name'] == $newApp['name'] ) {
				if( empty($aAppParts[1]) ) {
					//no new url given - use old url
					$newApp['url'] = $aOldApp['url'];
				}
				if( empty($aAppParts[2]) ) {
					//no new display title - use old displaytitle
					$newApp['displaytitle'] = $aOldApp['displaytitle'];
				}
				break;
			}
		}

		return $newApp;
	}
}