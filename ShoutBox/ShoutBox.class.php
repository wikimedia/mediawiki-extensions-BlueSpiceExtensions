<?php
/**
 * Shoutbox extension for BlueSpice
 *
 * Adds a parser function for embedding your own shoutbox.
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
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Karl Waldmanstetter
 * @version    1.22.0 stable
 * @version    $Id: ShoutBox.class.php 9745 2013-06-14 12:09:29Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage ShoutBox
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * v1.1.0
 * - Added new column for user_id
 * - Added User images
 * - Added indexes to table for improved performance
 * - Reworked some code
 * v1.0.0
 * - raised to stable
 * - removed a lot of unneccessary code.
 * v0.4.2
 * - no Shoutbox if title cannot be viewed by user
 * v0.4.1
 * - reworked some outputs
 * v0.4
 * - not displayed in edit mode
 * v0.3
 * - shouts per article
 * v0.2.1
 * - code beautifying
 * - added sanitizer to insert function
 * v0.2
 * - added view classes
 * v0.1
 * - initial release
 */

//Last Code Review RBV (30.06.2011)
 
/**
 * Base class for ShoutBox extension
 * @package BlueSpice_Extensions
 * @subpackage ShoutBox
 */
class ShoutBox extends BsExtensionMW {

	/**
	 * Constructor of ShoutBox class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::PARSERHOOK;
		$this->mInfo          = array(
			EXTINFO::NAME        => 'ShoutBox',
			EXTINFO::DESCRIPTION => 'Adds a parser function for embedding your own shoutbox.',
			EXTINFO::AUTHOR      => 'Karl Waldmannstetter, Markus Glaser',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9745 $)', 
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '1.22.0' )
		);
		$this->mExtensionKey = 'MW::ShoutBox';

		$this->registerView('ViewShoutBox');
		$this->registerView('ViewShoutBoxMessageList');
		$this->registerView('ViewShoutBoxMessage');
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of ShoutBox extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Hooks
		$this->setHook( 'BSBlueSpiceSkinAfterArticleContent' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'LoadExtensionSchemaUpdates' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );

		// Permissions
		$this->mAdapter->registerPermission( 'readshoutbox' );
		$this->mAdapter->registerPermission( 'writeshoutbox' );
		$this->mAdapter->registerPermission( 'archiveshoutbox' );

		$this->mAdapter->registerBehaviorSwitch( 'bs_noshoutbox' );

		$this->mAdapter->addRemoteHandler( 'ShoutBox', $this, 'getShouts', 'readshoutbox' );
		$this->mAdapter->addRemoteHandler( 'ShoutBox', $this, 'insertShout', 'writeshoutbox' );
		$this->mAdapter->addRemoteHandler( 'ShoutBox', $this, 'archiveShout', 'archiveshoutbox' );

		BsConfig::registerVar('MW::ShoutBox::ShowShoutBoxByNamespace', array(0), BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_INT, 'bs-shoutbox-pref-ShowShoutBoxByNamespace', 'multiselectplusadd' );
		BsConfig::registerVar('MW::ShoutBox::CommitTimeInterval',      15,       BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT,        'bs-shoutbox-pref-CommitTimeInterval', 'int' );
		BsConfig::registerVar('MW::ShoutBox::NumberOfShouts',          5,        BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT,        'bs-shoutbox-pref-NumberOfShouts', 'int' );
		BsConfig::registerVar('MW::ShoutBox::ShowAge',                 true,     BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL,       'bs-shoutbox-pref-ShowAge', 'toggle' );
		BsConfig::registerVar('MW::ShoutBox::ShowUser',                true,     BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL,       'bs-shoutbox-pref-ShowUser', 'toggle' );
		BsConfig::registerVar('MW::ShoutBox::Show',                    true,     BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL,       'bs-shoutbox-pref-show', 'toggle' );
		BsConfig::registerVar('MW::ShoutBox::AllowArchive',            true,     BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL,       'bs-shoutbox-pref-AllowArchive', 'toggle' );
		BsConfig::registerVar('MW::ShoutBox::MaxMessageLength',        255,      BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT,        'bs-shoutbox-pref-MaxMessageLength', 'int' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Sets up required database tables
	 * @param DatabaseUpdater $updater Provided by MediaWikis update.php
	 * @return boolean Always true to keep the hook running
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		global $wgDBtype, $wgExtNewTables, $wgExtModifiedFields, $wgExtNewIndexes, $wgExtNewFields;
		$sDir = dirname( __FILE__ ) . DS;

		if( $wgDBtype == 'mysql' ) {
			$wgExtNewTables[]  = array( 'bs_shoutbox', $sDir . 'ShoutBox.sql' );
			$wgExtNewIndexes[] = array( 'bs_shoutbox', 'sb_page_id', $sDir . 'db/mysql/ShoutBox.patch.sb_page_id.index.sql' );
			$wgExtNewFields[]  = array( 'bs_shoutbox', 'sb_user_id', $sDir . 'db/mysql/ShoutBox.patch.sb_user_id.sql' );
			$wgExtNewFields[]  = array( 'bs_shoutbox', 'sb_archived', $sDir . 'db/mysql/ShoutBox.patch.sb_archived.sql' );
			
			//TODO: also do this for oracle and postgres
			$wgExtNewFields[]  = array( 'bs_shoutbox', 'sb_title', $sDir . 'db/mysql/ShoutBox.patch.sb_title.sql' );
			$wgExtNewFields[]  = array( 'bs_shoutbox', 'sb_touched', $sDir . 'db/mysql/ShoutBox.patch.sb_touched.sql' );
			$wgExtNewFields[]  = array( 'bs_shoutbox', 'sb_parent_id', $sDir . 'db/mysql/ShoutBox.patch.sb_parent_id.sql' );
		} elseif( $wgDBtype == 'postgres' ) {
			$wgExtNewTables[]  = array( 'bs_shoutbox', $sDir . 'ShoutBox.pg.sql' );
			$wgExtNewFields[] = array( 'bs_shoutbox', 'sb_archived', $sDir . 'db/postgres/ShoutBox.patch.sb_archived.pg.sql' );
			/*
			$wgExtNewIndexes[] = array( 'bs_shoutbox', 'sb_page_id', $sDir . 'db/postgres/ShoutBox.patch.sb_page_id.index.pg.sql' );
			$wgExtNewFields[]  = array( 'bs_shoutbox', 'sb_user_id', $sDir . 'db/postgres/ShoutBox.patch.sb_user_id.pg.sql' );
			*/
		} elseif( $wgDBtype == 'oracle' ) {
			$wgExtNewTables[]  = array( 'bs_shoutbox', $sDir . 'ShoutBox.oci.sql' );
			$dbr = wfGetDB( DB_SLAVE );
			if( !$dbr->fieldExists('bs_shoutbox', 'sb_archived') && $dbr->tableExists('bs_shoutbox') ) {
				#$wgExtNewFields[] = array( 'bs_shoutbox', 'sb_archived', $sDir . 'db/oracle/ShoutBox.patch.sb_archived.oci.sql' );
			}
			$wgExtNewIndexes[] = array( 'bs_shoutbox', 'sb_page_id', $sDir . 'db/oracle/ShoutBox.patch.sb_page_id.index.oci.sql' );
			/*
			$wgExtNewFields[]  = array( 'bs_shoutbox', 'sb_user_id', $sDir . 'db/oracle/ShoutBox.patch.sb_user_id.oci.sql' );
			*/
		}
		return true;
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
			'id'   => 'bs:shoutbox',
			'type' => 'switch',
			'name' => 'NOSHOUTBOX',
			'desc' => wfMessage( 'bs-shoutbox-switch-description' )->plain(),
			'code' => '__NOSHOUTBOX__',
		);

		return true;
	}

	/**
	 * 
	 * @param OutputPage $oOutputPage
	 * @param SkinTemplate $oSkinTemplate
	 * @return boolean
	 */
	public function onBeforePageDisplay( $oOutputPage, $oSkinTemplate ) {
		if ( BsConfig::get( 'MW::ShoutBox::Show' ) === false ) return true;
		$oTitle = $oOutputPage->getTitle();
		if( $oOutputPage->isPrintable() ) return true;

		if( is_object( $oTitle ) && $oTitle->exists() == false ) return true;
		if( !$oTitle->userCan( 'readshoutbox' ) ) return true;
		if( BsAdapterMW::getAction() != 'view' )  return true;
		if( BsAdapterMW::isSpecial() )            return true;
		if( !$oTitle->userCan( 'read' ) )         return true;

		$aNamespacesToDisplayShoutBox = BsConfig::get( 'MW::ShoutBox::ShowShoutBoxByNamespace' );
		if( !in_array( $oTitle->getNsText(), $aNamespacesToDisplayShoutBox ) ) return true;

		$vNoShoutbox = BsArticleHelper::getInstance( $oTitle )->getPageProp( 'bs_noshoutbox' );
		if( $vNoShoutbox === '' ) return true;
		
		$oOutputPage->addModules('ext.bluespice.shoutbox');

		BsExtensionManager::setContext( 'MW::ShoutboxShow' );
		return true;
	}

	/**
	 * Adds the shoutbox output to be rendered from skin.
	 * @param array $aViews a collection of views. Add the view that needs to be displayed
	 * @param User $oUser currently logged in user. Not used in this context.
	 * @param Title $oTitle current title.
	 * @return bool always true 
	 */
	public function onBSBlueSpiceSkinAfterArticleContent( &$aViews, $oUser, $oTitle ) {
		if ( !BsExtensionManager::isContextActive( 'MW::ShoutboxShow' ) ) return true;

		$oShoutBoxView = new ViewShoutBox();

		wfRunHooks( 'BSShoutBoxBeforeAddViewAfterArticleContent', array( &$oShoutBoxView ) );

		if ( $oTitle->userCan( 'writeshoutbox' ) ) {
			$oShoutBoxView->setOption( 'showmessageform', true );
		}
		$aViews[] = $oShoutBoxView;
		return true;
	}

	/**
	 * Delivers a rendered list of shouts for the current page to be displayed in the shoutbox. 
	 * This function is called remotely via AJAX-Handler.
	 * @param string $sOutput contains the rendered list
	 * @return bool allow other hooked methods to be executed. Always true
	 */
	public function getShouts( &$sOutput ) {
		if( !BsAdapterMW::checkAccessAdmission( 'readshoutbox', '', '', true ) ) return true;
		$iArticleId = BsCore::getParam( 'articleid' );
		$iArticleId = BsCore::sanitize( $iArticleId, 0, BsPARAMTYPE::INT);
		// do not allow negative page ids and pages that have 0 as id (e.g. special pages)
		if ( $iArticleId <= 0 ) return true;

		$iLimit = BsCore::getParam( 'sblimit', BsConfig::get('MW::ShoutBox::NumberOfShouts'), BsPARAMTYPE::NUMERIC|BsPARAM::REQUEST|BsPARAMOPTION::DEFAULT_ON_ERROR );
		if( $iLimit == 0 ) {
			$iLimit = BsConfig::get('MW::ShoutBox::NumberOfShouts');
		}

		//return false on hook handler to break here
		if( !wfRunHooks('BSShoutBoxGetShoutsBeforeQuery', array(&$sOutput, $iArticleId, &$iLimit)) ) {
			return true;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
				'bs_shoutbox',
				'*',
				array( 'sb_page_id' => $iArticleId, 'sb_archived' => '0', 'sb_parent_id' => '0', 'sb_title' => '' ),
				__METHOD__,
				array(
					'ORDER BY' => 'sb_timestamp DESC',
					// One more than iLimit in order to know if there are more shouts left.
					'LIMIT' => $iLimit + 1
				)
		);

		$oShoutBoxMessageListView = new ViewShoutBoxMessageList();

		if( $dbr->numRows( $res ) > $iLimit ) {
			$oShoutBoxMessageListView->setMoreLimit( $iLimit + BsConfig::get('MW::ShoutBox::NumberOfShouts') );
		}

		$bShowAge  = BsConfig::get( 'MW::ShoutBox::ShowAge' );
		$bShowUser = BsConfig::get( 'MW::ShoutBox::ShowUser' );

		$iCount = 0;
		while( $row = $dbr->fetchRow( $res ) ) {
			$oUser = User::newFromId( $row['sb_user_id'] );
			$oProfile = $this->mAdapter->getUserMiniProfile( $oUser );
			$oShoutBoxMessageView = new ViewShoutBoxMessage();
			if ( $bShowAge )  $oShoutBoxMessageView->setDate( BsFormatConverter::mwTimestampToAgeString( $row[ 'sb_timestamp' ], true ) );
			if ( $bShowUser ) $oShoutBoxMessageView->setUsername( $row[ 'sb_user_name' ] );
			$oShoutBoxMessageView->setUser( $oUser );
			$oShoutBoxMessageView->setMiniProfile( $oProfile );
			$oShoutBoxMessageView->setMessage( $row[ 'sb_message' ] );
			$oShoutBoxMessageView->setShoutID( $row[ 'sb_id' ] );
			$oShoutBoxMessageListView->addItem( $oShoutBoxMessageView );
			// Since we have one more shout than iLimit, we need to count :)
			$iCount++;
			if ( $iCount >= $iLimit ) break;
		}

		$sOutput .= $oShoutBoxMessageListView->execute();

		$dbr->freeResult( $res );
		return true;
	}

	/**
	 * Inserts a shout for the current page. 
	 * This function is called remotely via AJAX-Handler.
	 * @param string $sOutput success state of database action
	 * @return bool allow other hooked methods to be executed
	 */
	public function insertShout( &$sOutput ) {
		global $wgRequest;

		if( !BsAdapterMW::checkAccessAdmission( 'readshoutbox' ) )  return true;
		if( !BsAdapterMW::checkAccessAdmission( 'writeshoutbox' ) ) return true;

		// prevent spam by enforcing a interval between two commits
		$iCommitTimeInterval = BsConfig::get( 'MW::ShoutBox::CommitTimeInterval' );
		$iCurrentCommit = time();
		$vLastCommit    = $wgRequest->getSessionData( $this->mExtensionKey.'::lastCommit' );
		if( is_numeric( $vLastCommit ) && $vLastCommit + $iCommitTimeInterval > $iCurrentCommit ) {
			return true;
		}
		$wgRequest->setSessionData( $this->mExtensionKey.'::lastCommit', $iCurrentCommit );

		$iArticleId = BsCore::getParam( 'articleid', 0,  BsPARAMTYPE::INT|BsPARAM::REQUEST );
		$sMessage   = BsCore::getParam( 'message'  , '', BsPARAMTYPE::STRING|BsPARAM::REQUEST );

		$iArticleId = BsCore::sanitize( $iArticleId, 0, BsPARAMTYPE::NUMERIC );
		$sMessage   = BsCore::sanitize( $sMessage,  '', BsPARAMTYPE::STRING|BsPARAMOPTION::CLEANUP_STRING);

		$sNick      = BsAdapterMW::getUserDisplayName( $this->mAdapter->User );
		$iUserId    = $this->mAdapter->User->getId();
		$sTimestamp = wfTimestampNow();

		if( strlen($sMessage) > BsConfig::get( 'MW::ShoutBox::MaxMessageLength' ) ) {
			$sMessage = substr( $sMessage, 0, BsConfig::get( 'MW::ShoutBox::MaxMessageLength' ) );
		}

		// TODO MRG (08.09.10 01:57): error message
		if( $iArticleId <= 0 ) return false;

		$dbw = wfGetDB( DB_MASTER );
            $sOutput = $dbw->insert(
				'bs_shoutbox',
				array(
					'sb_page_id'    => $iArticleId,
					'sb_user_id'    => $this->mAdapter->User->getId(),
					'sb_user_name'  => $sNick,
					'sb_message'    => $sMessage,
					'sb_timestamp'  => $sTimestamp,
					'sb_archived'   => '0'
				)
		); // TODO RBV (21.10.10 17:21): Send error / success to client.

		wfRunHooks( 'BSShoutBoxAfterInsertShout', array( $iArticleId, $iUserId, $sNick, $sMessage, $sTimestamp ) );
		return true;
	}

	/**
	 * Archivess a shout for the current page. 
	 * This function is called remotely via AJAX-Handler.
	 * @param string $sOutput success state of database action
	 * @return bool allow other hooked methods to be executed
	 */
	public function archiveShout( &$sOutput ){
		if( !BsAdapterMW::checkAccessAdmission( 'readshoutbox' ) )  return true;
		if( !BsAdapterMW::checkAccessAdmission( 'writeshoutbox' ) ) return true;
		if( !BsAdapterMW::checkAccessAdmission( 'archiveshoutbox' ) ) return true;

		$iShoutId = BsCore::getParam( 'shoutID', 0,  BsPARAMTYPE::INT|BsPARAM::REQUEST );
		$iShoutId = BsCore::sanitize( $iShoutId, 0, BsPARAMTYPE::NUMERIC );

		$iUserId    = $this->mAdapter->User->getId();

		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->select(
				'bs_shoutbox',
				'sb_user_id',
				array( 'sb_id' => $iShoutId ),
				__METHOD__,
				array('LIMIT' => '1')
		);

		$row = $dbw->fetchRow( $res );
		//if setting for "just allow own entries to be archived" is set + username != shoutbox-entry-username => exit
		if (BsConfig::get('MW::ShoutBox::AllowArchive') == true && $iUserId != $row['sb_user_id']){
			$sOutput = wfMsg( 'bs-shoutbox-archive-failure-user' );
			return true;
		}
		$res = $dbw->update(
							'bs_shoutbox',
							array( 'sb_archived' => '1' ),
							array( 'sb_id' => $iShoutId )
		);
		$sResponse = $res == true ? 'bs-shoutbox-archive-success' : 'bs-shoutbox-archive-failure';
		$sOutput = wfMsg( $sResponse );
		return true;
	}

}
