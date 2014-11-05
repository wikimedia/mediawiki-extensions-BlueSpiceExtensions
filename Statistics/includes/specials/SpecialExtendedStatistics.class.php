<?php
/**
 * Renders the Statistics special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>

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
class SpecialExtendedStatistics extends BsSpecialPage {

	/**
	 * Constructor of SpecialExtendedStatistics
	 */
	public function __construct() {
		parent::__construct( 'ExtendedStatistics', 'read', true );
	}

	/**
	 * Renders special page output.
	 * @param string $sParameter Name of the article, who's review should be edited, or user whos review should be displayed.
	 * @return bool Allow other hooked methods to be executed. always true.
	 */
	public function execute( $par ) {
		parent::execute( $par );

		if( !empty($par) ) {
			global $wgRequest;
			$sData = $wgRequest->getVal('svg', '');
			if( !empty($sData) ) {
				switch( $par ) {
					case 'export-png': 
						return $this->exportPNG( $sData );
					case 'export-svg': 
						return $this->exportSVG( $sData );
				}
			}
		}

		$this->getOutput()->addHTML('<div id="bs-statistics-panel"></div>');
		$this->getOutput()->addModules('ext.bluespice.statistics');
		$this->getOutput()->setPagetitle( wfMessage( 'extendedstatistics' )->plain() );
		$bAllowPNGExport = false;
		global $wgSVGConverter, $wgSVGConverters;
		if( $wgSVGConverter != false && isset($wgSVGConverters[$wgSVGConverter]) ) {
			$bAllowPNGExport = true;
		}
		$this->getOutput()->addJsConfigVars( 'BsExtendedStatisticsAllowPNGExport', $bAllowPNGExport );

		return true;
	}
	
	/**
	 * 
	 * @global User $wgUser
	 * @global WebRequest $wgRequest
	 * @return type
	 */
	public static function ajaxSave() {
		$aResult = array(
			"success" => false,
			"errors" => array(),
			"message" => '',
			"data" => array(),
		);

		global $wgUser, $wgRequest;
		if( !$wgUser->isAllowed('read') ) {
			$aResult["message"] = wfMessage('bs-statistics-not-allowed')->plain();
			return json_encode($aResult);
		}
		$sDiagram	= $wgRequest->getVal('inputDiagrams', '');
		$sGrain		= $wgRequest->getVal('InputDepictionGrain', 'Y');
		$sFrom		= $wgRequest->getVal('inputFrom', '');
		$sMode		= $wgRequest->getVal('rgInputDepictionMode', '');
		$sTo		= $wgRequest->getVal('inputTo', '');
		
		$aAvailableDiagrams = Statistics::getAvailableDiagrams();
		$aAllowedDiaKeys = array_keys($aAvailableDiagrams);

		if( empty($sDiagram) ) $aResult["errors"]['inputDiagrams'] = wfMessage('bs-statistics-err-emptyinput')->plain();
		elseif( !in_array($sDiagram, $aAllowedDiaKeys) ) $aResult["errors"]['inputDiagrams'] = wfMessage('bs-statistics-err-unknowndia')->plain();

		if( !array_key_exists($sGrain, BsConfig::get('MW::Statistics::AvailableGrains')) ) 
			$aResult["errors"]['InputDepictionGrain'] = wfMessage('bs-statistics-err-unknowngrain')->plain();

		if( empty($sFrom) ) $aResult["errors"]['inputFrom'] = wfMessage('bs-statistics-err-emptyinput')->plain();
		elseif( !$oFrom = DateTime::createFromFormat('d.m.Y', $sFrom) ) $aResult["errors"]['inputFrom'] = wfMessage('bs-statistics-err-invaliddate')->plain();

		if( empty($sTo) ) $aResult["errors"]['inputTo'] = wfMessage('bs-statistics-err-emptyinput')->plain();
		elseif( !$oTo = DateTime::createFromFormat('d.m.Y', $sFrom) ) $aResult["errors"]['inputTo'] = wfMessage('bs-statistics-err-invaliddate')->plain();
		elseif( $oTo > new DateTime() ) $aResult["errors"]['inputTo'] = wfMessage('bs-statistics-err-invaliddate')->plain();

		if( isset($oFrom) && isset($oTo) && $oFrom > $oTo ) {
			$aResult["errors"]['inputTo'] = wfMessage('bs-statistics-err-invalidtofromrelation')->plain();
		}

		if( empty($sMode) ) $aResult["errors"]['rgInputDepictionMode'] = wfMessage('bs-statistics-err-emptyinput')->plain();
		elseif( !in_array($sMode, array('absolute', 'aggregated', 'list')) ) $aResult["errors"]['rgInputDepictionMode'] = wfMessage('bs-statistics-err-unknownmode')->plain();
		elseif( !isset($aResult["errors"]['inputDiagrams']) && $sMode == 'list' && !$aAvailableDiagrams[$sDiagram]->isListable()) 
			$aResult["errors"]['rgInputDepictionMode'] = wfMessage('bs-statistics-err-modeunsupported')->plain();

		if( !empty($aResult['errors']) ) {
			return json_encode($aResult);
		}

		$oDiagram = Statistics::getDiagram( $sDiagram );
		$oDiagram->setStartTime( $sFrom );
		$oDiagram->setEndTime( $sTo );
		$oDiagram->setActualGrain( $sGrain );
		$oDiagram->setModLabel( $sGrain );
		$oDiagram->setMode( $sMode );
		//$oDiagram->setMessage( $sMessage );
		//$oDiagram->setFilters( $aDiagFilter );

		switch ( $oDiagram->getActualGrain() ) {
			// Here, only those grains are listed where label code differs from grain code.
			case 'm' : $oDiagram->setModLabel( 'M' ); break;
			case 'd' : $oDiagram->setModLabel( 'd.m' ); break;
			//default  : $oDiagram->modLabel = false;
		}

		switch ( $oDiagram->getDataSource() ) {
			case BsDiagram::DATASOURCE_DATABASE :
				global $wgDBtype, $wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname;
				switch( $wgDBtype ) {
					case "postgres" : $oReader = new PostGreSQLDbReader(); break;
					case "oracle"   : $oReader = new OracleDbReader(); break;
					default         : $oReader = new MySQLDbReader();
				}
				//$oReader = $sDbType == 'mysql' ? new MySQLDbReader() : new PostGreSQLDbReader();
				$oReader->host = $wgDBserver;
				$oReader->user = $wgDBuser;
				$oReader->pass = $wgDBpassword;
				$oReader->db   = $wgDBname;
				break;
		}
		
		$intervals = Interval::getIntervalsFromDiagram( $oDiagram );
		if( count( $intervals ) > BsConfig::get( 'MW::Statistics::MaxNumberOfIntervals' ) ) {
			$aResult['message'] = wfMessage( 'bs-statistics-interval-too-big' )->plain();
			return json_encode($aResult); 
		}
		//set_time_limit( 60 );
		// TODO MRG (20.12.10 00:01): already called before
		//$intervals = Interval::getIntervalsFromDiagram( $oDiagram );
		//$oDiagram->addFilterText( wfMessage( 'bs-statistics-from-to', $oDiagram->getStartTime(), $oDiagram->getEndTime() )->plain() );
		// TODO MRG (20.12.10 00:19): This should be getModeText
		//$oDiagram->addFilterText( "\n".wfMessage( 'bs-statistics-mode' )->plain().": ".wfMessage( $oDiagram->getMessage() )->plain() );
		// PostgreSQL-Check (uses mwuser instead of user)
		global $wgDBtype;
		// Pls. keep the space after user, otherwise, user_groups is also replaced
		$sql = $oDiagram->getSQL();
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__user', '#__mwuser', $sql );
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__mwuser_', '#__user_', $sql );
		global $wgDBprefix;
		$sql = str_replace( "#__", $wgDBprefix, $sql );

		foreach ( $oDiagram->getFilters() as $oFilter ) {
			$sFilterSql = $oFilter->getSql();
			$sql = str_replace( $oFilter->getSqlKey(), $sFilterSql, $sql );
		}

		$oReader->match = $sql;
		$oDiagram->setData( BsCharting::getDataPerDateInterval( $oReader, $oDiagram->getMode(), $intervals, $oDiagram->isListable() ) );

		if ( $oDiagram->isList() ) {
			//$aResult['data']['list'] = BsCharting::drawTable($oDiagram);
			$aResult['data']['list'] = BsCharting::prepareList( $oDiagram, $oReader );
			$aResult['label'] = $oDiagram->getTitle();
			$aResult['success'] = true;
			return json_encode($aResult);
		}

		$aData = $oDiagram->getData();
		$i = 0;
		foreach ( $intervals as $interval ) {
			$aResult['data'][] = array(
				'name' => $interval->getLabel(),
				'hits' => (int)$aData[$i],
			);
			$i ++;
		}

		$aAvalableGrains = BsConfig::get( 'MW::Statistics::AvailableGrains' );
		$sLabelMsgKey = 'bs-statistics-label-time';
		if( isset($aAvalableGrains[$oDiagram->getActualGrain()]) ) {
			$sLabelMsgKey = $aAvalableGrains[$oDiagram->getActualGrain()];
		}

		$aResult['label'] = wfMessage( $sLabelMsgKey )->plain();

		$aResult['success'] = true;
		return json_encode($aResult);
	}

	private function exportPNG( $sData ) {
		$this->getOutput()->disable();

		global $wgRequest, $wgSVGConverter, $wgSVGConverters, $wgSVGConverterPath, $IP;
		if( $wgSVGConverter == false || !isset($wgSVGConverters[$wgSVGConverter]) ) {
			echo wfMessage('bs-statistics-err-converter')->plain();
			return false;
		}

		$sFileName = wfTimestampNow();
		$sFileExt = '.svg';

		$oStatus = BsFileSystemHelper::saveToCacheDirectory( $sFileName.$sFileExt, $sData, 'Statistics' );
		if( !$oStatus->isGood() ) {
			echo $oStatus->getMessage();
			return false;
		}

		$sCacheDir = $oStatus->getValue();

		$cmd = str_replace(
			array( '$path/', '$width', '$height', '$input', '$output' ),
			array( $wgSVGConverterPath ? wfEscapeShellArg( "$wgSVGConverterPath/" ) : "",
				intval( $wgRequest->getVal('width', 600) ),
				intval( $wgRequest->getVal('height', 400) ),
				wfEscapeShellArg( $sCacheDir.'/'.$sFileName.$sFileExt ),
				wfEscapeShellArg( $sCacheDir ) 
			),
			$wgSVGConverters[$wgSVGConverter]
		)." 2>&1";

		$err = wfShellExec( $cmd );
		unlink($sCacheDir.'/'.$sFileName.$sFileExt);

		$sFileExt = '.png';
		if( !file_exists($sCacheDir.'/'.$sFileName.$sFileExt) ) {
			echo $err;
			return false;
		}

		$this->getRequest()->response()->header("Content-Type:image/png");
		$this->getRequest()->response()->header("Content-Disposition:attachment; filename={$sFileName}{$sFileExt}");
		readfile( $sCacheDir.'/'.$sFileName.$sFileExt );
		unlink($sCacheDir.'/'.$sFileName.$sFileExt);
		return true;
	}

	private function exportSVG( $sData ) {
		$this->getOutput()->disable();

		$sName = wfTimestampNow();
		$this->getRequest()->response()->header("Content-Disposition:attachment; filename=$sName.svg");
		echo $sData;

		return true;
	}
}