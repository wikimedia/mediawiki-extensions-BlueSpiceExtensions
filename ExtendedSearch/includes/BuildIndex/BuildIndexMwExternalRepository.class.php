<?php
/**
 * Controls repository index building mechanism for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/**
 * Controls repository index building mechanism for ExtendedSearch for MediaWiki
 */
class BuildIndexMwExternalRepository extends AbstractBuildIndexFile {

	/**
	 * I18n key for general error message
	 */
	const S_ERROR_MSG_KEY = 'error-indexing-repo';

	/**
	 * Array of Files to be indexed.
	 * @var resource Result of directory crawl.
	 */
	protected $aFiles = array();

	/**
	 * Constructor for BuildIndexMwLinked class
	 * @param BuildIndexMainControl $oMainControl Instance to decorate.
	 */
	public function __construct( $oMainControl ) {
		parent::__construct( $oMainControl );
	}

	/**
	 * Prepares array of files to be indexed
	 * @return true.
	 */
	public function crawl() {
		$sDirectories = BsConfig::get( 'MW::ExtendedSearch::ExternalRepo' );
		if ( $sDirectories === '' ) return $sDirectories;

		$aDirectories = explode( ',', $sDirectories );
		foreach ( $aDirectories as $sDirectory ) {
			$sDir = trim ( $sDirectory );
			if( !is_dir( $sDir ) ) continue;
			$this->readInFiles( $sDir );
		}
		return $this->aFiles;
	}

	/**
	 * Reads in all files from a directory
	 * @param string $sDir Directory to be indexed
	 */
	public function readInFiles( $sDir ) {
		$oCurrentDirectory = new DirectoryIterator( $sDir );
		foreach ( $oCurrentDirectory as $oFileinfo ) {
			if ( $oFileinfo->isFile() ) {
				$this->aFiles[] = $oFileinfo->getPathname();
				continue;
			}
			if ( $oFileinfo->isDir() && !$oFileinfo->isDot() && $oFileinfo->getFilename() != $sDir ) {
				$this->readInFiles( $oFileinfo->getPath() .DS. $oFileinfo->getFilename() );
			}
		}
	}

	/**
	 * Creates a document object that can be indexed by Solr.
	 * @param string $sType Of type 'wiki', 'doc', 'ppt', 'xls', 'pdf', 'txt', 'html', 'sql', 'sh', ...
	 * @param string $sFilename Filename of file to be indexed.
	 * @param string $sText The body of the file.
	 * @param string $sRealPath Path to file if external.
	 * @param unknown $vTimestamp Timestamp
	 * @return Apache_Solr_sFile
	 */
	public function makeRepoDocument( $sType, $sFilename, &$sText, $sRealPath, $vTimestamp ) {
		return $this->oMainControl->makeDocument( 'external', $sType, $sFilename, $sText, -1, 998, $sRealPath, $sRealPath, $vTimestamp );
	}

	/**
	 * Indexes all sFiles that were found in crawl() method.
	 */
	public function indexCrawledDocuments() {
		foreach ( $this->aFiles as $sFile ) {
			$oRepoFile = new SplFileInfo( $sFile );
			if ( !$oRepoFile->isFile() ) continue;

			$sFileName = $oRepoFile->getFilename();
			$sDocType = $this->mimeDecoding( $oRepoFile->getExtension() );
			if ( !$this->checkDocType( $sDocType, $sFileName ) ) continue;

			if ( !$this->oMainControl->bCommandLineMode ) set_time_limit( $this->iTimeLimit );

			if ( $this->sizeExceedsMaxDocSize( $oRepoFile->getSize(), $sFileName ) ) continue;

			$sRepoFileRealPath = $oRepoFile->getRealPath();
			$timestampImage = wfTimestamp( TS_ISO_8601, $oRepoFile->getMTime() );

			if ( $this->checkExistence( $sRepoFileRealPath, 'external', $timestampImage, $sFileName ) ) continue;

			$text = $this->getFileText( $sRepoFileRealPath, $sFileName );

			$doc = $this->makeRepoDocument( $sDocType, utf8_encode( $sFileName ), $text, utf8_encode( $sRepoFileRealPath ), $timestampImage );
			$this->writeLog( $sFileName );

			wfRunHooks( 'BSExtendedSearchBeforeAddExternalFile', array( $this, $oRepoFile, &$doc ) );

			if ( $doc ) {
				// mode and ERROR_MSG_KEY are only passed for the case when addsFile fails
				$this->oMainControl->addDocument( $doc, $this->mode, self::S_ERROR_MSG_KEY );
			}
		}
	}

	/**
	 * Descructor for BuildIndexMwExternalRepository class
	 */
	public function __destruct() {
		if ( $this->aFiles !== null )
			unset( $this->aFiles );
	}

}