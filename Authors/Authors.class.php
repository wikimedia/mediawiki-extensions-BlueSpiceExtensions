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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @version    1.22.0
 * @version    $Id: Authors.class.php 9931 2013-06-25 15:39:28Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage Authors
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
  * v1.20.0
  *
  * v1.0.0
  * - Stable state
  * - Adjusted to fit Functional Desgin
  * v0.3
  * - added view class
  * v0.2.1
  * - Removed DEFINEs and added them to standard config.
  * - Little code refactoring / beautifing
  * v0.2
  * - output is created directly as html
  * - default image is shipped with extension
  * - some i18n
  * v0.1
  * - initial version
  */

//Last review MRG (30.06.11 10:25)

/**
 * Base class for Authors extension
 * @package BlueSpice_Extensions
 * @subpackage Authors
 */
class Authors extends BsExtensionMW {

	/**
	 * Contructor of the Authors class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::PARSERHOOK;
		$this->mInfo          = array(
			EXTINFO::NAME        => 'Authors',
			EXTINFO::DESCRIPTION => 'Displays authors of an article with image.',
			EXTINFO::AUTHOR      => 'Markus Glaser, Robert Vogel',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9931 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '1.20.0' )
		);
		$this->mExtensionKey = 'MW::Authors';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of Authors extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Hooks
		$this->setHook( 'BSBlueSpiceSkinAfterArticleContent' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );
		$this->setHook( 'BS:UserPageSettings', 'onUserPageSettings' );

		BsConfig::registerVar( 'MW::Authors::Blacklist',   array( 'MediaWiki default' ), BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_STRING, 'bs-authors-pref-blacklist' );
		BsConfig::registerVar( 'MW::Authors::ImageHeight', 40,                           BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-authors-pref-imageheight', 'int' );
		BsConfig::registerVar( 'MW::Authors::ImageWidth',  40,                           BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-authors-pref-imagewidth', 'int' );
		BsConfig::registerVar( 'MW::Authors::Limit',       10,                           BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-authors-pref-limit', 'int' );
		BsConfig::registerVar( 'MW::Authors::MoreImage',   'more-users_v2.png',          BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING, 'bs-authors-pref-moreimage' );
		BsConfig::registerVar( 'MW::Authors::Show',        true,                         BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-authors-pref-show', 'toggle' );

		$this->registerView( 'ViewAuthors' );
		$this->registerView( 'ViewAuthorsUserPageProfileImageSetting' );

		$this->mAdapter->registerBehaviorSwitch( 'NOAUTHORS', array( $this, 'noAuthorsCallback' ) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Callback for behaviorswitch
	 */
	public function noAuthorsCallback() {
		BsExtensionManager::setContext( 'MW::Authors::Hide' );
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object $oResponse reference
	 * $param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'switches' ) return true;

		$oResponse->result[] = array(
			'id'   => 'bs:authors',
			'type' => 'switch',
			'name' => 'NOAUTHORS',
			'desc' => wfMessage( 'bs-authors-switch-description' )->plain(),
			'code' => '__NOAUTHORS__',
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
		$oOutputPage->addModules('ext.bluespice.authors');

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
		$sUserImage = BsConfig::get( 'MW::UserImage' );

		//This seems too late for the -AuthorsUserPageProfileImageSetting-View. 
		//Therefore something similar is implemented there too...
		if ( empty( $sUserImage ) ) {
			$sUserImage = $oUser->getName().'.jpg';

			$dbw = wfGetDB( DB_MASTER );
			$dbw->delete(
				'user_properties',
				array(
					'up_user' => $oUser->getId(),
					'up_property' => 'MW::UserImage'
				)
			);
			$dbw->insert( 
					'user_properties', 
					array(
						'up_user' => $oUser->getId(),
						'up_property' => 'MW::UserImage',
						'up_value' => serialize( $sUserImage )
					),
					__METHOD__,
					'IGNORE'
			);
			$oUser->setOption( 'MW::UserImage', $sUserImage );
			//$oUser->saveSettings();
		}

		$oUserPageSettingsView = new ViewAuthorsUserPageProfileImageSetting();
		$oUserPageSettingsView->setCurrentUser( $oUser );
		$aSettingViews[] = $oUserPageSettingsView;
		return true;
	}

	/**
	 * Hook-Handler for 'BSBlueSpiceSkinAfterArticleContent'. Creates the authors list below an article.
	 * @param array $aViews Array of views to be rendered in skin
	 * @param User $oUser Current user object
	 * @param Title $oTitle Current title object
	 * @return Boolean Always true to keep hook running.
	 */
	public function onBSBlueSpiceSkinAfterArticleContent( &$aViews, $oUser, $oTitle ) {
		if ( $this->checkContext() === false ) return true;

		//Read in config variables
		$iLimit     = BsConfig::get( 'MW::Authors::Limit' );
		$aBlacklist = BsConfig::get( 'MW::Authors::Blacklist' );
		$sMoreImage = BsConfig::get( 'MW::Authors::MoreImage' );

		$aParams = array();
		$aParams['width']  = BsConfig::get( 'MW::Authors::ImageWidth' );
		$aParams['height'] = BsConfig::get( 'MW::Authors::ImageHeight' );

		$sPrintable = BsCore::getParam( 'printable', 'no', BsPARAM::REQUEST|BsPARAMTYPE::STRING|BsPARAMOPTION::DEFAULT_ON_ERROR );
		$iArticleId = $oTitle->getArticleID();

		//HINT: Maybe we want to use MW interface Article::getContributors() to have better caching
		//HINT2: Check if available in MW 1.17+
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'revision' ),
			array( 'rev_user_text', 'MAX(rev_timestamp) AS ts' ),
			array( 'rev_page' => $iArticleId ),
			__METHOD__,
			array(
				'GROUP BY' => 'rev_user_text',
				'ORDER BY' => 'ts ASC'
			)
		);

		$iRows = $res->numRows();
		if ( $iRows == 0 ) return true;

		$oAuthorsView = new ViewAuthors();
		if ( $sPrintable == 'yes' ) $oAuthorsView->setOption( 'print', true );

		$aUserNames = array();
		foreach( $res as $row ) {
			$aUserNames[] = $row->rev_user_text;
		}

		$sOriginatorUserName = $oTitle->getFirstRevision()->getUserText();
		$sOriginatorUserName = $this->checkOriginatorForBlacklist( $sOriginatorUserName, $oTitle->getFirstRevision(), $aBlacklist );
		if ( $aUserNames[0] != $sOriginatorUserName ) array_unshift( $aUserNames, $sOriginatorUserName );

		$bUseEllipsis = false;
		$iCount = count( $aUserNames );
		if ( $iCount > $iLimit ) $bUseEllipsis = true;

		if ( $bUseEllipsis ) {
			$iLength = $iCount - $iLimit + 1; //The plus 1 is for the '//--MORE--//' entry
			array_splice( $aUserNames, 1, $iLength, '//--MORE--//' ); // '//--MORE--//' is an invalid username. Therefore we won't get problems in later processing.
			$iCount = count( $aUserNames );
		}

		for ( $i = 0; $i < $iCount; $i++ ) {
			$sUserName = $aUserNames[$i];
			if ( $sUserName == '//--MORE--//' ) {
				$oMoreAuthorsView = $this->mAdapter->getUserMiniProfile( new User(), $aParams );
				$oMoreAuthorsView->setOption( 'userdisplayname', wfMsg( 'bs-authors-show-all-authors' ) );
				$oMoreAuthorsView->setOption( 'userimagesrc', $this->getImagePath( true ).'/'.$sMoreImage );
				$oMoreAuthorsView->setOption( 'linktargethref', $oTitle->getLocalURL( array('action' => 'edit') ) );
				$oMoreAuthorsView->setOption( 'classes', array('bs-authors-more-icon') );
				if ( $sPrintable == 'yes' ) $oMoreAuthorsView->setOption( 'print', true );

				$oAuthorsView->addItem( $oMoreAuthorsView );
				continue;
			}

			$oAuthorUser = User::newFromName( $sUserName );

			if ( !is_object( $oAuthorUser ) ) continue; // If the username was invalid... Should never happen, because the value comes from the DB.
			if ( in_array( $oAuthorUser->getName(), $aBlacklist ) ) continue; // Check for blacklisting

			$oUserMiniProfileView = $this->mAdapter->getUserMiniProfile( $oAuthorUser, $aParams );
			if ( $sPrintable   == 'yes' )  $oUserMiniProfileView->setOption( 'print', true );

			$oAuthorsView->addItem( $oUserMiniProfileView );
		}

		$dbr->freeResult( $res );

		array_unshift( $aViews, $oAuthorsView );
		return true;
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
		if ( BsConfig::get( 'MW::Authors::Show' ) === false ) return false;

		$oTitle = $this->mAdapter->get( 'Title' );
		if ( !is_object( $oTitle ) ) return false;
		
		// Do only display when user is allowed to read
		if ( !$oTitle->userCan( 'read' ) ) return false;

		// Do only display in view mode
		if ( BsCore::getParam( 'action', 'view', BsPARAM::REQUEST | BsPARAMTYPE::STRING | BsPARAMOPTION::DEFAULT_ON_ERROR ) != 'view' ) {
			return false;
		}

		// Do not display on SpecialPages, CategoryPages or ImagePages
		if ( in_array( $oTitle->getNamespace(), array( NS_SPECIAL, NS_CATEGORY, NS_FILE ) ) ) {
			return false;
		}

		// Do not display if __NOAUTHORS__ keyword is found
		if ( BsExtensionManager::isContextActive( 'MW::Authors::Hide' ) ) return false;

		return true;
	}

} // class Authors extends BsExtensionMW