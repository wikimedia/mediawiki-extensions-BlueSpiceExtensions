<?php
/**
 * Index builder for files for ExtendedSearch
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @package    BlueSpice_Core
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v0.1
 * FIRST CHANGES
 */
/**
 * Indexer builder for files for ExtendedSearch
 * @package BlueSpice_Core
 * @subpackage ExtendedSearch
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
	public function sizeExceedsMaxDocSize( $iSize ) {
		if ( $this->iMaxDocSize === null || !is_int( $this->iMaxDocSize ) ) throw new BsException( 'iMaxDocSize not set or less than 1' );
		if ( $this->iMaxDocSize <= 0 ) return false;
		return ( $iSize > $this->iMaxDocSize );
	}

	/**
	 * Matches mime types to file extensions
	 * @param string $sMinorMime Mime type to match
	 * @return string File extension or original mime type
	 */
	public function mimeDecoding( $sMinorMime, $sFilename = '' ) {
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

}