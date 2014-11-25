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
class BuildIndexMwSingleFile extends AbstractBuildIndexFile {

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
	 * Object of the uploaded File
	 * @var File
	 */
	protected $oFile;

	/**
	 * Constructor for BuildIndexMwLinked class
	 * @param BsBuildIndexMainControl $oBsBuildIndexMainControl Instance to decorate.
	 */
	public function __construct( $oMainControl, $oFile ) {
		parent::__construct( $oMainControl );
		$this->oFile = $oFile;
		$this->oDbr = wfGetDB( DB_SLAVE );
	}

	/**
	 * Prepares list of documents to be indexed for wikis.
	 * @return int Number of documents to be indexed.
	 */
	public function crawl() {}

	/**
	 * Creates a document object that can be indexed by Solr.
	 * @param string $type Of type 'wiki', 'doc', 'ppt', 'xls', 'pdf', 'txt', 'html', 'sql', 'sh', ...
	 * @param string $img_name Filename of document to be indexed.
	 * @param string $fileText The body of the wiki-page or the document
	 * @param string $realPath Path to document if external (not in wiki). Might be empty or null
	 * @param unknown $ts Timestamp
	 * @return Apache_Solr_Document
	 */
	public function makeRepoDocument( $type, $img_name, &$text, $realPath, $ts, $sVirtualFilePath ) {
		return $this->oMainControl->makeDocument( 'repo', $type, $img_name, $text, -1, 999, $realPath, $sVirtualFilePath, $ts );
	}

	/**
	 * Indexes document that was set in __construct.
	 */
	public function indexCrawledDocuments() {
		$sFileName = $this->oFile->getName();
		$oFileMinorDocType = $this->oDbr->selectRow(
			'image',
			'img_minor_mime',
			array(
				'img_name' => $sFileName,
				'img_major_mime' => 'application'
			)
		);
		if ( $oFileMinorDocType === false ) return;

		$sFileDocType = $this->mimeDecoding( $oFileMinorDocType->img_minor_mime, $sFileName );
		if ( !$this->checkDocType( $sFileDocType, $sFileName ) ) return;

		$sFileTimestamp = $this->oFile->getTimestamp();

		$sVirtualFilePath = $this->oFile->getPath();
		$oFileRepoLocalRef = $this->oFile->getRepo()->getLocalReference( $sVirtualFilePath );
		if ( !is_null( $oFileRepoLocalRef ) ) {
			$sFilePath = $oFileRepoLocalRef->getPath();
		}

		if ( $this->checkExistence( $sVirtualFilePath, 'repo', $sFileTimestamp, $sFileName) ) return;

		$sFileText = $this->getFileText( $sFilePath, $sFileName );

		$doc = $this->makeRepoDocument( $sFileDocType, $sFileName, $sFileText, $sFilePath, $sFileTimestamp, $sVirtualFilePath );
		if ( $doc ) {
			// mode and ERROR_MSG_KEY are only passed for the case when addDocument fails
			$this->oMainControl->addDocument( $doc, $this->mode, self::S_ERROR_MSG_KEY );
		}
	}

	/**
	 * Descructor for BuildIndexMwSingleFile class
	 */
	public function __destruct() {
		if ( $this->documentsDb !== null ) $this->oDbr->freeResult( $this->documentsDb );
	}

}