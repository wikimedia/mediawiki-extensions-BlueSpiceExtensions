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
 * @author     Tobias Weichart <glaser@hallowelt.biz>
 * @version    1.22.0 stable
 * @version    $Id: Statistics.class.php 9745 2013-06-14 12:09:29Z pwirth $
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
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['Statistcs'] = dirname( __FILE__ ) . '/Statistics.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'Statistics',
			EXTINFO::DESCRIPTION => 'Statistics module for BlueSpice.',
			EXTINFO::AUTHOR      => 'Markus Glaser',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9745 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '1.22.0' )
		);
		$this->mExtensionKey = 'MW::Statistics';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of Statistics extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::Statistics::initExt' );
		$this->setHook( 'ParserFirstCallInit', 'onParserFirstCallInit' );
		$this->setHook( 'BSExtendedSearchAdminButtons' );
		$this->setHook( 'SpecialPage_initList' );
		$this->mAdapter->registerSpecialPage( 'SpecialExtendedStatistics', dirname( __FILE__ ), 'StatisticsAlias' );

		BsConfig::registerVar( 'MW::Statistics::DiagramDir',           'images/statistics',   BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING,       'bs-statistics-pref-DiagramDir' );
		BsConfig::registerVar( 'MW::Statistics::DiagramWidth',         700,                   BsConfig::LEVEL_USER|BsConfig::TYPE_INT,            'bs-statistics-pref-DiagramWidth', 'int' );
		BsConfig::registerVar( 'MW::Statistics::DiagramHeight',        500,                   BsConfig::LEVEL_USER|BsConfig::TYPE_INT,            'bs-statistics-pref-DiagramHeight', 'int' );
		//BsConfig::registerVar( 'MW::Statistics::DiagramType',          'line',                BsConfig::LEVEL_USER|BsConfig::TYPE_STRING,         $this->mI18N );
		BsConfig::registerVar( 'MW::Statistics::DiagramType',          'line',                BsConfig::LEVEL_USER|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-statistics-pref-DiagramType', 'select' );
		BsConfig::registerVar( 'MW::Statistics::DefaultFrom',          '01 January 2010',     BsConfig::LEVEL_USER|BsConfig::TYPE_STRING,         'bs-statistics-pref-DefaultFrom' );
		BsConfig::registerVar( 'MW::Statistics::DisableCache',         true,                  BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL,         'bs-statistics-pref-DisableCache', 'toggle' );

		BsConfig::registerVar( 'MW::Statistics::ExcludeUsers',         array( 'WikiSysop' ),  BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_ARRAY_STRING, 'bs-statistics-pref-ExcludeUsers', 'multiselectplusadd' );
		// default value equals 3 years in months
		BsConfig::registerVar( 'MW::Statistics::MaxNumberOfIntervals', 36,                    BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT,          'bs-statistics-pref-MaxNumberOfIntervals', 'int' );

		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ).'/extensions/BlueSpiceExtensions/Statistics/js', 'statistics', true, true, true, 'MW::Statistics' );
		$this->registerStyleSheet( BsConfig::get( 'MW::ScriptPath' ).'/extensions/BlueSpiceExtensions/Statistics/Statistics.css', true, 'MW::Statistics::ShowSpecialPage' );

		BsCore::registerClass( 'BsStatisticsFilter', dirname(__FILE__).DS.'lib', 'StatisticsFilter.class.php' );
		BsCore::registerClass( 'BsSelectFilter', dirname(__FILE__).DS.'lib', 'SelectFilter.class.php' );
		BsCore::registerClass( 'BsMultiSelectFilter', dirname(__FILE__).DS.'lib', 'MultiSelectFilter.class.php' );

		Statistics::addAvailableFilter( 'FilterUsers' );
		Statistics::addAvailableFilter( 'FilterNamespace' );
		Statistics::addAvailableFilter( 'FilterCategory' );
		// TODO MRG (21.12.10 11:44): Dependency on Search stats not resolved
		//if ( BsExtensionManager::isContextActive( 'MW::ExtendedSearch::Active' ) ) 
				Statistics::addAvailableFilter( 'FilterSearchScope' );

		BsCore::registerClass( 'BsDiagram', dirname(__FILE__).DS.'lib', 'Diagram.class.php' );
		Statistics::addAvailableDiagramClass( 'DiagramNumberOfUsers' );
		Statistics::addAvailableDiagramClass( 'DiagramNumberOfPages' );
		Statistics::addAvailableDiagramClass( 'DiagramNumberOfArticles' );
		Statistics::addAvailableDiagramClass( 'DiagramNumberOfEdits' );
		Statistics::addAvailableDiagramClass( 'DiagramEditsPerUser' );
		//if ( BsExtensionManager::isContextActive( 'MW::ExtendedSearch::Active' ) ) 
		Statistics::addAvailableDiagramClass( 'DiagramSearches' );

		wfProfileOut( 'BS::Statistics::initExt' );
	}

	public function onSpecialPage_initList( &$aList ) {
		$aList['Statistics'] = 'SpecialExtendedStatistics';
		return true;
	}

	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array( 'options' => array( 'line' => 'line', 'bar' => 'bar' ) );
		return $aPrefs;
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
		BsCore::registerClass( $sDiagramClassName, dirname(__FILE__).DS.'lib', $sDiagramClass.'.class.php' );
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
		BsCore::registerClass( $sFilterClassName, dirname(__FILE__).DS.'lib', $sFilterClass.'.class.php' );
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
		if (isset(Statistics::$aFilterDiagrams[$sFilterClass])) {
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
		$parser->setHook('bs:statistics:progress', array( &$this, 'onTagProgress' ) );
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
		$iBase =     BsCore::sanitizeArrayEntry( $args, 'basecount'     , 100              , BsPARAMTYPE::INT );
		$sFraction = BsCore::sanitizeArrayEntry( $args, 'progressitem'  , 'OK'             , BsPARAMTYPE::STRING );
		$iWidth =    BsCore::sanitizeArrayEntry( $args, 'width'         , 100              , BsPARAMTYPE::INT );

		// no Article when in cli mode
		if ( !is_object( $this->mAdapter->get( 'Article' ) ) ) {
			return '';
		}

		$sText = BsAdapterMW::get( 'Article' )->fetchContent();
		// substract 1 because one item is in the progressitem attribute
		$iFraction = substr_count( $sText, $sFraction ) - 1;
		$fPercent = $iFraction / $iBase;

		$iWidthGreen = floor($iWidth * $fPercent);
		$iWidthRemain = $iWidth-$iWidthGreen;

		$sPercent = sPrintf( "%0.1f", $fPercent * 100 );

		$sOut = '<div style="background-color:green;border:2px solid #DDDDDD;width:'.$iWidthGreen.'px;height:25px;float:left;color:#DDDDDD;text-align:center;border-right:0px;text-weight:bold;vertical-align:middle;">'.$sPercent.'%</div>';
		$sOut .= '<div style="border:2px solid #DDDDDD;border-left:0px;width:'.$iWidthRemain.'px;height:25px;float:left;"></div>';

		return $sOut;
	}

	public function onBSExtendedSearchAdminButtons( $oSpecialPage, &$aSearchAdminButtons ) {
		$sScriptPath = BsConfig::get( 'MW::ScriptPath' );
		$aSearchAdminButtons['Statistics'] = array(
			'href' => SpecialPage::getTitleFor( 'SpecialExtendedStatistics' )->getLinkUrl(),
			'onclick' => '',
			'label' => wfMsg( 'bs-extendedsearch-statistics' ),
			'image' => "$sScriptPath/extensions/BlueSpiceExtensions/Statistics/images/bs-searchstatistics.png"
		);
		return true;
	}

}
