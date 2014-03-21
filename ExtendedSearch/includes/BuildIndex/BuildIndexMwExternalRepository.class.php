<?php
/**
 * Controls repository index building mechanism for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v0.1
 * - initial commit
 */
/**
 * Controls repository index building mechanism for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
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
	 * @param BsBuildIndexMainControl $oBsBuildIndexMainControl Instance to decorate.
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
		$oSolrDocument = $this->oMainControl->makeDocument( '', $sType, $sFilename, $sText, -1, 998, $sRealPath, $vTimestamp );
		return $oSolrDocument;
	}

	// duplicate of AbstractBuildIndexMwLinked
	// they have no predecessor in common in bluespice-mw
	// todo: externalize static, may be to AdapterMW
	/**
	 * Compares two timestamps
	 * @param string $sTimestamp1 MW timestamp
	 * @param string $sTimestamp2 MW timestamp
	 * @return bool True if sTimestamp1 is younger than timestamp2
	 */
	public function isTimestamp1YoungerThanTimestamp2( $sTimestamp1, $sTimestamp2 ) {
		$ts_unix1 = wfTimestamp( TS_UNIX, $sTimestamp1 );
		$ts_unix2 = wfTimestamp( TS_UNIX, $sTimestamp2 );
		return ( $ts_unix1 > $ts_unix2 );
	}

	/**
	 * Indexes all sFiles that were found in crawl() method.
	 */
	public function indexCrawledDocuments() {

		foreach ( $this->aFiles as $sFile ) {
			$oRepoFile = new SplFileInfo( $sFile );

			$sDocType = $this->mimeDecoding( $oRepoFile->getExtension() );
			if ( !isset( $this->aFileTypes[$sDocType] ) || ( $this->aFileTypes[$sDocType] !== true ) ) {
				$this->writeLog( ( 'Filetype not allowed: '.$sDocType.' ('.$oRepoFile->getFilename() .')' ) );
				continue;
			}

			if ( !$this->oMainControl->bCommandLineMode ) set_time_limit( $this->iTimeLimit );

			try {
				$repoFileSize = $oRepoFile->getSize(); // throws Exception if file does not exist
			} catch ( Exception $e ) {
				$this->writeLog( ( 'File does not exist: '.$oRepoFile->getFilename() ) );
				continue;
			}
			if ( $this->sizeExceedsMaxDocSize( $repoFileSize ) ) {
				$this->writeLog( ( 'File exceeds max doc size and will not be indexed: '.$oRepoFile->getFilename() ) );
				continue;
			}

			$sRepoFileRealPath = $oRepoFile->getRealPath();

			try {
				$uniqueIdForFile = $this->oMainControl->getUniqueId( -1, $sRepoFileRealPath );
				$hitssFileInIndexWithSameUID = $this->oMainControl->oSearchService->search( 'uid:'.$uniqueIdForFile, 0, 1 );
			} catch ( Exception $e ) {
				$this->writeLog( 'Error indexing file '.$oRepoFile->getFilename().' with errormessage '.$e->getMessage() );
				continue;
			}

			$timestampImage = wfTimestamp( TS_ISO_8601, $oRepoFile->getMTime() );

			// If already indexed and timestamp is not newer => don't index it!
			if ( $hitssFileInIndexWithSameUID->response->numFound != 0 ) {
				// timestamps have different format => compare function to equalize both
				$timestampIndexDoc = $hitssFileInIndexWithSameUID->response->docs[0]->ts;
				if ( !$this->isTimestamp1YoungerThanTimestamp2( $timestampImage, $timestampIndexDoc ) ) {
					$this->writeLog( ('Already in index: '.$oRepoFile->getFilename() ) );
					continue;
				}
			}

			$text = '';
			try {
				$text = $this->oMainControl->oSearchService->getFileText( $sRepoFileRealPath, $this->iTimeLimit );
			} catch ( Exception $e ) { // Exception can be of type Exception OR BsException
				$this->writeLog( ( 'Unable to extract file '.$oRepoFile->getFilename().', errormessage: '.$e->getMessage() ) );
				error_log( $e->getMessage() );
			}

			$doc = $this->makeRepoDocument( $sDocType, utf8_encode( $oRepoFile->getFilename() ), $text, utf8_encode( $sRepoFileRealPath ), $timestampImage );
			$this->writeLog( $oRepoFile->getFilename() );

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