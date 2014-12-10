<?php
/**
 * Controls repository index building mechanism for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
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
 * Controls repository index building mechanism for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class BuildIndexMwRepository extends AbstractBuildIndexFile {

	/**
	 * I18n key for general error message
	 */
	const S_ERROR_MSG_KEY = 'error-indexing-repo';
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
	 * Constructor for BuildIndexMwLinked class
	 * @param BuildIndexMainControl $oMainControl Instance to decorate.
	 */
	public function __construct( $oMainControl ) {
		parent::__construct( $oMainControl );
		$this->oDbr = wfGetDB( DB_SLAVE );
	}

	/**
	 * Prepares list of documents to be indexed for wikis.
	 * @return int Number of documents to be indexed.
	 */
	public function crawl() {
	 $this->writeLog();
		$tables = 'image';
		$fields = array(
			'img_name',
			'img_minor_mime',
			'img_timestamp'
		);
		$clauses = 'img_major_mime = \'application\'';

		$this->documentsDb = $this->oDbr->select( $tables, $fields, $clauses, __METHOD__ );
		$this->totalNoDocumentsCrawled = $this->oDbr->numRows( $this->documentsDb );

		return $this->totalNoDocumentsCrawled;
	}

	/**
	 * Creates a document object that can be indexed by Solr.
	 * @param string $type Of type 'wiki', 'doc', 'ppt', 'xls', 'pdf', 'txt', 'html', 'sql', 'sh', ...
	 * @param string $img_name Filename of document to be indexed.
	 * @param string $fileText The body of the wiki-page or the document
	 * @param string $realPath Path to document if external (not in wiki). Might be empty or null
	 * @param unknown $ts Timestamp
	 * @return Apache_Solr_Document
	 */
	public function makeRepoDocument( $type, $img_name, &$text, $realPath, $ts, $sVirtualPath ) {
		return $this->oMainControl->makeDocument( 'repo', $type, $img_name, $text, -1, 999, $realPath, $sVirtualPath, $ts );
	}

	/**
	 * Indexes all documents that were found in crawl() method.
	 */
	public function indexCrawledDocuments() {
		while ( $document = $this->oDbr->fetchObject( $this->documentsDb ) ) {
			$this->count++;

			 if ( !$this->oMainControl->bCommandLineMode ) set_time_limit( $this->iTimeLimit );

			$this->writeLog( $document->img_name );

			$docType = $this->mimeDecoding( $document->img_minor_mime, $document->img_name );
			if ( !$this->checkDocType( $docType, $document->img_name ) ) continue;

			$oTitle = Title::newFromText( $document->img_name, NS_FILE );
			$oFile = wfLocalFile( $oTitle );
			$sVirtualPath = $oFile->getPath();
			$oFileRepoLocalRef = $oFile->getRepo()->getLocalReference( $sVirtualPath );
			if ( is_null( $oFileRepoLocalRef ) ) continue;
			$path = $oFileRepoLocalRef->getPath();

			$repoFile = new SplFileInfo( $path );
			if ( !$repoFile->isFile() ) continue;

			if ( $this->sizeExceedsMaxDocSize( $repoFile->getSize(), $document->img_name ) ) continue;

			$repoFileRealPath = $repoFile->getRealPath();
			$timestampImage = $document->img_timestamp;

			if ( $this->checkExistence( $sVirtualPath, 'repo', $timestampImage, $document->img_name ) ) continue;

			$text = $this->getFileText( $repoFileRealPath, $document->img_name );

			$doc = $this->makeRepoDocument( $docType, $document->img_name, $text, $repoFileRealPath, $timestampImage, $sVirtualPath );
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
		if ( $this->documentsDb !== null ) $this->oDbr->freeResult( $this->documentsDb );
	}

}