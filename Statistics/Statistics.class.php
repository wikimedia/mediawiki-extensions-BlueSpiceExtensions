<?php
/**
 * Statistics Extension for BlueSpice
 *
 * Adds statistical analysis to pages.
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
 * @author     Tobias Weichart <weichart@hallowelt.biz>
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    2.22.0 stable

 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * v1.0.0
 * - raised to stable
 * v0.1
 * - initial commit
 * - still buggy, some filters are not set correctly
 */

/**
 * Main class for Statistics extension
 * @package BlueSpice_Extensions
 * @subpackage Statistics
 */
class Statistics extends BsExtensionMW {

	/**
	 * Collects all available diagrams
	 * @var array List of strings
	 */
	protected static $aAvailableDiagramClasses = array();
	/**
	 * Contains all available diagrams
	 * @var array List of diagram objects
	 */
	protected static $aAvailableDiagrams = null;
	/**
	 * Contains all available filters
	 * @var array List of filter objects.
	 */
	protected static $aAvailableFilters = array();

	/**
	 * Constructor for statistcs class.
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'Statistics',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-statistics-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser, Patric Wirth',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::Statistics';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of Statistics extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::Statistics::initExt' );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSExtendedSearchAdminButtons' );
		$this->setHook( 'BSDashboardsAdminDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsAdminDashboardPortalPortlets' );
		$this->setHook( 'BSDashboardsUserDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsUserDashboardPortalPortlets' );

		BsConfig::registerVar( 'MW::Statistics::ExcludeUsers', array( 'WikiSysop' ), BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_ARRAY_STRING, 'bs-statistics-pref-excludeusers', 'multiselectplusadd' );
		BsConfig::registerVar( 'MW::Statistics::MaxNumberOfIntervals', 366, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-statistics-pref-maxnumberofintervals', 'int' );

		$aAvailableGrains = array(
			'Y' => 'bs-statistics-year',
			'm' => 'bs-statistics-month',
			'W' => 'bs-statistics-week',
			'd' => 'bs-statistics-day',
		);
		BsConfig::registerVar( 'MW::Statistics::AvailableGrains', $aAvailableGrains, BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_MIXED, 'bs-statistics-pref-AvailableGrains');

		Statistics::addAvailableFilter( 'FilterUsers' );
		Statistics::addAvailableFilter( 'FilterNamespace' );
		Statistics::addAvailableFilter( 'FilterCategory' );
		Statistics::addAvailableFilter( 'FilterSearchScope' );

		Statistics::addAvailableDiagramClass( 'BsDiagramNumberOfUsers' );
		Statistics::addAvailableDiagramClass( 'BsDiagramNumberOfPages' );
		Statistics::addAvailableDiagramClass( 'BsDiagramNumberOfArticles' );
		Statistics::addAvailableDiagramClass( 'BsDiagramNumberOfEdits' );
		Statistics::addAvailableDiagramClass( 'BsDiagramEditsPerUser' );
		Statistics::addAvailableDiagramClass( 'BsDiagramSearches' );

		wfProfileOut( 'BS::Statistics::initExt' );
	}

	/**
	 * Registers available diagrams
	 * @param string $sDiagramClass Name of class.
	 */
	public static function addAvailableDiagramClass( $sDiagramClass ) {
		if ( strpos( $sDiagramClass, 'Bs' ) !== 0 ) {
			$sDiagramClassName =  'Bs'.$sDiagramClass;
		} else {
			$sDiagramClassName =  $sDiagramClass;
		}

		Statistics::$aAvailableDiagramClasses[$sDiagramClassName] = $sDiagramClassName;
	}

	/**
	 * Returns list of available diagrams.
	 * @return array List of diagram objects.
	 */
	public static function getAvailableDiagrams() {
		self::loadAvailableDiagrams();
		return Statistics::$aAvailableDiagrams;
	}

	/**
	 * Loads all available diagrams, i.e. instanciate all classes
	 * @return array List of available diagrams
	 */
	protected static function loadAvailableDiagrams() {
		if ( !is_null( self::$aAvailableDiagrams ) ) {
			return self::$aAvailableDiagrams;
		}
		self::$aAvailableDiagrams = array();
		foreach ( Statistics::$aAvailableDiagramClasses as $sDiagramClass ) {
			self::$aAvailableDiagrams[$sDiagramClass] = new $sDiagramClass();
		}
		return self::$aAvailableDiagrams;
	}

	/**
	 * Get instance for a particluar diagram class.
	 * @param string $sDiagramClass Name of diagram
	 * @return BsDiagram
	 */
	public static function getDiagram( $sDiagramClass ) {
		self::loadAvailableDiagrams();
		return Statistics::$aAvailableDiagrams[$sDiagramClass];
	}

	/**
	 * Registers a filter
	 * @param string $sFilterClass Name of filter class
	 */
	public static function addAvailableFilter( $sFilterClass ) {
		if ( strpos( $sFilterClass, 'Bs' ) !== 0 ) {
			$sFilterClassName =  'Bs'.$sFilterClass;
		} else {
			$sFilterClassName =  $sFilterClass;
		}
	}

	/**
	 * Returns list of available filters
	 * @return array Names of filtesr.
	 */
	public static function getAvailableFilters() {
		return Statistics::$aAvailableFilters;
	}

	/**
	 * Get a particular filter
	 * @param string $sFilterClass Name of filter
	 * @return BsStatisticsFilter Filter object
	 */
	public static function getFilter( $sFilterClass ) {
		if ( isset( Statistics::$aFilterDiagrams[$sFilterClass] ) ) {
			return Statistics::$aFilterDiagrams[$sFilterClass];
		} else {
			return null;
		}
	}

	/**
	 * Registers a tag "bs:infobox" with the parser. for legacy reasons witn HalloWiki, also "infobox" is supported. Called by ParserFirstCallInit hook
	 * @param Parser $parser MediaWiki parser object
	 * @return bool allow other hooked methods to be executed. always true
	 */
	public function onParserFirstCallInit( &$parser ) {
		// for legacy reasons
		$parser->setHook( 'bs:statistics:progress', array( &$this, 'onTagProgress' ) );
		return true;
	}

	/**
	 *
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if( !$oSkin->getTitle()->equals(SpecialPage::getTitleFor('AdminDashboard'))
			&& !$oSkin->getTitle()->equals(SpecialPage::getTitleFor('WikiAdmin'))
			&& !$oSkin->getTitle()->equals(SpecialPage::getTitleFor('UserDashboard'))
		) return true;

		$oOutputPage->addModules('ext.bluespice.statisticsPortlets');
		return true;
	}

	/**
	 * Renders the Progress tag. Called by parser function.
	 * @param string $input Inner HTML of InfoBox tag. Not used.
	 * @param array $args List of tag attributes.
	 * @param Parser $parser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function onTagProgress( $input, $args, $parser ) {
		$iBaseCount = BsCore::sanitizeArrayEntry( $args, 'basecount'     , 100  , BsPARAMTYPE::INT );
		$sBaseItem  = BsCore::sanitizeArrayEntry( $args, 'baseitem'      , '' , BsPARAMTYPE::STRING );
		$sFraction  = BsCore::sanitizeArrayEntry( $args, 'progressitem'  , 'OK' , BsPARAMTYPE::STRING );
		$iWidth     = BsCore::sanitizeArrayEntry( $args, 'width'         , 100  , BsPARAMTYPE::INT );

		// no Article when in cli mode
		if ( !is_object( $this->getTitle() ) ) {
			return '';
		}

		$sText = BsPageContentProvider::getInstance()->getContentFromTitle( $this->getTitle() );

		// substract 1 because one item is in the progressitem attribute
		$iFraction = substr_count( $sText, $sFraction ) - 1;

		if ( $sBaseItem ) {
			$iBase = substr_count( $sText, $sBaseItem ) - 1;
		} else {
			$iBase = $iBaseCount;
		}

		$fPercent = $iFraction / $iBase;

		$iWidthGreen = floor($iWidth * $fPercent);
		$iWidthRemain = $iWidth-$iWidthGreen;

		$sPercent = sprintf( "%0.1f", $fPercent * 100 );

		$sOut = '<div style="background-color:green;border:2px solid #DDDDDD;width:'.$iWidthGreen.'px;height:25px;float:left;color:#DDDDDD;text-align:center;border-right:0px;text-weight:bold;vertical-align:middle;">'.$sPercent.'%</div>';
		$sOut .= '<div style="border:2px solid #DDDDDD;border-left:0px;width:'.$iWidthRemain.'px;height:25px;float:left;"></div>';

		return $sOut;
	}

	public function onBSExtendedSearchAdminButtons( $oSpecialPage, &$aSearchAdminButtons ) {
		global $wgScriptPath;

		$aSearchAdminButtons['Statistics'] = array(
			'href' => SpecialPage::getTitleFor( 'ExtendedStatistics' )->getLinkUrl(),
			'onclick' => '',
			'label' => wfMessage( 'bs-extendedsearch-statistics' )->plain(),
			'image' => "$wgScriptPath/extensions/BlueSpiceExtensions/Statistics/resources/images/bs-searchstatistics.png"
		);

		return true;
	}

	public static function ajaxGetAvalableDiagrams() {
		$aResult = array(
			"success" => false,
			"errors" => array(),
			"message" => '',
			"data" => array(),
		);

		global $wgUser;
		if( !$wgUser->isAllowed('read') ) {
			$aResult["message"] = wfMessage('bs-statistics-not-allowed')->plain();
			return json_encode($aResult);
		}

		foreach ( self::getAvailableDiagrams() as $oDiagram ) {
			$aFilterKeys = array();
			foreach( $oDiagram->getFilters() as $key => $oFilter ) $aFilterKeys[] = $key;

			$aResult['data'][] = array(
				'key' => $oDiagram->getDiagramKey(),
				'displaytitle' => $oDiagram->getTitle(),
				'listable' => $oDiagram->isListable(),
				'filters' => $aFilterKeys,
			);
		}

		$aResult['success'] = true;
		return json_encode($aResult);
	}

	public static function ajaxGetUserFilter() {
		$aResult = array(
			"success" => false,
			"errors" => array(),
			"message" => '',
			"data" => array(),
		);

		global $wgUser;
		if( !$wgUser->isAllowed('read') ) {
			$aResult["message"] = wfMessage('bs-statistics-not-allowed')->plain();
			return json_encode($aResult);
		}

		$oDbr = wfGetDB( DB_SLAVE );
		$rRes = $oDbr->select('user', array('user_id', 'user_name'), '', '', array('ORDER BY' => 'user_name ASC') );
		while ( $oRow = $rRes->fetchObject() ) {
			$aResult['data'][] = array(
				'key' => $oRow->user_name,
				'displaytitle' => $oRow->user_name,
			);
		}

		$aResult['success'] = true;
		return json_encode($aResult);
	}

	public static function ajaxGetNamespaceFilter() {
		$aResult = array(
			"success" => false,
			"errors" => array(),
			"message" => '',
			"data" => array(),
		);

		global $wgUser;
		if( !$wgUser->isAllowed('read') ) {
			$aResult["message"] = wfMessage('bs-statistics-not-allowed')->plain();
			return json_encode($aResult);
		}

		foreach( BsNamespaceHelper::getNamespacesForSelectOptions( array( -2,-1 ) ) as $key => $name )
			$aResult['data'][] = array( 'key' => $key, 'displaytitle' => $name );

		$aResult['success'] = true;
		return json_encode($aResult);
	}

	public static function ajaxGetCategoryFilter() {
		$aResult = array(
			"success" => false,
			"errors" => array(),
			"message" => '',
			"data" => array(),
		);

		global $wgUser;
		if( !$wgUser->isAllowed('read') ) {
			$aResult["message"] = wfMessage('bs-statistics-not-allowed')->plain();
			return json_encode($aResult);
		}

		//$aCategories[wfMsg( 'bs-ns_all' )] = '(all)';
		$oDbr = wfGetDB( DB_SLAVE );
		$rRes = $oDbr->select('categorylinks', 'distinct cl_to', '', '', array('ORDER BY' => 'cl_to ASC') );
		while ( $oRow = $rRes->fetchObject() ) {
			$aResult['data'][] = array( 'key' => $oRow->cl_to, 'displaytitle' => $oRow->cl_to );
		}

		$aResult['success'] = true;
		return json_encode($aResult);
	}

	public static function ajaxGetSearchscopeFilter() {
		$aResult = array(
			"success" => false,
			"errors" => array(),
			"message" => '',
			"data" => array(),
		);

		global $wgUser;
		if( !$wgUser->isAllowed('read') ) {
			$aResult["message"] = wfMessage('bs-statistics-not-allowed')->plain();
			return json_encode($aResult);
		}

		foreach( array( 'title', 'text', 'files', 'all' ) as $value )
			$aResult['data'][] = array( 'key' => $value, 'displaytitle' => $value );

		$aResult['success'] = true;
		return json_encode($aResult);
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param boolean $bIsDefault default
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$this->getPortalConfig( $aPortalConfig );

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalPortlets( &$aPortlets ) {
		$this->getPortalPortlets( $aPortlets );

		return true;
	}

		/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param boolean $bIsDefault default
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$this->getPortalConfig( $aPortalConfig );

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalPortlets( &$aPortlets ) {
		$this->getPortalPortlets( $aPortlets );

		return true;
	}

	public function getPortalConfig( &$aPortalConfig ) {
		$aPortalConfig[1][] = array(
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfUsers',
			'config' => array(
				'title' => wfMessage( 'bs-statistics-portlet-numberofusers' )->plain(),
				'inputPeriod' => 'week',
			)
		);
		$aPortalConfig[1][] = array(
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfEdits',
			'config' => array(
				'title' => wfMessage( 'bs-statistics-portlet-numberofedits' )->plain(),
				'inputPeriod' => 'week',
			)
		);
		$aPortalConfig[2][] = array(
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfArticles',
			'config' => array(
				'title' => wfMessage( 'bs-statistics-portlet-numberofpages' )->plain(),
				'inputPeriod' => 'week',
			)
		);
		$aPortalConfig[2][] = array(
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfPages',
			'config' => array(
				'title' => wfMessage( 'bs-statistics-portlet-numberofpages' )->plain(),
				'inputPeriod' => 'week',
			)
		);

		return true;
	}

	public function getPortalPortlets( &$aPortlets ) {
		$aPortlets[] = array(
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfUsers',
			'config' => array(
				'title' => wfMessage( 'bs-statistics-portlet-numberofusers' )->plain(),
				'inputPeriod' => 'week',
			),
			'title' => wfMessage( 'bs-statistics-portlet-numberofusers' )->plain(),
			'description' => wfMessage( 'bs-statistics-portlet-numberofusersdesc' )->plain()
		);
		$aPortlets[] = array(
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfEdits',
			'config' => array(
				'title' => wfMessage( 'bs-statistics-portlet-numberofedits' )->plain(),
				'inputPeriod' => 'week',
			),
			'title' => wfMessage( 'bs-statistics-portlet-numberofedits' )->plain(),
			'description' => wfMessage( 'bs-statistics-portlet-numberofeditsdesc' )->plain()
		);
		$aPortlets[] = array(
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfArticles',
			'config' => array(
				'title' => wfMessage( 'bs-statistics-portlet-numberofpages' )->plain(),
				'inputPeriod' => 'week',
			),
			'title' => wfMessage( 'bs-statistics-portlet-numberofpages' )->plain(),
			'description' => wfMessage( 'bs-statistics-portlet-numberofpagesdesc' )->plain()
		);
		$aPortlets[] = array(
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfPages',
			'config' => array(
				'title' => wfMessage( 'bs-statistics-portlet-numberofpages' )->plain(),
				'inputPeriod' => 'week',
			),
			'title' => wfMessage( 'bs-statistics-portlet-numberofpages' )->plain(),
			'description' => wfMessage( 'bs-statistics-portlet-numberofpagesdesc' )->plain()
		);

		return true;
	}

}
