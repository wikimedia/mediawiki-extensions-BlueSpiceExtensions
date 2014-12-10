<?php
/**
 * Controls article index building mechanism for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/**
 * Wiki page indexing mechanism
 */
class BuildIndexMwArticles extends AbstractBuildIndexAll {

	/**
	 * I18n key for general error message
	 */
	const S_ERROR_MSG_KEY = 'error-indexing-wiki';

	/**
	 * Pointer to current database connection
	 * @var object Referenec to Database object
	 */
	protected $oDbr = null;
	/**
	 * List of documents to be indexed.
	 * @var resource Result of db query.
	 */
	protected $oDocumentsDb = null;
	/**
	 * Rounds for indexing
	 * @var int
	 */
	protected $iRounds = 0;

	/**
	 * Constructor for BuildIndexMwArticles class
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
		// just for num rows
		$this->oDocumentsDb = $this->oDbr->select( 'page', 'page_id', array(), __METHOD__ );
		$this->totalNoDocumentsCrawled = $this->oDbr->numRows( $this->oDocumentsDb );

		$sMessage = wfMessage( 'bs-extendedsearch-totalnoofarticles', $this->totalNoDocumentsCrawled )->plain() . "\n";
		$this->oMainControl->write( '', $sMessage );

		return $this->totalNoDocumentsCrawled;
	}

	/**
	 * Creates a document object that can be indexed by Solr.
	 * @param string $sPageTitle Title or filename
	 * @param string $sContent The body of the wiki-page or the document
	 * @param int $iPageID Id is -1 in case it's no wiki-article
	 * @param int $sPageNamespace Namespace of document, is 999 in case it's no wiki-article
	 * @param unknown $iTimestamp Timestamp
	 * @param array $aCategories Categories
	 * @param array $aEditors Editors
	 * @return Apache_Solr_Document
	 */
	public function makeSingleDocument( $sPageTitle, $sContent, $iPageID, $sPageNamespace,
			$iTimestamp, $aCategories, $aEditors, $aRedirects, $bIsRedirect, $aSections ) {

		return $this->oMainControl->makeDocument(
				'wiki', 'wiki', $sPageTitle, $sContent, $iPageID, $sPageNamespace, '', '',
				$iTimestamp, $aCategories, $aEditors, $aRedirects, $bIsRedirect, $aSections );
	}

	/**
	 * Fetches next results to index
	 * @param integer $iRound which round we are in
	 */
	public function loadNextDocuments( $iRound ) {
		if ( $iRound === 0 ) {
			$sOptions = array( 'LIMIT' => $this->iLimit );
		} else {
			$iOffset = $iRound * $this->iLimit;
			$sOptions = array( 'LIMIT' => $this->iLimit, 'OFFSET' => $iOffset );
		}

		$this->oDocumentsDb = $this->oDbr->select( 'page', 'page_id', array(), __METHOD__, $sOptions );
	}

	/**
	 * Indexes all documents that were found in crawl() method.
	 */
	public function indexCrawledDocuments() {
		$this->iRounds = ceil( $this->totalNoDocumentsCrawled / $this->iLimit );

		for ( $i = 0; $i < $this->iRounds; $i++ ) {
			$this->loadNextDocuments( $i );

			while ( $oDocument = $this->oDbr->fetchObject( $this->oDocumentsDb ) ) {
				$oTitle = Title::newFromID( $oDocument->page_id );
				$this->count++;
				if ( !$this->oMainControl->bCommandLineMode ) set_time_limit( $this->iTimeLimit ); // is needed ... else you can not create larger indexes

				wfRunHooks( 'BS::ExtendedSearch::IndexCrawlDocuments', array( &$oDocument ) );

				if ( $oDocument === null ) continue;

				$this->writeLog( $oTitle->getText() );

				$iPageID = $oTitle->getArticleID();
				$sPageTitle = $oTitle->getText();
				$sPageNamespace = $oTitle->getNamespace();

				$bIsRedirect = $oTitle->isRedirect();

				$sContent = $this->oMainControl->prepareTextForIndex( $oTitle );
				$aSections = $this->oMainControl->extractEditSections( $oTitle );
				$aRedirects = $this->oMainControl->getRedirects( $oTitle );

				$iTimestamp = $oTitle->getTouched();
				$aEditors = $this->oMainControl->getEditorsFromDbForCertainPageId( $iPageID );
				$aCategories = $this->oMainControl->getCategoriesFromDbForCertainPageId( $iPageID );
				if ( empty( $aCategories ) ) $aCategories = array( 'notcategorized' );

				$oSolrDocument = $this->makeSingleDocument(
						$sPageTitle, $sContent, $iPageID, $sPageNamespace, $iTimestamp,
						$aCategories, $aEditors, $aRedirects, $bIsRedirect, $aSections
				);

				$this->oMainControl->addDocument( $oSolrDocument, $this->mode, self::S_ERROR_MSG_KEY );

				wfRunHooks( 'BSExtendedSearchBuildIndexAfterAddArticle', array( $oTitle, $oSolrDocument ) );
			}
		}
	}

	/**
	 * Descructor for BuildIndexMwArticles class
	 */
	public function __destruct() {
		if ( $this->oDocumentsDb !== null ) {
			$this->oDbr->freeResult( $this->oDocumentsDb );
		}
	}

}