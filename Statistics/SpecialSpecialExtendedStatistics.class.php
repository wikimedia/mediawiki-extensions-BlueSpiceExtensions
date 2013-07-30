<?php
/**
 * Renders the Statistics special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: SpecialExtendedStatistics.class.php 7886 2012-12-21 00:14:23Z mglaser $
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 *Statistics special page that renders the creation dialogue of statistics
 * @package BlueSpice_Extensions
 * @subpackage Statistics
 */
class SpecialExtendedStatistics extends SpecialPage {

	/**
	 * Constructor of SpecialExtendedStatistics
	 */
	public function __construct() {
		wfProfileIn( 'BS::SpecialExtendedStatistics::initExt' );
		parent::__construct( 'SpecialExtendedStatistics' );
		$this->oExtension = BsExtensionMW::getInstanceFor( 'MW::Statistics' );
		$this->oExtension->registerView( 'ViewDiagramStepI' );
		$this->oExtension->registerView( 'ViewDiagramStepII' );
		$this->oExtension->registerView( 'ViewDiagramStepIII' );

		BsCore::registerClass( 'MySQLDbReader', dirname(__FILE__).DS.'lib', 'MySQLDbReader.class.php' );
		BsCore::registerClass( 'PostGreSQLDbReader', dirname(__FILE__).DS.'lib', 'PostGreSQLDbReader.class.php' );
		BsCore::registerClass( 'OracleDbReader', dirname(__FILE__).DS.'lib', 'OracleDbReader.class.php' );
		BsCore::registerClass( 'StatsDataProvider', dirname(__FILE__).DS.'lib', 'StatsDataProvider.class.php' );
		BsCore::registerClass( 'Interval', dirname(__FILE__).DS.'lib', 'Interval.class.php' );
		BsCore::registerClass( 'BsCharting', dirname(__FILE__).DS.'lib', 'Charting.class.php' );
		BsCore::registerClass( 'BsDiagram', dirname(__FILE__).DS.'lib', 'Diagram.class.php' );

		wfProfileIn( 'BS::SpecialExtendedStatistics::initExt' );
	}

	/**
	 * Renders special page output.
	 * @param string $sParameter Name of the article, who's review should be edited, or user whos review should be displayed.
	 * @return bool Allow other hooked methods to be executed. always true.
	 */
	public function execute( $par ) {
		BsExtensionManager::setContext( 'MW::Statistics' );
		BsExtensionManager::setContext( 'MW::Statistics::ShowSpecialPage' );

		$this->setHeaders();
		//include('Charting.class.php');
		//include("diagrams.php");
		$oOutputPage = BsCore::getInstance( 'MW' )->getAdapter()->get( 'Out' );
		$sOut = '';
		$oOutputPage->setPagetitle( wfMsg( 'Statistics' ) );

		if ( !BsAdapterMW::checkAccessAdmission( 'statistics' ) ) {
			$sOut = wfMsg( 'bs-statistics-not-allowed' );
			$oOutputPage->addHTML( $sOut );
			return;
		}

		$aAvailableGrains = array( 'Y', 'm', 'W', 'd' );

		$sDiagFrom   = BsCore::getParam( 'hwpFrom',           date( "m/d/Y", strtotime( BsConfig::get( 'MW::Statistics::DefaultFrom' ) ) ) );
		// TODO MRG (09.12.10 12:28): Localize date?
		$sDiagTo     = BsCore::getParam( 'hwpTo',             date( "m/d/Y", time() ) );
		$sDiagType   = BsCore::getParam( 'hwpDiag',           false );
		$sDiagGrain  = BsCore::getParam( 'hwpGrain',          'm' );
		$sDiagMode   = BsCore::getParam( 'hwpMode',           BsDiagram::MODE_ABSOLUTE );
		$iStep       = BsCore::getParam( 'hwpStatisticsStep', false );

		if (!$iStep || $iStep == "1" || !$sDiagType) {
			$oDiagramStepIView = new ViewDiagramStepI();
			$oDiagramStepIView->setDiagramList( Statistics::getAvailableDiagrams() );
			$oDiagramStepIView->setActiveDiagram( $sDiagType );

			$sOut = $oDiagramStepIView->execute();
			$oOutputPage->addHTML( $sOut );
			return true;
		}

		if ($iStep == "2") {
			$oActiveDiagram = Statistics::getDiagram( $sDiagType );
			// TODO MRG (20.12.10 19:34): if ( !$oDiagram ) ERROR
			$oDiagramStepIIView = new ViewDiagramStepII();
			$oDiagramStepIIView->setDiagramList( Statistics::getAvailableDiagrams() );
			$oDiagramStepIIView->setActiveDiagram( $oActiveDiagram );

			//$oDiagramStepIIView->setActiveFilters( $aDiagFilter );
			$oDiagramStepIIView->setFrom( $sDiagFrom );
			$oDiagramStepIIView->setTo( $sDiagTo );
			$oDiagramStepIIView->setMode( $sDiagMode );
			$oDiagramStepIIView->setAvailableGrains( $aAvailableGrains );
			$oDiagramStepIIView->setActiveGrain( $sDiagGrain );

			$sOut = $oDiagramStepIIView->execute();
			$oOutputPage->addHTML( $sOut );
			return true;
		}

		//$diag = $diagram[$sDiagType];
		$oDiagram = Statistics::getDiagram( $sDiagType );
		$oDiagram->setStartTime( $sDiagFrom );
		$oDiagram->setEndTime( $sDiagTo );
		$oDiagram->setActualGrain( $sDiagGrain );
		$oDiagram->setModLabel( $sDiagGrain );
		$oDiagram->setMode( $sDiagMode );
		//$oDiagram->setFilters( $aDiagFilter );

		switch ( $oDiagram->getActualGrain() ) {
			// Here, only those grains are listed where label code differs from grain code.
			case 'm' : $oDiagram->setModLabel( 'M' ); break;
			case 'd' : $oDiagram->setModLabel( 'd.m' ); break;
			//default  : $oDiagram->modLabel = false;
		}

		switch ( $oDiagram->getDataSource() ) {
			case BsDiagram::DATASOURCE_DATABASE :
				$oBsAdapterMW = BsCore::getInstance( 'MW' )->getAdapter();
				$sDbType = $oBsAdapterMW->get( 'DBtype' );
				switch( $sDbType ) {
					case "postgres" : $oReader = new PostGreSQLDbReader(); break;
					case "oracle"   : $oReader = new OracleDbReader(); break;
					default         : $oReader = new MySQLDbReader();
				}
				//$oReader = $sDbType == 'mysql' ? new MySQLDbReader() : new PostGreSQLDbReader();
				$oReader->host = $oBsAdapterMW->get( 'DBserver' );
				$oReader->user = $oBsAdapterMW->get( 'DBuser' );
				$oReader->pass = $oBsAdapterMW->get( 'DBpassword' );
				$oReader->db   = $oBsAdapterMW->get( 'DBname' );
				break;
			/*case BsDiagram::DATASOURCE_LOGFILE :
				$oReader = new ApacheLogReader();
				$oReader->pathToLogfile = "C:\\Users\\Markus\\Desktop\\custom.log";
				$oReader->debug = false;
			 */
		}

		// TODO MRG (19.12.10 19:29): use new method: createFilename( $oDiagram );
		$sFilename = 'stat-';
		$sFilename .= $sDiagType.'-';
		$sFilename .= ( str_replace( '/', "", $sDiagFrom ) ).'-';
		$sFilename .= ( str_replace( '/', "", $sDiagTo ) ).'-';
		$sFilename .= $sDiagGrain.'-';
		foreach ( $oDiagram->getFilters() as $f => $v ) {
			if ( is_array( $v ) ) $val = join( '', $v );
			else $val = implode ( $v->getActiveValues() );
			$sFilename .= $f.$val.'-';
		}

		$sFilename .= $sDiagMode;
		$sFilename = md5( $sFilename ).'.png';

		$oDiagramStepIIIView = new ViewDiagramStepIII();
		$oDiagramStepIIIView->setActiveDiagram( $oDiagram );
		$oDiagramStepIIIView->setFrom( $sDiagFrom );
		$oDiagramStepIIIView->setTo( $sDiagTo );
		$oDiagramStepIIIView->setMode( $sDiagMode );
		$oDiagramStepIIIView->setAvailableGrains( $aAvailableGrains );
		$oDiagramStepIIIView->setActiveGrain( $sDiagGrain );
		$oDiagramStepIIIView->setFilename( $sFilename );

		$intervals = Interval::getIntervalsFromDiagram( $oDiagram );

		if ( count( $intervals ) > BsConfig::get( 'MW::Statistics::MaxNumberOfIntervals' ) ) {
			// TODO MRG (22.12.10 01:12): Error-Handling
			$sOut = '<font color="red">'.wfMsg( 'bs-statistics-interval-too-big' )."</font><br/>".$sOut;
			$oOutputPage->addHTML( $sOut );
			return true;
		} else {
			if ( !$oDiagram->isList() ) {
				if ( BsConfig::get( 'MW::Statistics::DisableCache' ) || !file_exists( BsConfig::get( 'MW::Statistics::DiagramDir' ).'/'.$sFilename ) ) {
					if ( !file_exists (BsConfig::get( 'MW::Statistics::DiagramDir' )) ) {
						mkdir( BsConfig::get( 'MW::Statistics::DiagramDir' ) );
					}
					if ( file_exists( BsConfig::get( 'MW::Statistics::DiagramDir' ).'/'.$sFilename ) ) {
							unlink( BsConfig::get( 'MW::Statistics::DiagramDir' ).'/'.$sFilename );
					}
					BsCharting::prepareData( $oDiagram, $oReader, $sFilename );
				}
			}
			//$sOut .= '<img src="'.$wgScriptPath.'/hallowelt/statistics/'.$d.'.png" />';
			if ( $oDiagram->isList() ) {
				$sList = BsCharting::prepareList( $oDiagram, $oReader );
				$oDiagramStepIIIView->setList( $sList );
			}
		}
		# Output
		$sOut = $oDiagramStepIIIView->execute();
		$oOutputPage->addHTML( $sOut );
		return true;
	}

}