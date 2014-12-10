<?php

/**
 * WhoIsOnline extension for BlueSpice
 *
 * Displays a list of users who are currently online.
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
 * @version    2.22.0
 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v1.20.0
 * - Added hook handler for InsertMagic
 * v1.1.0
 * - Added indexes to table to improve performance
 * - Added profile statements
 * - Reduced number of logging actions
 * v1.0.0
 * - Raised to stable
 * - Implemented Widget Keyword
 * v0.6
 * - Applied new coding conventions
 * - Logs SpecialPages too
 * v0.5
 * - introduced views
 * - improved error handling
 * v0.4
 * - fixed bug that if whoisonline-table not existant bluespice breaks
 * v0.3
 * - reactivated tooltip
 * - added widget hook
 * v0.2.0b
 * - Refactored / beautified code
 * - Changed SELECT querys, so they return only datasets which are not older than 10 Minutes
 * - Added new configuration variable MW::WhoIsOnline::MaxIdleTime, which
 *   tells the extenstion how long a user is allowed to be idle before being
 *   removed from the 'who's online' list.
 * - Table cleanup after 30 days ( = 720 hours )
 * - New database table scheme
 * - TO DO: Discuss wether a a minimalistic database scheme (id, user_id, page_id, timesamp) would be sufficient.
 * v0.1
 * - initial release
 */

/**
 * Base class for WhoIsOnline extension
 * @package BlueSpice_Extensions
 * @subpackage WhoIsOnline
 */
class WhoIsOnline extends BsExtensionMW {

	private $aWhoIsOnlineData = array();

	/**
	 * Constructor of MailChanges class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::PARSERHOOK;
		$this->mInfo = array(
			EXTINFO::NAME        => 'WhoIsOnline',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-whoisonline-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
				'bluespice' => '2.22.0'
			)
		);

		$this->mExtensionKey = 'MW::WhoIsOnline';

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of ShoutBox extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Hooks
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'BeforeInitialize' );
		$this->setHook( 'BeforePageDisplay');
		$this->setHook( 'LanguageGetMagic' );
		$this->setHook( 'BSWidgetBarGetDefaultWidgets' );
		$this->setHook( 'BSWidgetListHelperInitKeyWords' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );
		$this->setHook( 'BsAdapterAjaxPingResult' );

		BsConfig::registerVar( 'MW::WhoIsOnline::LimitCount', 7, BsConfig::LEVEL_USER | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_INT, 'bs-whoisonline-pref-limitcount', 'int' );
		BsConfig::registerVar( 'MW::WhoIsOnline::OrderBy', 'onlinetime', BsConfig::LEVEL_USER | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-whoisonline-pref-orderby', 'select' );
		BsConfig::registerVar( 'MW::WhoIsOnline::MaxIdleTime', 600, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_INT, 'bs-whoisonline-pref-maxidletime', 'int' );
		BsConfig::registerVar( 'MW::WhoIsOnline::Interval', 10, BsConfig::LEVEL_PUBLIC | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_INT, 'bs-whoisonline-pref-interval', 'int' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Sets up required database tables
	 * @param DatabaseUpdater $updater Provided by MediaWikis update.php
	 * @return boolean Always true to keep the hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		global $wgDBtype, $wgExtNewTables, $wgExtModifiedFields, $wgExtNewIndexes, $wgExtNewFields;
		$sDir = __DIR__ . DS;

		if( $wgDBtype == 'mysql' ) {
			$wgExtNewTables[] = array( 'bs_whoisonline', $sDir . 'whoisonline.sql' );

			$wgExtModifiedFields[] = array( 'bs_whoisonline', 'wo_timestamp', $sDir . 'db/mysql/whoisonline.patch.wo_timestamp.sql' );

			$wgExtNewFields[] = array( 'bs_whoisonline', 'wo_action', $sDir . 'db/mysql/whoisonline.patch.wo_action.sql' );

			$wgExtNewIndexes[] = array( 'bs_whoisonline', 'wo_user_id',        $sDir . 'db/mysql/whoisonline.patch.wo_user_id.index.sql' );
			$wgExtNewIndexes[] = array( 'bs_whoisonline', 'wo_page_namespace', $sDir . 'db/mysql/whoisonline.patch.wo_page_namespace.index.sql' );
			$wgExtNewIndexes[] = array( 'bs_whoisonline', 'wo_timestamp',      $sDir . 'db/mysql/whoisonline.patch.wo_timestamp.index.sql' );

		} elseif( $wgDBtype == 'postgres' ) {

			$wgExtNewTables[] = array( 'bs_whoisonline', $sDir . 'whoisonline.pg.sql' );

			$wgExtModifiedFields[] = array( 'bs_whoisonline', 'wo_timestamp', $sDir . 'db/postgres/whoisonline.patch.wo_timestamp.pg.sql' );
			//PW(25.06.2012): wont work on mw 1.16.5
			//global $wgExtPGNewFields;
			//$wgExtPGNewFields[] = array( 'bs_whoisonline', 'wo_action', $sDir . 'db/postgres/whoisonline.patch.wo_action.sql' );
			$dbr = wfGetDB( DB_MASTER );
			if( $dbr->tableExists( 'bs_whoisonline' ) && !$dbr->fieldExists( 'bs_whoisonline', 'wo_action' ) ) {
				$dbr->query( "ALTER TABLE ".$dbr->tableName( "bs_whoisonline" )." ADD wo_action text NOT NULL DEFAULT 'view';" );
			}

			$wgExtNewIndexes[] = array( 'bs_whoisonline', 'wo_user_id',        $sDir . 'db/postgres/whoisonline.patch.wo_user_id.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_whoisonline', 'wo_page_namespace', $sDir . 'db/postgres/whoisonline.patch.wo_page_namespace.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_whoisonline', 'wo_timestamp',      $sDir . 'db/postgres/whoisonline.patch.wo_timestamp.index.pg.sql' );

		} elseif( $wgDBtype == 'oracle' ) {
			$wgExtNewTables[] = array( 'bs_whoisonline', $sDir . 'whoisonline.oci.sql' );

			$wgExtModifiedFields[] = array( 'bs_whoisonline', 'wo_timestamp', $sDir . 'db/oracle/whoisonline.patch.wo_timestamp.oci.sql' );

			#$wgExtNewFields[] = array( 'bs_whoisonline', 'wo_action', $sDir . 'db/oracle/whoisonline.patch.wo_action.sql' );

			$wgExtNewIndexes[] = array( 'bs_whoisonline', 'wo_user_id',        $sDir . 'db/oracle/whoisonline.patch.wo_user_id.index.oci.sql' );
			$wgExtNewIndexes[] = array( 'bs_whoisonline', 'wo_page_namespace', $sDir . 'db/oracle/whoisonline.patch.wo_page_namespace.index.oci.sql' );
			$wgExtNewIndexes[] = array( 'bs_whoisonline', 'wo_timestamp',      $sDir . 'db/oracle/whoisonline.patch.wo_timestamp.index.oci.sql' );
		}
		return true;
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if ( !$this->getTitle()->userCan( 'read' ) ) return true;

		BsExtensionManager::setContext( 'MW::WhoIsOnline::Show' );
		$oOutputPage->addModules( 'ext.bluespice.whoisonline' );
		return true;
	}

	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if ( $type != 'tags' ) return true;

		$oResponse->result[] = array(
			'id' => 'bs:whoisonlinecount',
			'type' => 'tag',
			'name' => 'whoisonlinecount',
			'desc' => wfMessage( 'bs-whoisonline-tag-whoisonlinecount-desc' )->plain(),
			'code' => '<bs:whoisonlinecount />',
		);

		$oResponse->result[] = array(
			'id' => 'bs:whoisonlinepopup',
			'type' => 'tag',
			'name' => 'whoisonlinepopup',
			'desc' => wfMessage( 'bs-whoisonline-tag-whoisonlinepopup-desc' )->plain(),
			'code' => '<bs:whoisonlinepopup />',
		);

		return true;
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array(
			'options' => array(
				wfMessage( 'bs-whoisonline-pref-orderby-name' )->plain() => 'name',
				wfMessage( 'bs-whoisonline-pref-orderby-time' )->plain() => 'onlinetime',
			)
		);
		return $aPrefs;
	}

	/**
	 * Event-Handler for 'MW::Utility::WidgetListHelper::InitKeywords'. Registers a callback for the WHOISONLINE Keyword.
	 * @param BsEvent $oEvent The Event object
	 * @param array $aKeywords An array of Keywords array( 'KEYWORD' => $callable )
	 * @return array The appended array of Keywords array( 'KEYWORD' => $callable )
	 */
	public function onBSWidgetListHelperInitKeyWords( &$aKeywords, $oTitle ) {
		$aKeywords[ 'WHOISONLINE' ] = array( $this, 'onWidgetListKeyword' );
		return true;
	}

	/**
	 * Callback for WidgetListHelper. Adds the WhoIsOnline Widget to the list if Keyword is found.
	 * @return ViewWidget.
	 */
	public function onWidgetListKeyword() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$oWidgetView = new ViewWidget();
		$oWidgetView
			->setId( 'bs-whoisonline' )
			->setTitle( wfMessage( 'bs-whoisonline-widget-title' )->plain() )
			->setBody( $this->getPortlet(false, BsConfig::get('MW::WhoIsOnline::LimitCount') )->execute() )
			->setTooltip( wfMessage( 'bs-whoisonline-widget-title' )->plain() )
			->setAdditionalBodyClasses( array( 'bs-nav-links', 'bs-whoisonline-portlet' ) ); //For correct margin and fontsize

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oWidgetView;
	}

	/**
	 * Add various tags and magic words. Magic Words are only supported for legacy reasons.
	 * @param Parser $oParser Current MediaWiki Parser object
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		$oParser->setFunctionHook( 'userscount', array( &$this, 'onUsersCount' ) );
		$oParser->setHook( 'bs:whoisonline:count', array( &$this, 'onUsersCountTag' ) );
		$oParser->setHook( 'bs:whoisonlinecount', array( &$this, 'onUsersCountTag' ) );
		$oParser->setFunctionHook( 'userslink', array( &$this, 'onUsersLink' ) );
		$oParser->setHook( 'bs:whoisonline:popup', array( &$this, 'onUsersLinkTag' ) );
		$oParser->setHook( 'bs:whoisonlinepopup', array( &$this, 'onUsersLinkTag' ) );

		return true;
	}

	/**
	 * Add magic words. Used for legacy support.
	 * @param array $aMagicWords Array of magic words. Add to this array.
	 * @param string $sLangCode Current langugage.
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onLanguageGetMagic( &$aMagicWords, $sLangCode ) {
		$aMagicWords[ 'userscount' ] = array( 0, 'userscount' );
		$aMagicWords[ 'userslink' ]  = array( 0, 'userslink' );
		return true;
	}

	/**
	 * Hook-Handler for MediaWiki hook BeforeInitialize - Inserts a trace of the user action into the database
	 * @param Title $oTitle
	 * @param Article $oArticle
	 * @param OutputPage $oOutput
	 * @param User $oUser
	 * @param WebRequest $oRequest
	 * @param MediaWiki $oMediaWiki
	 * @return boolean - alway true
	 */
	public function onBeforeInitialize( &$oTitle, &$oArticle, &$oOutput, &$oUser, $oRequest, $oMediaWiki ) {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->insertTrace( $oTitle, $oUser, $oRequest );

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Fetches the HTML for bs:whoisonline:count tag
	 * @param string $sInput Inner HTML of the tag. Not used.
	 * @param array $aAttributes List of the tag's attributes.
	 * @param Parser $oParser MediaWiki parser object.
	 * @return string Rendered HTML.
	 */
	public function onUsersCountTag( $sInput, $aAttributes, $oParser ) {
		$aOut = $this->onUsersCount( $oParser );
		return $aOut[0];
	}

	/**
	 * Renders bs:whoisonline:count output.
	 * @param Parser $oParser MediaWiki parser object.
	 * @return array Rendered HTML and flags. Used by magic word function hook as well as by onUsersCountTag.
	 */
	public function onUsersCount( $oParser ) {
		wfProfileIn( 'BS::'.__METHOD__ );

		$sOut = '<span class="bs-whoisonline-count">'.count( $this->getWhoIsOnline() ).'</span>';

		wfProfileOut( 'BS::'.__METHOD__ );
		return array( $sOut, 'noparse' => 1 );
	}

	/**
	 * Fetches the HTML for bs:whoisonline:popup tag
	 * @param string $sInput Inner HTML of the tag. Not used.
	 * @param array $aAttributes List of the tag's attributes.
	 * @param Parser $oParser MediaWiki parser object.
	 * @return string Rendered HTML.
	 */
	public function onUsersLinkTag( $sInput, $aAttributes, $oParser ) {
		//Validation in onUsersLink.
		return $this->onUsersLink( $oParser, isset($aAttributes[ 'anchortext' ])?$aAttributes[ 'anchortext' ] : "" );
	}

	/**
	 * Renders bs:whoisonline:popup output.
	 * @param Parser $oParser MediaWiki parser object.
	 * @param string $sLinkTitle Label of the link that is the anchor of the flyout
	 * @return array Rendered HTML and flags. Used by magic word function hook as well as by onUsersLinkTag.
	 */
	public function onUsersLink( $oParser, $sLinkTitle = '') {
		$oParser->disableCache();
		wfProfileIn( 'BS::'.__METHOD__ );
		$sLinkTitle = BsCore::sanitize( $sLinkTitle, '', BsPARAMTYPE::STRING );

		if( empty( $sLinkTitle ) ) $sLinkTitle = wfMessage('bs-whoisonline-widget-title')->plain();
		$oWhoIsOnlineTagView = new ViewWhoIsOnlineTag();
		$oWhoIsOnlineTagView->setOption( 'title', $sLinkTitle );
		$oWhoIsOnlineTagView->setPortlet( $this->getPortlet() );
		$sOut = $oWhoIsOnlineTagView->execute();

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oParser->insertStripItem( $sOut, $oParser->mStripState );
	}

	/**
	 * Renders the WhoIsOnline widget
	 * @param BsEvent $oEvent
	 * @param array $aWidgets List of widgets. Add to this list.
	 * @return array Appenden list of Widgets.
	 */
	public function onBSWidgetBarGetDefaultWidgets( &$aViews, $oUser, $oTitle ) {
		$aViews['WHOISONLINE'] = $this->onWidgetListKeyword();
		return true;
	}

	/**
	 * Renders the inner part of tag and widget view.
	 * @param mixed $vWrapperId Distinct ID. Used if several instances are used on a page.
	 * @return string Rendered HTML
	 */
	private function getPortlet( $vWrapperId = false, $iLimit = 0 ) {
		wfProfileIn( 'BS::'.__METHOD__ );

		$aWhoIsOnline = $this->getWhoIsOnline();

		// who (names)
		$oWhoIsOnlineWidgetView = new ViewWhoIsOnlineWidget();
		$oWhoIsOnlineWidgetView->setOption( 'count', count($aWhoIsOnline) );
		$oWhoIsOnlineWidgetView->setOption( 'wrapper-id', $vWrapperId );

		$iCount = 1;
		foreach( $aWhoIsOnline as $oWhoIsOnline) {
			if( $iLimit > 0 && $iCount > $iLimit ) break;

			$oUser = User::newFromName( $oWhoIsOnline->wo_user_name );
			$oWhoIsOnlineItemWidgetView = new ViewWhoIsOnlineItemWidget();
			$oWhoIsOnlineItemWidgetView->setUserName( $oUser->getName() );
			$oWhoIsOnlineItemWidgetView->setUserDisplayName( $this->mCore->getUserDisplayName( $oUser ) );
			$oWhoIsOnlineWidgetView->addItem( $oWhoIsOnlineItemWidgetView );
			$iCount++;
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oWhoIsOnlineWidgetView;
	}

	/**
	 * Hook-Handler for BS hook BsAdapterAjaxPingResult
	 * @global User $wgUser
	 * @global WebRequest $wgRequest
	 * @param string $sRef
	 * @param array $aData
	 * @param integer $iArticleId
	 * @param array $aSingleResult
	 * @return boolean
	 */
	public function onBsAdapterAjaxPingResult( $sRef, $aData, $iArticleId, $sTitle, $iNamespace, $iRevision, &$aSingleResult ) {
		if ( $sRef !== 'WhoIsOnline') return true;

		$oTitle = Title::newFromText( $sTitle, $iNamespace );
		if ( is_null($oTitle) || !$oTitle->userCan('read') ) return true;

		$aWhoIsOnline = $this->getWhoIsOnline();
		$aSingleResult['count'] = count( $aWhoIsOnline );

		$aSingleResult['portletItems'] = array();
		foreach ( $aWhoIsOnline as $oWhoIsOnline ) {
			$oUser = User::newFromName( $oWhoIsOnline->wo_user_name );
			$oWhoIsOnlineItemWidgetView = new ViewWhoIsOnlineItemWidget();
			$oWhoIsOnlineItemWidgetView->setUserName( $oUser->getName() );
			$oWhoIsOnlineItemWidgetView->setUserDisplayName( $this->mCore->getUserDisplayName( $oUser ) );
			$aSingleResult['portletItems'][] = $oWhoIsOnlineItemWidgetView->execute();
		}

		$aSingleResult['success'] = true;
		return true;
	}

	/**
	 * Loads WhoIsOnline data from DB
	 * @param string $sOrderBy
	 * @param bool $bForceReload
	 * @return type
	 */
	private function getWhoIsOnline( $sOrderBy = '', $bForceReload = false){
		wfProfileIn( 'BS::'.__METHOD__ );

		if ( isset( $this->aWhoIsOnlineData[$sOrderBy] ) && $bForceReload === false ) {
			wfProfileOut( 'BS::'.__METHOD__ );
			return $this->aWhoIsOnlineData[$sOrderBy];
		}

		if ( empty( $sOrderBy ) ) $sOrderBy = BsConfig::get( 'MW::WhoIsOnline::OrderBy' );

		$sMaxIdle = BsConfig::get( 'MW::WhoIsOnline::MaxIdleTime' );
		//$iLimit   = BsConfig::get( 'MW::WhoIsOnline::LimitCount' );

		$this->aWhoIsOnlineData[$sOrderBy] = array();

		$aTables = array(
			'bs_whoisonline'
		);
		$aFields = array(
			'wo_user_name'
		);
		$aConditions = array(
			'wo_timestamp > '.( time() - $sMaxIdle )
		);
		$aOptions = array(
			'GROUP BY' => 'wo_user_name',
			//'LIMIT'    => (int) $iLimit,
		);

		$dbr = wfGetDB( DB_SLAVE );
		switch ( $sOrderBy ) {
			case 'name' :
			default :
				$aOptions['ORDER_BY'] = 'wo_user_name ASC';
			case 'onlinetime' :
				$aOptions['ORDER_BY'] = 'MAX(wo_timestamp) DESC';
		}

		$rRes = $dbr->select( $aTables, $aFields, $aConditions, __METHOD__, $aOptions );
		while( $oRow = $dbr->fetchObject($rRes) )
			$this->aWhoIsOnlineData[$sOrderBy][] = $oRow;

		wfProfileOut( 'BS::'.__METHOD__ );
		return $this->aWhoIsOnlineData[$sOrderBy];
	}

	/**
	 * Inserts a trace of the user action into the database
	 * @global string $wgDBtype
	 * @param Title $oTitle
	 * @param User $oUser
	 * @param Request $oRequest
	 * @return boolean
	 */
	public function insertTrace( $oTitle, $oUser, $oRequest) {
		if ( wfReadOnly() ) return true;
		if ( ( $oUser->getId() == 0 ) ) return true; // Anonymous user

		$sPageTitle = $oTitle->getText();
		if ( $sPageTitle == '-' ) return true; // otherwise strange '-' with page_id 0 are logged

		$iPageId             = $oTitle->getArticleId();
		$iPageNamespaceId    = $oTitle->getNamespace();
		$iCurrentTimestamp   = time();
		$vLastLoggedPageHash = $oRequest->getSessionData( $this->mExtensionKey.'::lastLoggedPageHash' );
		$vLastLoggedTime     = $oRequest->getSessionData( $this->mExtensionKey.'::lastLoggedTime' );
		$sCurrentPageHash    = md5( $iPageId.$iPageNamespaceId.$sPageTitle ); //this combination should be pretty unique, even with specialpages.
		$iMaxIdleTime        = BsConfig::get( 'MW::WhoIsOnline::MaxIdleTime' );
		$iInterval           = BsConfig::get( 'MW::WhoIsOnline::Interval' );

		if ( $vLastLoggedPageHash == $sCurrentPageHash
			&& $vLastLoggedTime + $iMaxIdleTime + $iInterval + ($iMaxIdleTime * 0.1) > $iCurrentTimestamp )
				return true;

		//log action
		wfProfileIn( 'BS::'.__METHOD__ );
		$oRequest->setSessionData( $this->mExtensionKey.'::lastLoggedPageHash', $sCurrentPageHash );
		$oRequest->setSessionData( $this->mExtensionKey.'::lastLoggedTime', $iCurrentTimestamp );

		$iRemoveEntriesAfter = 2592000;

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'bs_whoisonline',
			array( 'wo_timestamp < ' . ( $iCurrentTimestamp - $iRemoveEntriesAfter ) )
		);

		$aNewRow = array();

		$aNewRow[ 'wo_page_id' ]        = $oTitle->getArticleId();
		$aNewRow[ 'wo_page_namespace' ] = $oTitle->getNamespace();
		$aNewRow[ 'wo_page_title' ]     = $sPageTitle;
		$aNewRow[ 'wo_user_id' ]        = $oUser->getId();
		$aNewRow[ 'wo_user_name' ]      = $oUser->getName();
		$aNewRow[ 'wo_user_real_name' ] = $oUser->getRealName();
		$aNewRow[ 'wo_timestamp' ]      = $iCurrentTimestamp;
		$aNewRow[ 'wo_action' ]         = $oRequest->getVal( 'action', 'view' );

		global $wgDBtype;
		if ( $wgDBtype == 'oracle' ) $aNewRow[ 'wo_id' ] = 0;

		$dbw->insert( 'bs_whoisonline', $aNewRow );

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

}