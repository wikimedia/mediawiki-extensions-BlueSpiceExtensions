<?php
/**
 * WatchList extension for BlueSpice
 *
 * Adds the watchlist to focus.
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
 * @version    2.22.0
 * @package    BlueSpice_Extensions
 * @subpackage WatchList
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * v1.0.0
 * - Raised to stable
 * - Added phpDoc
 * v0.1
 * - initial release
 */

// Last review MRG (01.07.11 15:41)

/**
 * Base class for WatchList extension
 * @package BlueSpice_Extensions
 * @subpackage WantedArticle
 */
class WatchList extends BsExtensionMW {

	/**
	 * Contructor of the WatchList class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER;
		$this->mInfo = array(
			EXTINFO::NAME        => 'WatchList',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-watchlist-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
									'bluespice' => '2.22.0',
									'UserSidebar' => '2.22.0'
									)
		);
		$this->mExtensionKey = 'MW::WatchList';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of WatchList extension
	 */
	protected function initExt() {
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'BSUserSidebarDefaultWidgets' );
		$this->setHook( 'BSWidgetListHelperInitKeyWords' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );

		BsConfig::registerVar( 'MW::WatchList::WidgetLimit', 10, BsConfig::LEVEL_USER|BsConfig::TYPE_INT, 'bs-watchlist-pref-widgetlimit', 'int' );
		BsConfig::registerVar( 'MW::WatchList::WidgetSortOdr', 'time', BsConfig::LEVEL_USER|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-watchlist-pref-widgetsortodr', 'select' );
	}

	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		return array(
			'options' => array(
				wfMessage( 'bs-watchlist-pref-sort-time' )->plain() => 'time',
				wfMessage( 'bs-watchlist-pref-sort-title' )->plain() => 'pagename',
			)
		);
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
			'id' => 'bs:watchlist',
			'type' => 'tag',
			'name' => 'watchlist',
			'desc' => wfMessage( 'bs-watchlist-tag-watchlist-desc' )->plain(),
			'code' => '<bs:watchlist />',
		);

		return true;
	}

	 /**
	 * Registers &lt;bs:watchlist /&gt; and &lt;watchlist /&gt; tags with the MediaWiki parser
	 * @param Parser $oParser Current MediaWiki Parser object
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		$oParser->setHook( 'bs:watchlist', array( $this, 'onWatchlistTag' ) );
		$oParser->setHook( 'watchlist',    array( $this, 'onWatchlistTag' ) );

		return true;
	}

	/**
	 * Creates the HTML for &lt;bs:watchlist /&gt; tag
	 * @param string $sInput Inner HTML of the tag. Not used.
	 * @param array $aAttributes List of the tag's attributes.
	 * @param Parser $oParser MediaWiki parser object.
	 * @return string Rendered HTML.
	 */
	public function onWatchlistTag( $sInput, $aAttributes, $oParser ) {
		//Get arguments
		$iCount          = BsCore::sanitizeArrayEntry( $aAttributes, 'count',          5,          BsPARAMTYPE::INT        );
		$iMaxTitleLength = BsCore::sanitizeArrayEntry( $aAttributes, 'maxtitlelength', 20,         BsPARAMTYPE::INT        );
		$sOrder          = BsCore::sanitizeArrayEntry( $aAttributes, 'order',          'pagename', BsPARAMTYPE::SQL_STRING ); //'pagename|time'

		//Validation
		$oErrorListView = new ViewTagErrorList( $this );
		$oValidationICount = BsValidator::isValid( 'IntegerRange', $iCount, array('fullResponse' => true, 'lowerBoundary' => 1, 'upperBoundary' => 1000) );
		if ( $oValidationICount->getErrorCode() ) {
			$oErrorListView->addItem(
				new ViewTagError( 'count: '.wfMessage( $oValidationICount->getI18N() )->text() )
			);
		}

		$oValidationIMaxTitleLength = BsValidator::isValid( 'IntegerRange', $iMaxTitleLength, array('fullResponse' => true, 'lowerBoundary' => 5, 'upperBoundary' => 500) );
		if ( $oValidationIMaxTitleLength->getErrorCode() ) {
			$oErrorListView->addItem(
				new ViewTagError( 'maxtitlelength: '.wfMessage( $oValidationIMaxTitleLength->getI18N() )->text() )
			);
		}

		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$sOrder,
			array(
				'fullResponse' => true,
				'setname' => 'sort',
				'set' => array(
					'time',
					'pagename'
				)
			)
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		if ( $oErrorListView->hasItems() ) {
			return $oErrorListView->execute();
		}

		$oWatchList = $this->fetchWatchlist( $this->getUser(), $iCount, $iMaxTitleLength, $sOrder );
		return $this->mCore->parseWikiText( $oWatchList->execute(), $this->getTitle() );
	}

	/**
	 * Event-Handler for 'MW::Utility::WidgetListHelper::InitKeywords'. Registers a callback for the WATCHLIST Keyword.
	 * @param array $aKeywords An array of Keywords array( 'KEYWORD' => $callable )
	 * @return array The appended array of Keywords array( 'KEYWORD' => $callable )
	 */
	public function onBSWidgetListHelperInitKeyWords( &$aKeywords, $oTitle ) {
		$aKeywords['WATCHLIST'] = array( $this, 'onWidgetListKeyword' );
		return true;
	}

	/**
	 * Creates a Widget and returns it
	 * @return ViewWidget
	 */
	public function onWidgetListKeyword() {
		$oCurrentUser = $this->getUser();
		if( $oCurrentUser->isAnon() ) {
			return null;
		}

		$iCount = BsConfig::get('MW::WatchList::WidgetLimit');
		$sOrder = BsConfig::get('MW::WatchList::WidgetSortOdr');

		//Validation
		$oValidationICount = BsValidator::isValid( 'IntegerRange', $iCount, array('fullResponse' => true, 'lowerBoundary' => 1, 'upperBoundary' => 30) );
		if( $oValidationICount->getErrorCode() ) $iCount = 10;
		if( !in_array( $sOrder, array( 'pagename', 'time' ) ) ) $sOrder = 'pagename';

		$oUserSidebarView = new ViewWidget();
		$oUserSidebarView->setTitle( wfMessage( 'bs-watchlist-title-sidebar' )->plain() )
			->setAdditionalBodyClasses( array('bs-nav-links') ); //For correct margin and fontsize

		$oWatchList = $this->fetchWatchlist(
			$oCurrentUser,
			$iCount,
			30,
			$sOrder
		);
		$sWatchListWikiText = $oWatchList->execute();
		if (  empty( $sWatchListWikiText ) ) {
			return $oUserSidebarView;
		}

		$oUserSidebarView->setBody( $this->mCore->parseWikiText( $sWatchListWikiText, $this->getTitle() ) );

		return $oUserSidebarView;
	}

	/**
	 *
	 * @param User $oCurrentUser
	 * @param int $iCount
	 * @param int $iMaxTitleLength
	 * @param string $sOrder
	 * @return ViewBaseElement
	 */
	private function fetchWatchlist( $oCurrentUser, $iCount = 10, $iMaxTitleLength = 50, $sOrder = 'pagename' ) {
		$aWatchlist = array();

		$aOptions = array();
		if( $sOrder == 'pagename' ) {
			$aOptions['ORDER BY'] = 'wl_title';
		}
		$aOptions['LIMIT'] = $iCount;

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'watchlist',
			array( 'wl_namespace', 'wl_title' ),
			array(
				'wl_user' => $oCurrentUser->getId(),
				'NOT wl_notificationtimestamp' => NULL
			),
			__METHOD__,
			$aOptions
		);

		$oWatchedArticlesListView = new ViewBaseElement();
		$oWatchedArticlesListView->setTemplate( '*{WIKILINK}' . "\n" );
		foreach ( $res as $row ) {
			$oWatchedTitle = Title::newFromText( $row->wl_title, $row->wl_namespace );
			if( $oWatchedTitle === null
				|| $oWatchedTitle->exists() === false
				|| $oWatchedTitle->userCan( 'read' ) === false ) {
				continue;
			}
			$sDisplayTitle = BsStringHelper::shorten(
				$oWatchedTitle->getPrefixedText(),
				array( 'max-length' => $iMaxTitleLength, 'position' => 'middle' )
			);
			$oWatchedArticlesListView->addData(
				array (	'WIKILINK' => BsLinkProvider::makeEscapedWikiLinkForTitle( $oWatchedTitle, $sDisplayTitle )	)
				);
		}

		return $oWatchedArticlesListView;
	}

	/**
	 * Callback for UserSidebar. Adds the PagesVisited Widget to the UserSidebar as default filling.
	 * @param BsEvent $oEvent The event to handle
	 * @param array $aWidgets An array of WidgetView objects
	 * @return array An array of WidgetView objects
	 */
	public function onBSUserSidebarDefaultWidgets( &$aViews, $oUser, $oTitle ) {
		$aViews['WATCHLIST'] = $this->onWidgetListKeyword();
		return true;
	}
}
