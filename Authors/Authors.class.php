<?php
/**
 * Authors extension for BlueSpice
 *
 * Displays authors of an article with image.
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
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage Authors
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

//Last review MRG (30.06.11 10:25)

/**
 * Base class for Authors extension
 * @package BlueSpice_Extensions
 * @subpackage Authors
 */
class Authors extends BsExtensionMW {

	/**
	 * Initialization of Authors extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Hooks
		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );
		$this->setHook( 'BS:UserPageSettings', 'onUserPageSettings' );
		$this->setHook( 'PageContentSave' );

		BsConfig::registerVar( 'MW::Authors::Blacklist',   array( 'MediaWiki default' ), BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_STRING );
		BsConfig::registerVar( 'MW::Authors::Limit',       10,                           BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-authors-pref-limit', 'int' );
		BsConfig::registerVar( 'MW::Authors::MoreImage',   'more-users_v2.png',          BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING );
		BsConfig::registerVar( 'MW::Authors::Show',        true,                         BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-authors-pref-show', 'toggle' );

		$this->mCore->registerBehaviorSwitch( 'bs_noauthors' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object $oResponse reference
	 * $param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'switches' ) return true;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:authors';
		$oDescriptor->type = 'switch';
		$oDescriptor->name = 'NOAUTHORS';
		$oDescriptor->desc = wfMessage( 'bs-authors-switch-description' )->plain();
		$oDescriptor->code = '__NOAUTHORS__';
		$oDescriptor->previewable = false;
		$oResponse->result[] = $oDescriptor;

		return true;
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if ( $this->checkContext() === false ) return true;
		$oOutputPage->addModuleStyles('ext.bluespice.authors.styles');

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
		$oUserPageSettingsView = new ViewAuthorsUserPageProfileImageSetting();
		$oUserPageSettingsView->setCurrentUser( $oUser );
		$aSettingViews[] = $oUserPageSettingsView;
		return true;
	}

	/**
	 * Hook-Handler for 'SkinTemplateOutputPageBeforeExec'. Creates the authors list below an article.
	 * @param SkinTemplate $sktemplate a collection of views. Add the view that needs to be displayed
	 * @param BaseTemplate $tpl currently logged in user. Not used in this context.
	 * @return bool always true
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		if ( $this->checkContext() === false ) {
			return true;
		}

		$aDetails = array();
		$oAuthorsView = $this->getAuthorsViewForAfterContent( $sktemplate, $aDetails );
		$tpl->data['bs_dataAfterContent']['bs-authors'] = array(
			'position' => 10,
			'label' => wfMessage( 'bs-authors-title', $aDetails['count'], $aDetails['username'] )->text(),
			'content' => $oAuthorsView
		);
		return true;
	}

	private function getAuthorsViewForAfterContent( $oSkin, &$aDetails ) {
		$oTitle = $oSkin->getTitle();

		//Read in config variables
		$iLimit = BsConfig::get( 'MW::Authors::Limit' );
		$aBlacklist = BsConfig::get( 'MW::Authors::Blacklist' );
		$sMoreImage = BsConfig::get( 'MW::Authors::MoreImage' );

		$sPrintable = $oSkin->getRequest()->getVal( 'printable', 'no' );
		$iArticleId = $oTitle->getArticleID();

		$sKey = BsCacheHelper::getCacheKey( 'BlueSpice', 'Authors', $iArticleId );
		$aData = BsCacheHelper::get( $sKey );

		if ( $aData !== false ) {
			wfDebugLog( 'BsMemcached', __CLASS__ . ': Fetched AuthorsView and Details from cache' );
			$oAuthorsView = $aData['view'];
			$aDetails = $aData['details'];
		} else {
			wfDebugLog( 'BsMemcached', __CLASS__ . ': Fetching AuthorsView and Details from DB' );
			//HINT: Maybe we want to use MW interface Article::getContributors() to have better caching
			//HINT2: Check if available in MW 1.17+
			// SW: There is still no caching in WikiPage::getContributors()! 17.07.2014
			$dbr = wfGetDB( DB_REPLICA );
			$res = $dbr->select(
					array( 'revision' ), array( 'rev_user_text', 'MAX(rev_timestamp) AS ts' ), array( 'rev_page' => $iArticleId ), __METHOD__, array(
				'GROUP BY' => 'rev_user_text',
				'ORDER BY' => 'ts DESC'
					)
			);

			if ( $res->numRows() == 0 ) {
				return true;
			}

			$oAuthorsView = new ViewAuthors();
			if ( $sPrintable == 'yes' ) {
				$oAuthorsView->setOption( 'print', true );
			}

			$aUserNames = array();
			foreach ( $res as $row ) {
				$aUserNames[] = $row->rev_user_text;
			}

			$iCount = count( $aUserNames );
			$aDetails['count'] = $iCount;

			$sOriginatorUserName = $oTitle->getFirstRevision()->getUserText();
			$sOriginatorUserName = $this->checkOriginatorForBlacklist(
				$sOriginatorUserName, $oTitle->getFirstRevision(), $aBlacklist
			);

			if ( $iCount > 1 ) {
				array_unshift( $aUserNames, $sOriginatorUserName );
				$iCount++;
			}

			$bAddMore = false;
			if ( $iCount > $iLimit ) {
				$bAddMore = true;
			}

			$i = 0;
			$iItems = 0;
			$aDetails['username'] = '';
			while ( $i < $iCount ) {
				if ( $iItems > $iLimit ) {
					break;
				}
				$sUserName = $aUserNames[$i];

				if ( User::isIP( $sUserName ) ) {
					unset( $aUserNames[$i] );
					$i++;
					continue;
				}

				$oAuthorUser = User::newFromName( $sUserName );

				if ( !is_object( $oAuthorUser ) || in_array( $oAuthorUser->getName(), $aBlacklist ) ) {
					unset( $aUserNames[$i] );
					$i++;
					continue;
				}
				$aDetails['username'] = $oAuthorUser->getName();

				$oUserMiniProfileView = BsCore::getInstance()->getUserMiniProfile(
					$oAuthorUser
				);
				if ( $sPrintable == 'yes' ) {
					$oUserMiniProfileView->setOption( 'print', true );
				}

				$iItems++;
				$i++;
				$oAuthorsView->addItem( $oUserMiniProfileView );
			}

			if ( $bAddMore === true ) {
				$oMoreAuthorsView = BsCore::getInstance()->getUserMiniProfile(
					new User()
				);
				$oMoreAuthorsView->setOption( 'userdisplayname', wfMessage( 'bs-authors-show-all-authors' )->plain() );
				$oMoreAuthorsView->setOption( 'userimagesrc', $this->getImagePath( true ) . '/' . $sMoreImage );
				$oMoreAuthorsView->setOption( 'linktargethref', $oTitle->getLocalURL( array( 'action' => 'history' ) ) );
				$oMoreAuthorsView->setOption( 'classes', array( 'bs-authors-more-icon' ) );
				if ( $sPrintable == 'yes' ) {
					$oMoreAuthorsView->setOption( 'print', true );
				}

				$oAuthorsView->addItem( $oMoreAuthorsView );
			}

			$dbr->freeResult( $res );
			BsCacheHelper::set(
				$sKey,
				array(
					'view' => $oAuthorsView,
					'details' => $aDetails
				)
			);
		}

		return $oAuthorsView;
	}

	/**
	 * Walks the line of revisions to find first editor that is not on blacklist
	 * @param string $sOriginatorUserName
	 * @param Revision $oRevision
	 * @return string The originators username
	 */
	private function checkOriginatorForBlacklist( $sOriginatorUserName, $oRevision, $aBlacklist ) {
		if( $oRevision instanceof Revision == false ) {
			return $sOriginatorUserName;
		}
		$sOriginatorUserName = $oRevision->getUserText();
		if(in_array( $sOriginatorUserName, $aBlacklist) ) {
			return $this->checkOriginatorForBlacklist($sOriginatorUserName, $oRevision->getNext(), $aBlacklist);
		}
		return $sOriginatorUserName;
	}

	/**
	 * Checks wether to set Context or not.
	 * @return bool
	 */
	private function checkContext() {
		if ( BsConfig::get( 'MW::Authors::Show' ) === false ) {
			return false;
		}

		$oTitle = $this->getTitle();
		if ( !is_object( $oTitle ) ) {
			return false;
		}

		if ( !$oTitle->exists() ) {
			return false;
		}

		// Do only display when user is allowed to read
		if ( !$oTitle->userCan( 'read' ) ) {
			return false;
		}

		// Do only display in view mode
		if ( $this->getRequest()->getVal( 'action', 'view' ) != 'view' ) {
			return false;
		}

		// Do not display on SpecialPages, CategoryPages or ImagePages
		if ( in_array( $oTitle->getNamespace(), array( NS_SPECIAL, NS_CATEGORY, NS_FILE ) ) ) {
			return false;
		}

		// Do not display if __NOAUTHORS__ keyword is found
		$vNoAuthors = BsArticleHelper::getInstance( $oTitle )->getPageProp( 'bs_noauthors' );
		if( $vNoAuthors === '' ) {
			return false;
		}

		return true;
	}

	/**
	 * Invalidates cache for authors
	 * @param WikiPage $wikiPage
	 * @param User $user
	 * @param Content $content
	 * @param type $summary
	 * @param type $isMinor
	 * @param type $isWatch
	 * @param type $section
	 * @param type $flags
	 * @param Status $status
	 * @return boolean
	 */
	public static function onPageContentSave( $wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $status ) {
		BsCacheHelper::invalidateCache( BsCacheHelper::getCacheKey( 'BlueSpice', 'Authors', $wikiPage->getTitle()->getArticleID() ) );
		return true;
	}
}
