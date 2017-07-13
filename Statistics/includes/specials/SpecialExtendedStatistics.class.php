<?php
/**
 * Renders the Statistics special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
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
		$this->getOutput()->setPageTitle( wfMessage( 'extendedstatistics' )->plain() );
		$bAllowPNGExport = false;
		global $wgSVGConverter, $wgSVGConverters;
		if( $wgSVGConverter != false && isset($wgSVGConverters[$wgSVGConverter]) ) {
			$bAllowPNGExport = true;
		}
		$this->getOutput()->addJsConfigVars( 'BsExtendedStatisticsAllowPNGExport', $bAllowPNGExport );

		return true;
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
				wfEscapeShellArg( $sCacheDir.'/'.$sFileName.'.png' )
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

	protected function getGroupName() {
		return 'bluespice';
	}
}
