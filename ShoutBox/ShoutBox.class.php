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
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Tobias Weichart <weichart@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @author     Karl Waldmanstetter
 * @version    2.23.2
 * @package    BlueSpice_Extensions
 * @subpackage ShoutBox
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

//Last Code Review RBV (30.06.2011)

/**
 * Base class for ShoutBox extension
 * @package BlueSpice_Extensions
 * @subpackage ShoutBox
 */
class ShoutBox extends BsExtensionMW {
	/**
	 * Initialization of ShoutBox extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::' . __METHOD__ );

		// Hooks
		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );
		$this->setHook( 'BSStateBarBeforeTopViewAdd', 'onStateBarBeforeTopViewAdd' );
		$this->setHook( 'EchoGetDefaultNotifiedUsers' );


		// Permissions
		$this->mCore->registerPermission( 'readshoutbox', array(), array( 'type' => 'global' ) );
		$this->mCore->registerPermission( 'writeshoutbox', array(), array( 'type' => 'global' ) );
		$this->mCore->registerPermission( 'archiveshoutbox', array(), array( 'type' => 'global' ) );

		$this->mCore->registerBehaviorSwitch( 'bs_noshoutbox' );

		BsConfig::registerVar( 'MW::ShoutBox::ShowShoutBoxByNamespace', array( 0 ), BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_ARRAY_INT, 'multiselectplusadd' );
		BsConfig::registerVar( 'MW::ShoutBox::CommitTimeInterval', 15, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_INT, 'bs-shoutbox-pref-committimeinterval', 'int' );
		BsConfig::registerVar( 'MW::ShoutBox::NumberOfShouts', 5, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_INT, 'bs-shoutbox-pref-numberofshouts', 'int' );
		BsConfig::registerVar( 'MW::ShoutBox::ShowAge', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-shoutbox-pref-showage', 'toggle' );
		BsConfig::registerVar( 'MW::ShoutBox::ShowUser', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-shoutbox-pref-showuser', 'toggle' );
		BsConfig::registerVar( 'MW::ShoutBox::Show', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-shoutbox-pref-show', 'toggle' );
		BsConfig::registerVar( 'MW::ShoutBox::AllowArchive', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-shoutbox-pref-allowarchive', 'toggle' );
		BsConfig::registerVar( 'MW::ShoutBox::MaxMessageLength', 255, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_INT, 'bs-shoutbox-pref-maxmessagelength', 'int' );

		BSNotifications::registerNotificationCategory( 'bs-shoutbox-mention-cat' );
		BSNotifications::registerNotification(
			'bs-shoutbox-mention',
			'bs-shoutbox-mention-cat',
			'bs-shoutbox-notifications-summary',
			array( 'agent', 'title', 'titlelink' ),
			'bs-shoutbox-notifications-title-message-subject',
			array(),
			'bs-shoutbox-notifications-title-message-text',
			array( 'agent', 'title', 'titlelink', 'agentprofile', 'title' )
		);
		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * Sets up required database tables
	 * @param DatabaseUpdater $updater Provided by MediaWikis update.php
	 * @return boolean Always true to keep the hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		global $wgDBtype, $wgExtNewTables, $wgExtNewIndexes, $wgExtNewFields;
		$sDir = __DIR__ . '/';

		if ( $wgDBtype == 'mysql') {
			$wgExtNewTables[] = array( 'bs_shoutbox', $sDir . 'db/mysql/ShoutBox.sql' );
			$wgExtNewIndexes[] = array( 'bs_shoutbox', 'sb_page_id', $sDir . 'db/mysql/ShoutBox.patch.sb_page_id.index.sql' );
			$wgExtNewFields[] = array( 'bs_shoutbox', 'sb_user_id', $sDir . 'db/mysql/ShoutBox.patch.sb_user_id.sql' );
			$wgExtNewFields[] = array( 'bs_shoutbox', 'sb_archived', $sDir . 'db/mysql/ShoutBox.patch.sb_archived.sql' );

			//TODO: also do this for oracle and postgres
			$wgExtNewFields[] = array( 'bs_shoutbox', 'sb_title', $sDir . 'db/mysql/ShoutBox.patch.sb_title.sql' );
			$wgExtNewFields[] = array( 'bs_shoutbox', 'sb_touched', $sDir . 'db/mysql/ShoutBox.patch.sb_touched.sql' );
			$wgExtNewFields[] = array( 'bs_shoutbox', 'sb_parent_id', $sDir . 'db/mysql/ShoutBox.patch.sb_parent_id.sql' );
		} elseif ( $wgDBtype == 'sqlite' ) {
			$wgExtNewTables[] = array( 'bs_shoutbox', $sDir . 'db/mysql/ShoutBox.sql' );
		} elseif ( $wgDBtype == 'postgres' ) {
			$wgExtNewTables[] = array( 'bs_shoutbox', $sDir . 'db/postgres/ShoutBox.pg.sql' );
			$wgExtNewFields[] = array( 'bs_shoutbox', 'sb_archived', $sDir . 'db/postgres/ShoutBox.patch.sb_archived.pg.sql' );
			/*
			  $wgExtNewIndexes[] = array( 'bs_shoutbox', 'sb_page_id', $sDir . 'db/postgres/ShoutBox.patch.sb_page_id.index.pg.sql' );
			  $wgExtNewFields[]  = array( 'bs_shoutbox', 'sb_user_id', $sDir . 'db/postgres/ShoutBox.patch.sb_user_id.pg.sql' );
			 */
		} elseif ( $wgDBtype == 'oracle' ) {
			$wgExtNewTables[] = array( 'bs_shoutbox', $sDir . 'db/oracle/ShoutBox.oci.sql' );
			$dbr = wfGetDB( DB_SLAVE );
			if ( !$dbr->fieldExists( 'bs_shoutbox', 'sb_archived' ) && $dbr->tableExists( 'bs_shoutbox' ) ) {
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
		if ( $type != 'switches' )
			return true;

		$oResponse->result[] = array(
			'id' => 'bs:shoutbox',
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
		if ( BsConfig::get( 'MW::ShoutBox::Show' ) === false )
			return true;
		$oTitle = $oOutputPage->getTitle();
		if ( $oOutputPage->isPrintable() )
			return true;

		if ( is_object( $oTitle ) && $oTitle->exists() == false )
			return true;
		if ( !$oTitle->userCan( 'readshoutbox' ) )
			return true;
		if ( $this->getRequest()->getVal( 'action', 'view' ) != 'view' )
			return true;
		if ( $oTitle->isSpecialPage() )
			return true;
		if ( !$oTitle->userCan( 'read' ) )
			return true;

		$aNamespacesToDisplayShoutBox = BsConfig::get( 'MW::ShoutBox::ShowShoutBoxByNamespace' );
		if ( !in_array( $oTitle->getNsText(), $aNamespacesToDisplayShoutBox ) )
			return true;

		$vNoShoutbox = BsArticleHelper::getInstance( $oTitle )->getPageProp( 'bs_noshoutbox' );
		if ( $vNoShoutbox === '' )
			return true;

		$oOutputPage->addModuleStyles( 'ext.bluespice.shoutbox.styles' );
		$oOutputPage->addModules( 'ext.bluespice.shoutbox' );
		$oOutputPage->addModules( 'ext.bluespice.shoutbox.mention' );

		BsExtensionManager::setContext( 'MW::ShoutboxShow' );
		return true;
	}

	/**
	 * Adds the shoutbox output to be rendered from skin.
	 * @param SkinTemplate $sktemplate a collection of views. Add the view that needs to be displayed
	 * @param BaseTemplate $tpl currently logged in user. Not used in this context.
	 * @return bool always true
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		if ( !BsExtensionManager::isContextActive( 'MW::ShoutboxShow' ) ) {
			return true;
		}

		$tpl->data['bs_dataAfterContent']['bs-shoutbox'] = array(
			'position' => 30,
			'label' => wfMessage( 'bs-shoutbox-title' )->text(),
			'content' => $this->getShoutboxViewForAfterContent( $sktemplate )
		);
		return true;
	}

	private function getShoutboxViewForAfterContent( $sktemplate ) {
		$oShoutBoxView = new ViewShoutBox();

		wfRunHooks( 'BSShoutBoxBeforeAddViewAfterArticleContent', array( &$oShoutBoxView ) );

		if ( $sktemplate->getTitle()->userCan( 'writeshoutbox' ) ) {
			$oShoutBoxView->setOption( 'showmessageform', true );
		}

		return $oShoutBoxView;
	}

	/**
	 * Returns total number of shouts for the article id
	 * @param int $iArticleId
	 * @return int number of shouts
	 */
	public static function getTotalShouts( $iArticleId = 0 ) {
		$sKey = BsCacheHelper::getCacheKey( 'BlueSpice', 'ShoutBox', 'totalCount' . $iArticleId );
		$iData = BsCacheHelper::get( $sKey );

		if ( $iData === false ) {
			wfDebugLog( 'BsMemcached', __CLASS__ . ': Fetching total count from DB' );
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'bs_shoutbox',
				'sb_id',
				array(
					'sb_page_id' => $iArticleId,
					'sb_archived' => '0',
					'sb_parent_id' => '0',
					'sb_title' => '',
				),
				__METHOD__
			);
			$iTotalShouts = $res !== false ? $dbr->numRows( $res ) : 0;

			BsCacheHelper::set( $sKey, $iTotalShouts );
		} else {
			wfDebugLog( 'BsMemcached', __CLASS__ . ': Fetching total count from cache' );
			$iTotalShouts = $iData;
		}
		return $iTotalShouts;
	}

	/**
	 * Invalidates the ShoutBox cache for a single article
	 * @param integer $iArticleId
	 */
	public static function invalidateShoutBoxCache( $iArticleId ) {
		BsCacheHelper::invalidateCache( BsCacheHelper::getCacheKey(
			'BlueSpice',
			'ShoutBox',
			"totalCount$iArticleId"
		));
	}

	/**
	 * Ads Shoutbox icon with number of shouts to the statebar
	 * @global String $wgScriptPath
	 * @param StateBar $oStateBar
	 * @param array $aTopViews
	 * @param User $oUser
	 * @param Title $oTitle
	 * @return boolean
	 */
	public function onStateBarBeforeTopViewAdd( $oStateBar, &$aTopViews, $oUser, $oTitle ) {
		$oShoutboxView = new ViewStateBarTopElement();
		$iTotalShouts = self::getTotalShouts( $oTitle->getArticleID() );
		if ( !is_object( $this->getTitle() ) || $iTotalShouts == 0 ) {
			return true;
		}
		global $wgScriptPath;
		$oShoutboxView->setKey( 'Shoutbox' );
		$oShoutboxView->setIconSrc( $wgScriptPath . '/extensions/BlueSpiceExtensions/ShoutBox/resources/images/icon-shoutbox.png' );
		$oShoutboxView->setIconAlt( wfMessage( 'bs-shoutbox-title' )->plain() );
		$oShoutboxView->setText(wfMessage( 'bs-shoutbox-n-shouts',self::getTotalShouts( $oTitle->getArticleID() ) )->text() );
		$oShoutboxView->setTextLink('#bs-shoutbox');
		$aTopViews['statebartopshoutbox'] = $oShoutboxView;
		return true;
	}

	/**
	 * Notifies a User for different actions
	 * @param String $sAction
	 * @param int $iArticleId
	 * @param int $iUserId
	 * @param String $sNick
	 * @param String $sMessage
	 * @param String $sTimestamp
	 */
	public static function notify( $sAction, $iArticleId, $iUserId, $sNick, $sMessage, $sTimestamp ) {
		switch ( $sAction ) {
			case "insert":
				$aUsers = self::getUsersMentioned( $sMessage );
				if ( count( $aUsers ) < 1 )
					break;
				self::notifyUser( "mention", $aUsers, $iArticleId, $iUserId );
		}
	}

	/**
	 * Returns an array of users being mentioned in a shoutbox message
	 * @param String $sMessage
	 * @return array Array filled with users of type User
	 */
	public static function getUsersMentioned( $sMessage ) {
		if ( empty( $sMessage ) )
			return array();
		$bResult = preg_match_all( "#@(\S*)#", $sMessage, $aMatches );
		if ( $bResult === false || $bResult < 1 )
			return array();
		$aReturn = array();
		foreach ( $aMatches[1] as $sUserName ) {
			$aReturn [] = User::newFromName( $sUserName );
		}
		return $aReturn;
	}

	/**
	 * Notifies all Users specified in the array via Echo extension if it's turned on
	 * @param String $sAction
	 * @param array $aUsers
	 * @param int $iArticleId
	 * @param int $iUserId
	 */
	public static function notifyUser( $sAction, $aUsers, $iArticleId, $iUserId ) {
		$oCurrentUser = RequestContext::getMain()->getUser();
		$sCurrentUserName = BsUserHelper::getUserDisplayName($oCurrentUser);
		$oTitle = Article::newFromID($iArticleId)->getTitle();
		$sTitleText = $oTitle->getText();
		$oAgent = User::newFromId($iUserId);

		foreach ( $aUsers as $oUser ) {
			#if you're mentioning yourself don't send notification
			if ( $oUser->getId() === $iUserId ) {
				continue;
			}
			BSNotifications::notify(
				"bs-shoutbox-{$sAction}",
				$oAgent,
				$oTitle,
				array(
					'mentioned-user-id' => $oUser->getId(),
					'realname' => $sCurrentUserName,
					'title' => $sTitleText,
					'agentprofile' => $oCurrentUser->getUserPage()->getFullURL(),
				)
			);
		}

	}

	/**
	 * Handler for EchoGetDefaultNotifiedUsers hook.
	 * @param EchoEvent $event EchoEvent to get implicitly subscribed users for
	 * @param array &$users Array to append implicitly subscribed users to.
	 * @return bool true in all cases
	 */
	public static function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
		switch ( $event->getType() ) {
			case 'bs-shoutbox-mention':
				$extra = $event->getExtra();
				if ( !$extra || !isset( $extra['mentioned-user-id'] ) ) {
					break;
				}
				$recipientId = $extra['mentioned-user-id'];
				$users[$recipientId] = User::newFromId($recipientId);
				break;
		}
		return true;
	}

	/**
	 * Callback from preg_replace, replaces the mention with a link to the user page
	 * @param array $sMatch
	 * @return String The link to the user page
	 */
	public static function replaceUsernameInMessage( $sMatch ) {
		$oUser = User::newFromName( $sMatch[1] );
		return Linker::link( $oUser->getUserPage(), BsUserHelper::getUserDisplayName( $oUser ) );
	}

}
