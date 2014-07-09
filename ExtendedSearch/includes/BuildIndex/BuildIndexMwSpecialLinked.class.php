<?php
/**
 * Controls special linked files index building mechanism for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
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
 * Controls special linked files index building mechanism for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class BuildIndexMwSpecialLinked extends AbstractBuildIndexMwLinked {

	/**
	 * I18n key for general error message
	 */
	const S_ERROR_MSG_KEY = 'error-indexing-special-linked';

	/**
	 * Pointer to current database connection
	 * @var object Referenec to Database object
	 */
	protected $oDbr;
	/**
	 * List of documents to be indexed.
	 * @var resource Result of db query.
	 */
	protected $documentsDb = null;
	/**
	 * Credentials for special link resoureces, e.g. Sharepoint or some folder under access control.
	 * @var array Username and password (format currently unclear)
	 */
	protected $aCredentials;


	public function __construct( $oMainControl ) {
		$this->oDbr = wfGetDB( DB_SLAVE );
		include( dirname( dirname( dirname( dirname( __DIR__ ) ) ) ).DS.'config'.DS.'ExtendedSearchSpecialLinked.php' );
		if ( isset( $credentials ) ) $this->aCredentials = $credentials;
		parent::__construct( $oMainControl );
	}

	/**
	 * Prepares list of documents to be indexed for wikis.
	 * @return int Number of documents to be indexed.
	 */
	public function crawl() {
		$this->writeLog();
//		$db_select_linked_files =
//			'SELECT ' // todo: distinct! Why index same document twice?
//			.$this->sDbPrefix."externallinks.el_to, "
//			.$this->sDbPrefix."page.page_touched, " // todo: unused?!
//			.$this->sDbPrefix."page.page_id "
//			.'FROM '
//			.$this->sDbPrefix."externallinks, "
//			.$this->sDbPrefix."page "
//			.'WHERE '
//			.$this->sDbPrefix."externallinks.el_to LIKE 'file://%' "
//			.'AND '
//			.$this->sDbPrefix."externallinks.el_from=".$this->sDbPrefix."page.page_id";
//		if ( $this->sLimit !== null )
//				$db_select_linked_files .= " LIMIT $this->sLimit";
//		$this->documentsDb = $this->oDbr->query( $db_select_linked_files );
//		$this->totalNoDocumentsCrawled = $this->oDbr->numRows( $this->documentsDb );
//		return $this->totalNoDocumentsCrawled;

		$tables = 'externallinks';
		$fields = 'DISTINCT el_to';
		$clauses = 'el_to LIKE \'file://%\'';
		$options = $this->getLimitForDb();
		$this->documentsDb = $this->oDbr->select( $tables, $fields, $clauses, __METHOD__, $options );
		$this->totalNoDocumentsCrawled = $this->oDbr->numRows( $this->documentsDb );
		return $this->totalNoDocumentsCrawled;
	}

	/**
	 * Creates a document object that can be indexed by Solr.
	 * @param string $type Of type 'wiki', 'doc', 'ppt', 'xls', 'pdf', 'txt', 'html', 'sql', 'sh', ...
	 * @param string $filename Filename of document to be indexed.
	 * @param string $fileText The body of the wiki-page or the document
	 * @param string $path Path to document if external (not in wiki). Might be empty or null
	 * @param unknown $timestamp Timestamp
	 * @return Apache_Solr_Document
	 */
	public function makeLinkedDocument( $type, $filename, &$fileText, $path, $timestamp ) {
		return $this->oMainControl->makeDocument( 'linked', $type, $filename, $fileText, -1, 999, $path, $timestamp );
	}

	/**
	 * Indexes all documents that were found in crawl() method.
	 */
	public function indexCrawledDocuments() {

		while ( $document = $this->oDbr->fetchObject( $this->documentsDb ) ) {
			$this->count++;
			//set_time_limit( $this->iTimeLimit );

			if ( $this->doesLinkedPathFilterMatch( $document->el_to ) ) continue;

			$path = str_replace( "file:///", "", $document->el_to );
			$ext = strtolower( array_pop( preg_split( '#[/\\.]+#', $path ) ) );
			$docType = $this->mimeDecoding( $ext ); // really needed for file extensions? or is it a relict from mw images (repo)
			if ( !isset( $this->aFileTypes[$docType] ) || ( $this->aFileTypes[$docType] !== true ) ) {
				$this->writeLog( ( 'Filetype not allowed: '.$docType.' ('.$document->el_to.')' ) );
				continue;
			}

			$size = @filesize( urldecode( $path ) );
			if ($size === false) {
				$this->writeLog( ( 'File not accessible: '.$document->el_to ) );
				continue;
			}
			if ( $this->sizeExceedsMaxDocSize( $size ) ){
				$this->writeLog( ( 'File exceeds max doc size and will not be indexed: '.$document->el_to ) );
				continue;
			}

			$filename = array_pop( explode( '/', $path ) );

			$time = @filemtime( urldecode( $path ) );
			$date = date( "YmdHis", $time );

			// Check if the file is already indexed
			try {
				$uniqueIdForDocument = $this->oMainControl->getUniqueId( $path, 'special-linked' );
				$hitsDocumentInIndexWithSameUID = $this->oMainControl->oSearchService->search( 'uid:'.$uniqueIdForDocument, 0, 1 );
			}
			catch ( Exception $e ) {
				$this->writeLog( 'Error indexing file '.$document->el_to.' with errormessage '.$e->getMessage() );
				continue;
			}

			// If already indexed and timestamp is not newer => don't index it!
			if ( $hitsDocumentInIndexWithSameUID->response->numFound != 0 ) {
				// timestamps have different format => compare function to equalize both
				$timestampIndexDoc = $hitsDocumentInIndexWithSameUID->response->docs[0]->ts;
				if ( !$this->isTimestamp1YoungerThanTimestamp2( $date, $timestampIndexDoc ) ) {
					$this->writeLog( ( 'Already in index: '.$document->el_to ) );
					continue;
				}
			}

			$fileInfo = new SplFileInfo( $path );
			$fileRealPath = $fileInfo->getRealPath();
			$fileText =& $this->oMainControl->oSearchService->getFileText( $fileRealPath );

			$doc = $this->makeLinkedDocument( $docType, $filename, $fileText, $path, $date );
			$this->writeLog( $path );
			if ( $doc ) {
				// mode and ERROR_MSG_KEY are only passed for the case when addDocument fails
				$this->oMainControl->addDocument( $doc, $this->mode, self::S_ERROR_MSG_KEY );
			}
		}
	}

	/**
	 * Descructor for BuildIndexMwArticles class
	 */
	public function __destruct() {
		if ( $this->documentsDb !== null )
			$this->oDbr->freeResult( $this->documentsDb );
	}

}