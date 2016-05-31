<?php
/**
 * Provides the shoutbox api for BlueSpice.
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
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * ShoutBox Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksShoutBox extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'getShouts',
		'insertShout',
		'archiveShout',
	);

	/**
	 * Methods that can be executed even when the wiki is in read-mode, as
	 * they do not alter the state/content of the wiki
	 * @var array
	 */
	protected $aReadTasks = array(
		'getShouts',
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'getShouts' => array( 'readshoutbox' ),
			'insertShout' => array( 'writeshoutbox' ),
			'archiveShout' => array( 'writeshoutbox' ),
		);
	}

	/**
	 * Delivers a rendered list of shouts for the current page to be displayed
	 * in the shoutbox.
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_getShouts( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$iArticleId = isset( $oTaskData->articleId )
			? (int) $oTaskData->articleId
			: 0
		;
		$iLimit = isset( $oTaskData->limit )
			? (int) $oTaskData->limit
			: 0
		;
		$oReturn->payload['html'] = '';

		if( $iArticleId < 1 ) {
			return $oReturn;
		}

		if( $iLimit === 0 ) {
			$iLimit = BsConfig::get( 'MW::ShoutBox::NumberOfShouts' );
		}

		$aTables = array( 'bs_shoutbox' );
		$aFields = array( '*' );
		$aConditions = array(
			'sb_page_id' => $iArticleId,
			'sb_archived' => '0',
			'sb_parent_id' => '0',
			'sb_title' => '',
		);
		$aOptions = array(
			'ORDER BY' => 'sb_timestamp DESC',
		);
		//-int: no limit
		if( $iLimit > 0 ) {
			//One more than iLimit in order to know if there are more shouts
			//left.
			$aOptions['LIMIT'] = $iLimit + 1;
		}

		$b = wfRunHooks( 'BSShoutBoxGetShoutsBeforeQuery', array(
			&$oReturn->payload['html'],
			$iArticleId,
			&$iLimit,
			&$aTables,
			&$aFields,
			&$aConditions,
			&$aOptions,
			&$oReturn,
		));
		if( !$b ) {
			return $oReturn;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			$aTables,
			$aFields,
			$aConditions,
			__METHOD__,
			$aOptions
		);

		$oShoutBoxMessageListView = new ViewShoutBoxMessageList();

		if ( $dbr->numRows( $res ) > $iLimit ) {
			$oShoutBoxMessageListView->setMoreLimit(
				$iLimit + BsConfig::get( 'MW::ShoutBox::NumberOfShouts' )
			);
		}

		$bShowAge = BsConfig::get( 'MW::ShoutBox::ShowAge' );
		$bShowUser = BsConfig::get( 'MW::ShoutBox::ShowUser' );

		while ( $row = $dbr->fetchRow( $res ) ) {
			$oUser = User::newFromId( $row['sb_user_id'] );
			$oProfile = BsCore::getInstance()->getUserMiniProfile( $oUser );
			$sMessage = preg_replace_callback(
				"#@(\S*)#",
				"ShoutBox::replaceUsernameInMessage",
				$row['sb_message']
			);
			$oShoutBoxMessageView = new ViewShoutBoxMessage();
			if( $bShowAge ) {
				$sAge = BsFormatConverter::mwTimestampToAgeString(
					$row['sb_timestamp'],
					true
				);
				$oShoutBoxMessageView->setDate( $sAge );
			}
			if( $bShowUser ) {
				$oShoutBoxMessageView->setUsername( $row['sb_user_name'] );
			}
			$oShoutBoxMessageView->setUser( $oUser );
			$oShoutBoxMessageView->setMiniProfile( $oProfile );
			$oShoutBoxMessageView->setMessage( $sMessage );
			$oShoutBoxMessageView->setShoutID( $row['sb_id'] );
			$oShoutBoxMessageListView->addItem( $oShoutBoxMessageView );
			// Since we have one more shout than iLimit, we need to count :)
			$oReturn->payload_count++;
			if ( $oReturn->payload_count >= $iLimit ) {
				break;
			}
		}

		$oReturn->payload['html'] .= $oShoutBoxMessageListView->execute();

		$iTotelShouts = $oReturn->payload_count;
		if( $oReturn->payload_count >= $iLimit ) {
			$iTotelShouts = ShoutBox::getTotalShouts( $iArticleId );
			$oReturn->payload_count = $iLimit;
		}
		$oReturn->payload['html'] .= Xml::element( 'div', array(
			'id' => 'bs-sb-count-all',
			'style' => 'display:none'
		), $iTotelShouts );

		$dbr->freeResult( $res );
		$oReturn->success = true;
		return $oReturn;
	}

	/**
	 * Inserts a shout for the current page.
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_insertShout( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();
		$oRequest = $this->getRequest();
		// prevent spam by enforcing a interval between two commits
		$iCommitTimeInterval = BsConfig::get(
			'MW::ShoutBox::CommitTimeInterval'
		);
		$iCurrentCommit = time();
		$vLastCommit = (int) $oRequest->getSessionData(
			'MW::ShoutBox::lastCommit'
		);
		if( $vLastCommit + $iCommitTimeInterval > $iCurrentCommit ) {
			$oReturn->message = wfMessage( 'bs-shoutbox-too-early' )->plain();
			return $oReturn;
		}
		$oRequest->setSessionData(
			'MW::ShoutBox::lastCommit',
			$iCurrentCommit
		);

		$iArticleId = isset( $oTaskData->articleId )
			? (int) $oTaskData->articleId
			: 0
		;
		// TODO MRG (08.09.10 01:57): error message
		if ( $iArticleId <= 0 ) {
			return $oReturn;
		}
		$sMessage = isset( $oTaskData->message )
			? (string) $oTaskData->message
			: ''
		;
		$sNick = BsCore::getUserDisplayName( $this->getUser() );
		$sTimestamp = wfTimestampNow();
		$iMsgMaxLenght = BsConfig::get( 'MW::ShoutBox::MaxMessageLength' );
		$sMessage = htmlspecialchars( $sMessage, ENT_QUOTES, 'UTF-8' );
		if ( strlen( $sMessage ) > $iMsgMaxLenght ) {
			$sMessage = substr(
				$sMessage,
				0,
				BsConfig::get( 'MW::ShoutBox::MaxMessageLength' )
			);
		}

		$oDB = wfGetDB( DB_MASTER );
		$oReturn->payload = array( 'sb_id' => 0 );
		$oReturn->success = $oDB->insert(
			'bs_shoutbox',
			array(
				'sb_page_id' => $iArticleId,
				'sb_user_id' => $this->getUser()->getId(),
				'sb_user_name' => $sNick,
				'sb_message' => $sMessage,
				'sb_timestamp' => $sTimestamp,
				'sb_archived' => '0'
			)
		);

		if( $oReturn->success ) {
			$oReturn->payload['sb_id'] = $oDB->insertId();
		}
		$b = wfRunHooks( 'BSShoutBoxAfterInsertShout', array(
			$iArticleId,
			$this->getUser()->getId(),
			$sNick,
			$sMessage,
			$sTimestamp,
			&$oReturn
		));
		if( !$b || !$oReturn->success ) {
			return $oReturn;
		}

		ShoutBox::notify(
			"insert",
			$iArticleId,
			$this->getUser()->getId(),
			$sNick,
			$sMessage,
			$sTimestamp
		);

		ShoutBox::invalidateShoutBoxCache( $iArticleId );
		return $oReturn;
	}

	/**
	 * Archivess a shout for the current page.
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_archiveShout( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$iShoutId = isset( $oTaskData->shoutId )
			? (int) $oTaskData->shoutId
			: 0
		;

		$oDBw = wfGetDB( DB_MASTER );
		$oRes = $oDBw->selectRow(
			'bs_shoutbox',
			array( 'sb_user_id', 'sb_page_id' ),
			array( 'sb_id' => $iShoutId ),
			__METHOD__
		);

		if( !$oRes ) {
			$oReturn->message = wfMessage(
				'bs-shoutbox-archive-failure'
			)->plain();
			return $oReturn;
		}
		$iArticleId = $oRes->sb_page_id;
		//If we don't have archiveshoutbox rights, maybe we can delete our own
		//shout?
		if ( !$this->getUser()->isAllowed('archiveshoutbox') ) {
			$bAllowArchive = BsConfig::get( 'MW::ShoutBox::AllowArchive' );
			$bOwn = $this->getUser()->getId() != $oRes->sb_user_id;
			if( !$bAllowArchive || $bOwn ) {
				$oReturn->message = wfMessage(
					'bs-shoutbox-archive-failure'
				)->plain();
				return $oReturn;
			}
		}
		$oRes = $oDBw->update(
			'bs_shoutbox',
			array( 'sb_archived' => '1' ),
			array( 'sb_id' => $iShoutId )
		);
		if( !$oRes ) {
			$oReturn->message = wfMessage(
				'bs-shoutbox-archive-failure'
			)->plain();
			return $oReturn;
		}

		ShoutBox::invalidateShoutBoxCache( $iArticleId );
		$oReturn->message = wfMessage(
			'bs-shoutbox-archive-success'
		)->plain();

		return $oReturn;
	}

	public function needsToken() {
		return parent::needsToken();
	}
}