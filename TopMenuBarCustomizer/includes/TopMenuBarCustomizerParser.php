<?php
/**
 * TopMenuBarCustomizerParser class for extension TopMenuBarCustomizer
 *
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
 * @package    BlueSpice_Extensions
 * @subpackage TopMenuBarCustomizer
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * TopMenuBarCustomizerParser class for TopMenuBarCustomizer extension
 * @package BlueSpice_Extensions
 * @subpackage TopMenuBarCustomizer
 */
class TopMenuBarCustomizerParser {
	private static $aNavigationSites = null;

	/**
	 * Getter for $aNavigationSites array
	 * @param boolean $bForceReload
	 * @return array
	 */
	public static function getNavigationSites( $bForceReload = false ) {
		if( !$bForceReload && !is_null(self::$aNavigationSites) ) return self::$aNavigationSites;
		self::$aNavigationSites = array();

		$oTopBarMenuTitle = Title::makeTitle( NS_MEDIAWIKI, 'TopBarMenu' );
		if( is_null($oTopBarMenuTitle ) || !$oTopBarMenuTitle->exists() ) {
			return self::$aNavigationSites;
		}

		$sContent = BsPageContentProvider::getInstance()
			->getContentFromTitle( $oTopBarMenuTitle );

		$aLines = explode( "\n", trim( $sContent ) );

		$iMaxMainEntries = BsConfig::get('MW::TopMenuBarCustomizer::NumberOfMainEntries');
		$iMaxSubEntries = BsConfig::get('MW::TopMenuBarCustomizer::NumberOfSubEntries');
		$iAllowedLevels = BsConfig::get('MW::TopMenuBarCustomizer::NuberOfLevels');

		self::$aNavigationSites = self::parseArticleContentLines(
			$aLines,
			$iAllowedLevels,
			$iMaxMainEntries,
			$iMaxSubEntries
		);

		return self::$aNavigationSites;
	}

	/**
	 * Returns recursively all parsed menu items
	 * TODO: Clean up
	 * @param type $aLines
	 * @param type $aApps
	 * @param type $iPassed
	 * @return Array
	 */
	private static function parseArticleContentLines( $aLines, $iAllowedLevels = 2, $iMaxMainEntries = 5, $iMaxSubEntries = 20, $aApps = array(), $iPassed = 0 ) {
		$iMaxEntrys = ( $iPassed === 0 ) ? $iMaxMainEntries -1 : $iMaxSubEntries -1;

		if ( $iAllowedLevels < 1 || $iMaxEntrys < 1 ) {
			return $aApps;
		}

		$iPassed++;
		$aChildLines = array();
		$iCount = count($aLines);
		$i = 0;
		for ( $i; $i < $iCount; $i++ ) {
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
			if ( !empty( $aChildLines ) ) {
				$iLastKey = key( array_slice( $aApps, -1, 1, TRUE ) );
				$aApps[$iLastKey]['children'] = self::parseArticleContentLines(
					$aChildLines,
					$iAllowedLevels,
					$iMaxMainEntries,
					$iMaxSubEntries,
					array(),
					$iPassed
				);
				foreach( $aApps[$iLastKey]['children'] as $aChildApps ) {
					if( !$aChildApps['active'] && !$aChildApps['containsactive'] ) {
						continue;
					}
					$aApps[$iLastKey]['containsactive'] = true;
					break;
				}
				$aChildLines = array();
			}

			if ( count($aApps) > $iMaxEntrys) {
				continue;
			}

			$aApp = self::parseSingleLine( substr($aLines[$i], 1) );
			if( empty($aApp) ) {
				continue;
			}

			$aApp['level'] = $iPassed;
			$aApps[] = $aApp;
		}
		//add childern to the last element
		if( !empty( $aChildLines ) ) {
			$iLastKey = key( array_slice( $aApps, -1, 1, true ) );
			$aApps[$iLastKey]['children'] = self::parseArticleContentLines( $aChildLines,
				$iAllowedLevels,
				$iMaxMainEntries,
				$iMaxSubEntries,
				array(),
				$iPassed
			);
			foreach ( $aApps[$iLastKey]['children'] as $aChildApps ) {
				if ( !$aChildApps['active'] && !$aChildApps['containsactive'] ) {
					continue;
				}
				$aApps[$iLastKey]['containsactive'] = true;
				break;
			}
		}

		return $aApps;
	}

	/**
	 * Parses a single menu item
	 * TODO: Clean up
	 * @global Title $wgTitle
	 * @param String $sLine
	 * @return Array - Single parsed menu item (app)
	 */
	public static function parseSingleLine( $sLine ) {
		global $wgTitle, $wgServer, $wgScriptPath;
		$newApp = TopMenuBarCustomizer::$aNavigationSiteTemplate;

		$aAppParts = explode( '|', trim ( $sLine ) );
		foreach( $aAppParts as $key => $val ) {
			$aAppParts[$key ] = trim( $val );
		}
		if( empty($aAppParts[0]) ) {
			return array();
		}
		$newApp['id'] = $aAppParts[0];

		if( !empty( $aAppParts[1] ) ) {
			$aParsedUrl = wfParseUrl( $aAppParts[1] );
			if( $aParsedUrl !== false ) {
				if(preg_match('# |\\*#',$aParsedUrl['host'])) {
					//TODO: Use status ojb on BeforeArticleSave to detect parse errors
				}
				if( $aParsedUrl['scheme'] == 'http' || $aParsedUrl['scheme'] == 'https' ) {
					$sQuery = !empty( $aParsedUrl['query'] ) ? '?'.$aParsedUrl['query'] : '';
					if( !isset($aParsedUrl['path']) ) $aParsedUrl['path'] = '';
					$newApp['href'] = $aParsedUrl['scheme'].$aParsedUrl['delimiter'].$aParsedUrl['host'].$aParsedUrl['path'].$sQuery;
					$newApp['external'] = true;
				}
			} else if( strpos($aAppParts[1], '?') === 0 ) { //?action=blog
				$newApp['href'] = $wgServer.$wgScriptPath.'/'.$aAppParts[1];
			} else {
				$oTitle = Title::newFromText( trim($aAppParts[1]) );
				if( is_null($oTitle) ) {
					//TODO: Use status ojb on BeforeArticleSave to detect parse errors
				} else {
					$newApp['href'] = $oTitle->getFullURL();
					if( $oTitle->equals($wgTitle) ) {
						$newApp['active'] = true;
					}
				}
			}
		} else {
			$newApp['href'] = $wgServer.$wgScriptPath;
		}

		if( !empty( $aAppParts[2] ) ) {
			$newApp['text'] = $aAppParts[2];
		}

		return $newApp;
	}

	/**
	 * Returns wikitext list from recursively processed array
	 * @global string $wgArticlePath
	 * @param array $aNavigationSites
	 * @param string $sWikiText
	 * @param string $sPrefix
	 * @return type
	 */
	public static function toWikiText( $aNavigationSites, $sWikiText = '', $sPrefix = '*' ) {
		foreach( $aNavigationSites as $aNavigationSite ) {
			$sText = $sHref = '';

			if( !empty($aNavigationSite['href']) ) {
				$sHref = '|';

				if( !isset($aNavigationSite['external']) || !$aNavigationSite['external'] ) {
					//extract Title from url - maybe not 100% accurate
					global $wgArticlePath;
					$aInternalUrl = explode(
						substr($wgArticlePath, 0, -2), //remove $1
						'A'.$aNavigationSite['href'] //Added A - url could be relative
					);

					if( !isset($aInternalUrl[1]) ) {
						$sHref .= $aNavigationSite['href'];
					} else {
						$sHref .= $aInternalUrl[1];
						/* TODO: Remove query - not yet needed
						if( strpos($aInternalUrl[1], '?') !== false ) {
							$sHref .= substr(
								$aInternalUrl[1],
								0,
								strpos( $aInternalUrl[1], '?')
							);
						} elseif( strpos($aInternalUrl[1], '&' ) !== false) {
							$sHref .= substr(
								$aInternalUrl[1],
								0,
								strpos( $aInternalUrl[1], '&')
							);
						}*/
					}
				} else {
					$sHref .= $aNavigationSite['href'];
				}
				if( !empty($aNavigationSite['text']) ) {
					$sText = '|'.$aNavigationSite['text'];
				}
			}

			$sWikiText .= "$sPrefix{$aNavigationSite['id']}$sHref$sText\n";
			if( empty($aNavigationSite['children']) ) continue;

			$sWikiText = self::toWikiText($aNavigationSite['children'], $sWikiText, "*$sPrefix");
		}
		return $sWikiText;
	}
}