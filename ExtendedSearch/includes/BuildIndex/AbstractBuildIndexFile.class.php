<?php
/**
 * Index builder for files for ExtendedSearch
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Abstract build index class for files
 */
abstract class AbstractBuildIndexFile extends AbstractBuildIndexAll {

	/**
	 * List of file types to be indexed
	 * @var array Strings of file extensions
	 */
	protected $aFileTypes = array();
	/**
	 * Maximum size of documents to be indexed
	 * @var int Document size in bytes
	 */
	protected $iMaxDocSize = 0;

	/*
	 * Constructor
	 */
	public function __construct( $oMainControl ) {
		parent::__construct( $oMainControl );
		// Set file types to be indexed
		$vTempFileTypes = BsConfig::get( 'MW::ExtendedSearch::IndexFileTypes' );
		$vTempFileTypes = str_replace( array( ' ', ';' ), array( '', ',' ), $vTempFileTypes );
		$vTempFileTypes = explode( ',', $vTempFileTypes );

		foreach ( $vTempFileTypes as $value ) {
			$this->aFileTypes[$value] = true;
		}
		unset( $vTempFileTypes );

		// Maximum file size in MB
		$iMaxFileSize = (int)ini_get( 'post_max_size' );
		if ( empty( $iMaxFileSize ) || $iMaxFileSize <= 0 ) $iMaxFileSize = 32;
		$this->iMaxDocSize = $iMaxFileSize * 1024 * 1024; // Make bytes out of it
	}

	/**
	 * Checks system settings before indexing.
	 * @param array &$aErrorMessageKeys with keys to be filled as keys of error messages
	 * @return bool true if system's settings are ok to run with
	 */
	public static function areYouAbleToRunWithSystemSettings( &$aErrorMessageKeys = array() ) {
		if ( !ini_get( 'allow_url_fopen' ) ) {
			$aErrorMessageKeys['no_allow_url_include'] = true;
		}
		return parent::areYouAbleToRunWithSystemSettings( $aErrorMessageKeys );
	}

	/**
	 * Setter for aFileTyes
	 * @param array $aFileTypes Strings of file extensions
	 * @return BsAbstractBuildIndexFile Return self for method chaining.
	 */
	public function setFileTypes( $aFileTypes ) {
		$this->aFileTypes = $aFileTypes;
		return $this;
	}

	/**
	 * Setter for iMaxDocSize
	 * set to <=0 disables checking with sizeExceedsMaxDocSize
	 * @param mixed $vMaxDocSize numeric string or integer
	 * @return BsAbstractBuildIndexFile Return self for method chaining.
	 */
	public function setMaxDocSize( $vMaxDocSize ) {
		$this->iMaxDocSize = (int)$vMaxDocSize;
		return $this;
	}

	/**
	 * Checks whether a document is too big.
	 * @param int $iSize Size of document to check in bytes.
	 * @return bool True if document is too big.
	 */
	public function sizeExceedsMaxDocSize( $iSize, $sFileName ) {
		if ( $iSize > $this->iMaxDocSize ) {
			wfDebugLog( 'ExtendedSearch', __METHOD__ . ' File exceeds max doc size and will not be indexed: ' . $sFileName );
			return true;
		}
		return false;
	}

	/**
	 * Matches mime types to file extensions
	 * @param string $sMinorMime Mime type to match
	 * @return string File extension or original mime type
	 */
	protected function mimeDecoding( $sMinorMime, $sFilename = '' ) {
		$aDecodingTable = array(
			'msword' => 'doc',
			'vnd.ms-powerpoint' => 'ppt',
			'vnd.ms-excel' => 'xls',
			'htm' => 'html',
			'x-bash' => 'sh',
			'plain' => 'txt',
			'x-c' => 'txt',
			'x-c++' => 'txt',
			'perl' => 'txt',
			'text' => 'txt',
			'x-gzip' => 'zip',
			'vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xls',
			'vnd.openxmlformats-officedocument.spreadsheetml.template' => 'xls',
			'vnd.ms-excel.sheet.macroEnabled.12' => 'xls',
			'vnd.ms-excel.template.macroEnabled.12' => 'xls',
			'vnd.openxmlformats-officedocument.presentationml.template' => 'ppt',
			'vnd.openxmlformats-officedocument.presentationml.presentation' => 'ppt',
			'vnd.ms-powerpoint.presentation.macroEnabled.12' => 'ppt',
			'vnd.ms-powerpoint.template.macroEnabled.12' => 'ppt',
			'vnd.openxmlformats-officedocument.wordprocessingml.document' => 'doc',
			'vnd.openxmlformats-officedocument.wordprocessingml.template' => 'doc',
			'vnd.ms-word.document.macroEnabled.12' => 'doc',
			'vnd.ms-word.template.macroEnabled.12' => 'doc',
			'acad' => 'm',
			'x-mathcad' => 'mcd'
		);

		if ( array_key_exists( $sMinorMime, $aDecodingTable ) ) {
			$sMime = $aDecodingTable[$sMinorMime];
		} else {
			if ( $sMinorMime == 'vnd.ms-office' && !empty( $sFilename ) ) {
				$vType = explode( '.', $sFilename );
				$vType = array_pop( $vType );
				$sMime = $vType;
			} else {
				$sMime = $sMinorMime;
			}
		}
		return $sMime;
	}

	/**
	 * Checks if given doc type is allowed
	 * @param string $sDocType doc type of file
	 * @param string $sFileName file name - just for debug log
	 * @return bool
	 */
	protected function checkDocType( $sDocType, $sFileName ) {
		if ( !isset( $this->aFileTypes[$sDocType] ) || ( $this->aFileTypes[$sDocType] !== true ) ) {
			wfDebugLog( 'ExtendedSearch', __METHOD__ . ' Filetype not allowed: '.$sDocType.' ('.$sFileName.')' );
			return false;
		}
		return true;
	}

	/**
	 * Compares two timestamps
	 * @param string $timestamp1 MW timestamp
	 * @param string $timestamp2 MW timestamp
	 * @return bool true if timestamp1 is younger than timestamp2
	 */
	protected function compareTimestamps( $timestamp1, $timestamp2 ) {
		$ts_unix1 = wfTimestamp( TS_UNIX, $timestamp1 );
		$ts_unix2 = wfTimestamp( TS_UNIX, $timestamp2 );
		return ( $ts_unix1 > $ts_unix2 );
	}

	/**
	 * Checks if a later file version is already indexed
	 * @param string $sPath file path
	 * @param string $sType file index type
	 * @param int $iFileTs file timestamp
	 * @param string $sFileName file name - just for debug log
	 * @return bool
	 */
	protected function checkExistence( $sPath, $sType, $iFileTs, $sFileName ) {
		try {
			$sUid = $this->oMainControl->getUniqueId( $sPath, $sType );
			$oResponse = $this->oMainControl->oSearchService->search( 'uid:'.$sUid, 0, 1 );
		} catch ( Exception $e ) {
			wfDebugLog( 'ExtendedSearch', __METHOD__ . ' Error indexing file ' . $sFileName . ' with error message '.$e->getMessage() );
			return true;
		}

		if ( $oResponse->response->numFound != 0 ) {
			$timestampIndexDoc = $oResponse->response->docs[0]->ts;
			if ( !$this->compareTimestamps( $iFileTs, $timestampIndexDoc ) ) {
				wfDebugLog( 'ExtendedSearch', __METHOD__ . ' Already in index: ' . $sFileName );
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns text of a file
	 * @param string $sPath file path
	 * @param string $sFileName file name - just for debug log
	 * @return string file text or empty string
	 */
	protected function getFileText( $sPath, $sFileName ) {
		$sText = '';
		try {
			$sText = $this->oMainControl->oSearchService->getFileText( $sPath, $this->iTimeLimit );
		} catch ( Exception $e ) {
			wfDebugLog( 'ExtendedSearch', __METHOD__ . ' Unable to extract document ' . $sFileName . ', error message: ' . $e->getMessage() );
		}
		return $sText;
	}
}