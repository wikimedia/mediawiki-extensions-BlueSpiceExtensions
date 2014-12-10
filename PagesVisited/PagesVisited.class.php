<?php
/**
 * PagesVisited extension for BlueSpice
 *
 * Provides a personalized list of last visited pages.
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
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    2.22.0
 * @package    BlueSpice_Extensions
 * @subpackage PagesVisited
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
  * v1.20.0
  *
  * v1.0.0
  * - Optimized database access -> only whoisonline tables gets fetched
  * - Using BlueSpice view architecture
  * v0.2.0b
  * - Refactored / beautified code
  * - Using new database table scheme from WhoIsOnline Extension
  */

/**
 * Base class for PagesVisited extension
 * @package BlueSpice_Extensions
 * @subpackage PagesVisited
 */
class PagesVisited extends BsExtensionMW {

	/**
	 * Should cache a result list. Currently disabled.
	 * @var array
	 */
	private static $prResultListViewCache = array();

	/**
	 * Contructor of the PagesVisited class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'PagesVisited',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-pagesvisited-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Robert Vogel, Stephan Muggli',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
										'bluespice'   => '2.22.0',
										'WhoIsOnline' => '2.22.0'
										)
		);
		$this->mExtensionKey = 'MW::PagesVisited';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of PagesVisited extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'BSUserSidebarDefaultWidgets' );
		$this->setHook( 'BSWidgetListHelperInitKeyWords' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );

		BsConfig::registerVar( 'MW::PagesVisited::WidgetLimit', 10, BsConfig::LEVEL_USER|BsConfig::TYPE_INT, 'bs-pagesvisited-pref-widgetlimit', 'int' );
		BsConfig::registerVar( 'MW::PagesVisited::WidgetNS', array( 0 ), BsConfig::LEVEL_USER|BsConfig::TYPE_ARRAY_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-pagesvisited-pref-widgetns', 'multiselectex' );
		BsConfig::registerVar( 'MW::PagesVisited::WidgetSortOdr', 'time', BsConfig::LEVEL_USER|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-pagesvisited-pref-widgetsortodr', 'select' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * The preferences plugin callback
	 * @param string $sAdapterName
	 * @param BsConfig $oVariable
	 * @return array MediaWiki preferences options array
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array();
		switch( $oVariable->getName() ) {
			case 'WidgetNS':
				$aPrefs = array(
					'type' => 'multiselectex',
					'options' => BsNamespaceHelper::getNamespacesForSelectOptions( array( -2, NS_MEDIA, NS_MEDIAWIKI, NS_MEDIAWIKI_TALK, NS_SPECIAL ) )
				);
				break;
			case 'WidgetSortOdr':
				$aPrefs = array(
					'options' => array(
						wfMessage( 'bs-pagesvisited-pref-sort-time' )->plain() => 'time',
						wfMessage( 'bs-pagesvisited-pref-sort-pagename' )->plain() => 'pagename'
					)
				);
				break;
			default:
				break;
		}
		return $aPrefs;
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object $oResponse reference
	 * $param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'tags' ) return true;

		$oResponse->result[] = array(
			'id' => 'bs:pagesvisited',
			'type' => 'tag',
			'name' => 'pagesvisited',
			'desc' => wfMessage( 'bs-pagesvisited-tag-pagesvisited-desc' )->escaped(),
			'code' => '<bs:pagesvisited />',
		);

		return true;
	}

	/**
	 * Event-Handler for 'MW::Utility::WidgetListHelper::InitKeywords'. Registers a callback for the PAGESVISITED Keyword.
	 * @param array $aKeywords An array of Keywords array( 'KEYWORD' => $callable )
	 * @return array The appended array of Keywords array( 'KEYWORD' => $callable )
	 */
	public function onBSWidgetListHelperInitKeyWords( &$aKeywords, $oTitle ) {
		$aKeywords['PAGESVISITED'] = array( $this, 'onWidgetListKeyword' );
		return true;
	}

	/**
	 * Hook-Handler for 'ParserFirstCallInit' (MediaWiki). Sets new Parser-Hooks for the &lt;bs:pagesvisited /&gt; and &lt;pagesvisited /&gt; tag
	 * @param Parser $oParser The current Parser object from MediaWiki Framework
	 * @return bool Always true to keep hook running.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		$oParser->setHook( 'pagesvisited', array( $this, 'onPagesVisitedTag' ) );
		$oParser->setHook( 'bs:pagesvisited', array( $this, 'onPagesVisitedTag' ) );
		return true;
	}

	/**
	 * Handles the Parser Hook for TagExtensions
	 * @param string $sInput Content of $lt;pagesvisited /&gt; from MediaWiki Framework
	 * @param array $aAttributes Attributes of &lt;pagesvisited /&gt; from MediaWiki Framework
	 * @param Parser $oParser Parser object from MediaWiki Framework
	 * @return string HTML list of recently visited pages
	 */
	public function onPagesVisitedTag( $sInput, $aAttributes, $oParser ) {
		$oParser->disableCache();
		$oErrorListView = new ViewTagErrorList( $this );

		$iCount = BsCore::sanitizeArrayEntry( $aAttributes, 'count', 5, BsPARAMTYPE::INT );
		$iMaxTitleLength = BsCore::sanitizeArrayEntry( $aAttributes, 'maxtitlelength', 20, BsPARAMTYPE::INT );
		$sNamespaces = BsCore::sanitizeArrayEntry( $aAttributes, 'namespaces', 'all', BsPARAMTYPE::STRING | BsPARAMOPTION::CLEANUP_STRING );
		$sSortOrder = BsCore::sanitizeArrayEntry( $aAttributes, 'order', 'time', BsPARAMTYPE::STRING | BsPARAMOPTION::CLEANUP_STRING );

		//Validation
		$oValidationICount = BsValidator::isValid( 'IntegerRange', $iCount, array('fullResponse' => true, 'lowerBoundary' => 1, 'upperBoundary' => 30) );
		if ( $oValidationICount->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( 'count: '.$oValidationICount->getI18N() ) );
		}

		$oValidationIMaxTitleLength = BsValidator::isValid( 'IntegerRange', $iMaxTitleLength, array('fullResponse' => true, 'lowerBoundary' => 5, 'upperBoundary' => 50) );
		if ( $oValidationIMaxTitleLength->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( 'maxtitlelength: '.$oValidationIMaxTitleLength->getI18N() ) );
		}

		if ( $oErrorListView->hasItems() ) {
			return $oErrorListView->execute();
		}
		$iCurrentNamespaceId = $oParser->getTitle()->getNamespace();
		$oListView = $this->makePagesVisitedWikiList( $iCount, $sNamespaces, $iCurrentNamespaceId, $iMaxTitleLength, $sSortOrder );
		$sOut = $oListView->execute();

		if ( $oListView instanceof ViewTagErrorList ) {
			return $sOut;
		} else {
			return $this->mCore->parseWikiText( $sOut, $this->getTitle() );
		}
	}

	/**
	 * Callback for WidgetListHelper. Adds the PagesVisited Widget to the list if Keyword is found.
	 * @return ViewWidget.
	 */
	public function onWidgetListKeyword() {
		$aViews = array();
		$this->addWidgetView( $aViews );
		$aViews[0]->setAdditionalBodyClasses( array( 'bs-nav-links' ) );

		return $aViews[0];
	}

	/**
	 * Callback for WidgetBar. Adds the PagesVisited Widget to the WidgetBar as default filling.
	 * @param BsEvent $oEvent The event to handle
	 * @param array $aWidgets An array of WidgetView objects
	 * @return array An array of WidgetView objects
	 */
	public function onBSUserSidebarDefaultWidgets( &$aViews, $oUser, $oTitle ) {
		$aView = array();
		$this->addWidgetView( $aView );
		$aView[0]->setAdditionalBodyClasses( array( 'bs-nav-links' ) );
		$aViews['PAGESVISITED'] = $aView[0];

		return true;
	}

	/**
	 * Creates a Widget view object for the BlueSpice Skin.
	 * @param array &$aViews List of Widget view objects from the BlueSpice Skin.
	 */
	private function addWidgetView( &$aViews ) {
		$iCount = BsConfig::get( 'MW::PagesVisited::WidgetLimit' );
		$aNamespaces = BsConfig::get( 'MW::PagesVisited::WidgetNS' );
		$sSortOrder = BsConfig::get( 'MW::PagesVisited::WidgetSortOdr' );

		//Validation
		$oValidationICount = BsValidator::isValid( 'IntegerRange', $iCount, array( 'fullResponse' => true, 'lowerBoundary' => 1, 'upperBoundary' => 30 ) );
		if ( $oValidationICount->getErrorCode() ) $iCount = 10;

		$iCurrentNamespaceId = $this->getTitle()->getNamespace();

		// TODO RBV (04.07.11 15:02): Rework method -> implode() is a workaround for legacy code.
		$oListView = $this->makePagesVisitedWikiList( $iCount, implode( ',', $aNamespaces ), $iCurrentNamespaceId, 30, $sSortOrder );
		$sOut = $oListView->execute();

		if ( !( $oListView instanceof ViewTagError ) ) {
			$sOut = $this->mCore->parseWikiText( $sOut, $this->getTitle() );
		}

		$oWidgetView = new ViewWidget();
		$oWidgetView->setTitle( wfMessage( 'bs-pagesvisited-widget-title' )->plain() )
					->setBody( $sOut )
					->setAdditionalBodyClasses( array( 'bs-nav-links' ) ); //For correct margin and fontsize

		$aViews[] = $oWidgetView;
	}

	/**
	 * Gets the recently visited pages of the current user.
	 * @param int $iCount The number of pages to display
	 * @param string $sNamespaces Comma separated list of requested namespaces, i.e. "1,5,Category,101"
	 * @param int $iCurrentNamespaceId To determin wether the current namespace is in the list of requested namespaces
	 * @param string $sSortOrder Defines the sorting of the list. 'time|pagename', default is 'time'
	 * @return ViewBaseElement Contains the list in its _data member. The predefined template is '*[[{LINK}|{TITLE}]]\n'
	 */
	private function makePagesVisitedWikiList( $iCount = 5, $sNamespaces = 'all', $iCurrentNamespaceId = 0, $iMaxTitleLength = 20, $sSortOrder = 'time' ) {
		$oCurrentUser = $this->getUser();
		if ( is_null( $oCurrentUser ) ) return null; // in CLI

		//$sCacheKey = md5( $oCurrentUser->getName().$iCount.$sNamespaces.$iCurrentNamespaceId.$iMaxTitleLength );
		//if( isset( self::$prResultListViewCache[$sCacheKey] ) ) return self::$prResultListViewCache[$sCacheKey];
		$oErrorListView = new ViewTagErrorList( $this );
		$oErrorView = null;
		$aConditions = array();
		$aNamespaceIndexes = array( 0 );

		try {
			$aNamespaceIndexes = BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $sNamespaces ); //Returns array of integer indexes
		} catch ( BsInvalidNamespaceException $oException ) {
			$aInvalidNamespaces = $oException->getListOfInvalidNamespaces();

			$oVisitedPagesListView = new ViewBaseElement();
			$oVisitedPagesListView->setTemplate( '<ul><li><em>{TEXT}</em></li></ul>' . "\n" );

			$iCount = count( $aInvalidNamespaces );
			$sNs = implode( ', ', $aInvalidNamespaces );
			$sErrorMsg = wfMessage( 'bs-pagesvisited-error-nsnotvalid', $iCount, $sNs )->text();

			$oVisitedPagesListView->addData( array ( 'TEXT' => $sErrorMsg ) );

			//self::$prResultListViewCache[$sCacheKey] = $oVisitedPagesListView;
			return $oVisitedPagesListView;
		}

		$aConditions = array(
			'wo_user_id' => $oCurrentUser->getId(),
			'wo_action' => 'view'
		);

		$aConditions[] = 'wo_page_namespace IN ('.implode( ',', $aNamespaceIndexes ).')'; //Add IN clause to conditions-array
		$aConditions[] = 'wo_page_namespace != -1'; // TODO RBV (24.02.11 13:54): Filter SpecialPages because there are difficulties to list them

		$aOptions = array(
			'GROUP BY' => 'wo_page_id, wo_page_namespace, wo_page_title',
			'ORDER BY' => 'MAX(wo_timestamp) DESC'
		);

		if ( $sSortOrder == 'pagename' ) $aOptions['ORDER BY'] = 'wo_page_title ASC';

		//If the page the extension is used on appears in the result set we have to fetch one row more than neccessary.
		if ( in_array( $iCurrentNamespaceId, $aNamespaceIndexes ) ) $aOptions['OFFSET'] = 1;

		$aFields = array( 'wo_page_id', 'wo_page_namespace', 'wo_page_title' );
		$sTable = 'bs_whoisonline';

		$dbr = wfGetDB( DB_SLAVE );

		global $wgDBtype;
		if ( $wgDBtype == 'oracle' ) {
			$sRowNumField = 'rnk';
			$sTable = mb_strtoupper( $dbr->tablePrefix().$sTable );
			$sFields = implode( ',', $aFields );
			$sConditions = $dbr->makeList( $aConditions, LIST_AND );
			$aOptions['ORDER BY'] = $sSortOrder == 'pagename' ? $aOptions['ORDER BY'] : 'wo_timestamp DESC' ;

			$res = $dbr->query( "SELECT ".$sFields." FROM (
											SELECT ".$sFields.", row_number() over (order by ".$aOptions['ORDER BY'].") ".$sRowNumField."
											FROM ".$sTable."
											WHERE ".$sConditions."
											)
										WHERE ".$sRowNumField." BETWEEN (0) AND (".$iCount.") GROUP BY ".$aOptions["GROUP BY"].""
			);
		} else {
			$res = $dbr->select(
								$sTable,
								$aFields,
								$aConditions,
								__METHOD__,
								$aOptions
						);
		}

		$oVisitedPagesListView = new ViewBaseElement();
		$oVisitedPagesListView->setTemplate( '*{WIKILINK}' . "\n" );
		$iItems = 1;

		foreach ( $res as $row ) {
			if ( $iItems > $iCount ) break;
			$oVisitedPageTitle = Title::newFromID( $row->wo_page_id );
			/*
			// TODO RBV (24.02.11 13:52): Make SpecialPages work...
			$oVisitedPageTitle = ( $row->wo_page_namespace != NS_SPECIAL )
								? Title::newFromID( $row->wo_page_id )
								//: SpecialPage::getTitleFor( $row->wo_page_title );
								: Title::makeTitle( NS_SPECIAL, $row->wo_page_title );
			*/
			if ( $oVisitedPageTitle == null
				|| $oVisitedPageTitle->exists() === false
				|| $oVisitedPageTitle->quickUserCan( 'read' ) === false
				//|| $oVisitedPageTitle->isRedirect() //Maybe later...
				) {
				continue;
			}

			$sDisplayTitle = BsStringHelper::shorten(
				$oVisitedPageTitle->getPrefixedText(),
				array( 'max-length' => $iMaxTitleLength, 'position' => 'middle' )
			);

			$oVisitedPagesListView->addData(
				array ( 'WIKILINK' => BsLinkProvider::makeEscapedWikiLinkForTitle( $oVisitedPageTitle, $sDisplayTitle ) )
			);
			$iItems++;
		}

		//$dbr->freeResult( $res );

		//self::$prResultListViewCache[$sCacheKey] = $oVisitedPagesListView;
		return $oVisitedPagesListView;
	}

}
