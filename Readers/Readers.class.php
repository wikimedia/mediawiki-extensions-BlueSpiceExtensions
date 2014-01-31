<?php
/**
 * Readers for BlueSpice
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
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    2.22.0
 * @package    BlueSpice_Extensions
 * @subpackage Readers
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v2.22.0
 * - initial release
*/

/**
 * Readers extension
 * @package BlueSpice_Extensions
 * @subpackage Readers
 */
class Readers extends BsExtensionMW {

	/**
	 * Contructor of the Readers class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'Readers',
			EXTINFO::DESCRIPTION => 'Creates a list of the people who read an article.',
			EXTINFO::AUTHOR      => 'Stephan Muggli',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
										'bluespice' => '2.22.0',
										)
		);
		$this->mExtensionKey = 'MW::Readers';

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of ExtensionTemplate extension
	 */
	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSBlueSpiceSkinBeforeArticleHeadline' );
		$this->setHook( 'BSBlueSpiceSkinAfterArticleContent' );
		$this->setHook( 'SkinTemplateContentActions' );

		$this->mCore->registerPermission( 'viewreaders' );

		BsConfig::registerVar( 'MW::Readers::UpOrDown', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-readers-pref-upordown', 'toggle' );
		BsConfig::registerVar( 'MW::Readers::Active', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-readers-pref-active', 'toggle' );
		BsConfig::registerVar( 'MW::Readers::NumOfReaders', 10, BsConfig::TYPE_INT|BsConfig::LEVEL_PUBLIC, 'bs-readers-pref-numofreaders', 'int' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for Hook 'LoadExtensionSchemaUpdates'
	 * @param object §updater Updater
	 * @return boolean Always true
	 */
	public static function getSchemaUpdates( $updater ) {
		$updater->addExtensionTable(
			'bs_readers',
			__DIR__.DS.'db'.DS.'readers.sql'
		);
		$updater->addExtensionField(
			'bs_readers',
			'readers_ts',
			__DIR__.DS.'db/mysql/readers.patch.readers_ts.sql'
		);

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
		$oOutputPage->addModuleStyles( 'ext.bluespice.readers.styles' );
		$this->insertTrace();

		return true;
	}

	/**
	 * Hook-Handler for Hook 'ParserFirstCallInit'
	 * @param object $oParser Parser
	 * @return boolean Always true
	 */
	public function insertTrace() {
		$oUser = $this->getUser();
		$oTitle = $this->getTitle();
		$oRevision = Revision::newFromTitle( $oTitle );

		if ( !( $oRevision instanceof Revision ) ) return true;

		$oDbw = wfGetDB( DB_MASTER );

		$oDbw->delete(
			'bs_readers',
			array(
				'readers_user_id' => $oUser->getId(),
				'readers_page_id' => $oTitle->getArticleID()
			)
		);

		$aNewRow = array();
		$aNewRow['readers_id'] = 0;
		$aNewRow['readers_user_id'] = $oUser->getId();
		$aNewRow['readers_user_name'] = $oUser->getName();
		$aNewRow['readers_page_id'] = $oTitle->getArticleID();
		$aNewRow['readers_rev_id'] = $oRevision->getId();
		$aNewRow['readers_ts'] = wfTimestampNow();

		$oDbw->insert( 'bs_readers', $aNewRow );

		return true;
	}

	/**
	 * MediaWiki ContentActions hook. For more information please refer to <mediawiki>/docs/hooks.txt
	 * @param Array $aContentActions This array is used within the skin to render the content actions menu
	 * @return Boolean Always true for it is a MediwWiki Hook callback.
	 */
	public function onSkinTemplateContentActions( &$aContentActions ) {
		if ( $this->checkContext() === false ) return true;
		//Check if menu entry has to be displayed
		$oCurrentUser = $this->getUser();
		if ( $oCurrentUser->isLoggedIn() === false ) return true;

		$oCurrentTitle = $this->getTitle();
		if ( $oCurrentTitle->exists() === false ) return true;
		if ( $oCurrentTitle->getNamespace() === NS_SPECIAL ) return true;
		if ( !$oCurrentTitle->userCan( 'viewreaders' ) ) return true;

		$oSpecialPageWithParam = SpecialPage::getTitleFor( 'Readers', $oCurrentTitle->getPrefixedText());

		//Add menu entry
		$aContentActions['readersbutton'] = array(
			'class' => false,
			'text'  => wfMessage( 'bs-readers-contentactions-label' )->plain(),
			'href'  => $oSpecialPageWithParam->getLocalURL(),
			'id'    => 'ca-readers'
		);

		return true;
	}

	/**
	 * Hook-Handler for 'BSBlueSpiceSkinBeforeArticleContent'. Creates the StateBar. on articles.
	 * @param array $aViews Array of views to be rendered in skin
	 * @param User $oUser Current user object
	 * @param Title $oTitle Current title object
	 * @return bool Always true to keep hook running.
	 */
	public function onBSBlueSpiceSkinBeforeArticleHeadline( &$aViews, $oUser, $oTitle ) {
		if ( $this->checkContext() === false ) return true;
		if ( !$oTitle->userCan( 'viewreaders' ) ) return true;
		if ( BsConfig::get( 'MW::Readers::UpOrDown' ) === false ) return true;

		$oViewReaders = $this->generateViewReaders( $oTitle );
		array_unshift( $aViews, $oViewReaders );

		return true;
	}

	/**
	 * Hook-Handler for 'BSBlueSpiceSkinAfterArticleContent'. Creates the Readers list below an article.
	 * @param array $aViews Array of views to be rendered in skin
	 * @param User $oUser Current user object
	 * @param Title $oTitle Current title object
	 * @return Boolean Always true to keep hook running.
	 */
	public function onBSBlueSpiceSkinAfterArticleContent( &$aViews, $oUser, $oTitle ) {
		if ( $this->checkContext() === false ) return true;
		if ( !$oTitle->userCan( 'viewreaders' ) ) return true;
		if ( BsConfig::get( 'MW::Readers::UpOrDown' ) === true ) return true;

		$oViewReaders = $this->generateViewReaders( $oTitle );
		$aViews[] = $oViewReaders;

		return true;
	}

	/**
	 * Generates a Readers view
	 * @param Title $oTitle Current title object
	 * @return View Readers view
	 */
	private function generateViewReaders( $oTitle ) {
		$oViewReaders = null;
		$oDbr = wfGetDB( DB_SLAVE );
		$res = $oDbr->select(
				array( 'bs_readers' ),
				array( 'readers_user_id', 'MAX(readers_ts) as readers_ts' ),
				array( 'readers_page_id' => $oTitle->getArticleID() ),
				__METHOD__,
				array(
					'GROUP BY' => 'readers_user_id',
					'ORDER BY' => 'MAX(readers_ts) DESC',
					'LIMIT' => BsConfig::get( 'MW::Readers::NumOfReaders' )
				)
		);

		if ( $oDbr->numRows( $res ) > 0 ) {
			$aParams = array();
			$aParams['width']  = BsConfig::get( 'MW::Authors::ImageWidth' );
			$aParams['height'] = BsConfig::get( 'MW::Authors::ImageHeight' );

			$oViewReaders = new ViewReaders();
			while ( $row = $oDbr->fetchObject( $res ) ) {
				$oUser = User::newFromId( (int)$row->readers_user_id );

				$oUserMiniProfile = $this->mCore->getUserMiniProfile( $oUser, $aParams );
				$oViewReaders->addItem( $oUserMiniProfile );
			}
		}
		$oDbr->freeResult( $res );

		return $oViewReaders;
	}

	/**
	 * Get the Users for specialpage, called via ajax
	 * @param string $sOutput Output to return
	 * @return bool Always true
	 */
	public static function getUsers( $sPage ) {
		$oTitle = Title::newFromText( $sPage );
		if ( !$oTitle->exists() ) return json_encode( array() );
		$iArticleID = $oTitle->getArticleID();

		$oStoreParams = BsExtJSStoreParams::newFromRequest();
		$iLimit     = $oStoreParams->getLimit();
		$iStart     = $oStoreParams->getStart();
		$sSort      = $oStoreParams->getSort( 'MAX(readers_ts)' );
		$sDirection = $oStoreParams->getDirection();

		if ( $sSort == 'user_page' ) $sSort = 'readers_user_name';

		$oDbr = wfGetDB( DB_SLAVE );
		$res = $oDbr->select(
				array( 'bs_readers' ),
				array( 'readers_user_id', 'MAX(readers_ts) as readers_ts' ),
				array( 'readers_page_id' => $iArticleID ),
				__METHOD__,
				array(
					'GROUP BY' => 'readers_user_id',
					'ORDER BY' => '' . $sSort . ' ' . $sDirection .'',
					'LIMIT'    => $iLimit,
					'OFFSET'   => $iStart
				)
		);

		$aUsers = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			$aParams = array();
			foreach ( $res as $row ) {
				$oUser = User::newFromId( (int)$row->readers_user_id );
				$oTitle = Title::makeTitle( NS_USER, $oUser->getName() );
				$oUserMiniProfile = BsCore::getInstance()->getUserMiniProfile( $oUser, $aParams );

				$sImage = $oUserMiniProfile->getUserImageSrc();
				if ( BsExtensionManager::isContextActive( 'MW::SecureFileStore::Active' ) )
					$sImage = SecureFileStore::secureStuff( $sImage, true );

				$aTmpUser = array();
				$aTmpUser['user_image'] = $sImage;
				$aTmpUser['user_name'] = $oUser->getName();
				$aTmpUser['user_page'] = $oTitle->getLocalURL();
				$aTmpUser['user_readers'] = SpecialPage::getTitleFor( 'Readers', $oTitle->getPrefixedText() )->getLocalURL();
				$aTmpUser['user_ts'] = $row->readers_ts;
				$aTmpUser['user_date'] = RequestContext::getMain()->getLanguage()->timeanddate( $row->readers_ts );

				$aUsers['users'][] = $aTmpUser;
			}
		}
		$rowCount = $oDbr->select(
				'bs_readers',
				'readers_user_id',
				array(
					'readers_page_id' => $iArticleID
				),
				__METHOD__,
				array(
					'GROUP BY' => 'readers_user_id'
				)
		);
		$aUsers['totalCount'] = $oDbr->numRows( $rowCount );

		return json_encode( $aUsers );
	}

	/**
	 * Checks wether to set Context or not.
	 * @return bool
	 */
	public function checkContext() {
		$oTitle = $this->getTitle();
		$oUser = $this->getUser();

		if ( wfReadOnly() ) return false;

		if ( BsConfig::get( 'MW::Readers::Active' ) == false ) return false;

		if ( is_null( $oTitle ) ) return false;

		if ( !$oTitle->exists() ) return false;

		if ( $oUser->isAnon() || User::isIP( $oUser->getName() ) ) return false;

		// Do only display when user is allowed to read
		if ( !$oTitle->userCan( 'read' ) ) return false;

		// Do only display in view mode
		if ( $this->getRequest()->getVal( 'action', 'view' ) !== 'view' ) return false;

		// Do not display on SpecialPages, CategoryPages or ImagePages
		if ( in_array( $oTitle->getNamespace(), array( NS_SPECIAL, NS_CATEGORY, NS_FILE, NS_MEDIAWIKI ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the pages for specialpage, called via ajax
	 * @param string $sOutput Output to return
	 * @return bool Always true
	 */
	public static function getData( $iUserID ) {
		$oDbr = wfGetDB( DB_SLAVE );

		$oStoreParams = BsExtJSStoreParams::newFromRequest();
		$iLimit = $oStoreParams->getLimit();
		$iStart = $oStoreParams->getStart();
		$sSort = $oStoreParams->getSort( 'MAX(readers_ts)' );

		if ( $sSort == 'user_page' ) $sSort = 'readers_user_name';

		$res = $oDbr->select(
				array( 'bs_readers', 'page' ),
				array( 'readers_page_id', 'MAX(readers_ts) as readers_ts' ),
				array( 'readers_user_id' => $iUserID ),
				__METHOD__,
				array(
					'GROUP BY' => 'readers_page_id',
					'ORDER BY' => 'MAX(readers_ts) DESC',
					'LIMIT' => $iLimit,
					'OFFSET' => $iStart
				),
				array( 'page' => array( 'INNER JOIN', 'readers_page_id = page_id' ) )
		);

		$aPages = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->readers_page_id );

				$aTmpPage = array();
				$aTmpPage['pv_page'] = $oTitle->getLocalURL();
				$aTmpPage['pv_page_title'] = $oTitle->getPrefixedText();
				$aTmpPage['pv_ts'] = RequestContext::getMain()->getLanguage()->timeanddate( $row->readers_ts );

				$aPages['page'][] = $aTmpPage;
			}
		}
		$oDbr->freeResult( $res );

		$rowCount = $oDbr->select(
				'bs_readers',
				'readers_page_id',
				array( 'readers_user_id' => $iUserID ),
				__METHOD__,
				array( 'GROUP BY' => 'readers_page_id' )
		);
		$aPages['totalCount'] = $oDbr->numRows( $rowCount );
		$oDbr->freeResult( $rowCount );

		return json_encode( $aPages );
	}

}