<?php
/**
 * Controls article index building mechanism for ExtendedSearch for MediaWiki
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
 * Controls article index building mechanism for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
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
	protected $oDbr;
	/**
	 * List of documents to be indexed.
	 * @var resource Result of db query.
	 */
	protected $oDocumentsDb = null;

	/**
	 * Constructor for BuildIndexMwArticles class
	 * @param BsBuildIndexMainControl $oBsBuildIndexMainControl Instance to decorate. 
	 */
	public function __construct( $oMainControl ) {
		$this->oDbr = wfGetDB( DB_SLAVE );
		parent::__construct( $oMainControl );
	}

	/**
	 * Prepares list of documents to be indexed for wikis.
	 * @return int Number of documents to be indexed.
	 */
	public function crawl() {
		$this->writeLog();
		$tables = array(
			'page',
			'text',
			'revision'
		);
		$fields = array(
			'page_id',
			'page_title',
			'page_namespace',
			'page_touched',
			'old_text'
		);
		$clauses = 'page_latest = rev_id AND rev_text_id = old_id';
		$options = $this->getLimitForDb();
		$this->oDocumentsDb = $this->oDbr->select( $tables, $fields, $clauses, __METHOD__, $options );
		$this->totalNoDocumentsCrawled = $this->oDbr->numRows( $this->oDocumentsDb );
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

		$oSolrDocument = $this->oMainControl->makeDocument(
				'wiki', 'wiki', $sPageTitle, $sContent, $iPageID, $sPageNamespace, '',
				$iTimestamp, $aCategories, $aEditors, $aRedirects, $bIsRedirect, $aSections );

		return $oSolrDocument;
	}

	/**
	 * Indexes all documents that were found in crawl() method.
	 */
	public function indexCrawledDocuments() {
		$oExtendedSearchBase = ExtendedSearchBase::getInstance();

		while ( $oDocument = $this->oDbr->fetchObject( $this->oDocumentsDb ) ) {
			$this->count++;
			set_time_limit( $this->iTimeLimit ); // is needed ... else you can not create larger indexes

			wfRunHooks( 'BS::ExtendedSearch::IndexCrawlDocuments', array( &$oDocument ) );

			if ( $oDocument === null ) continue;

			$this->writeLog( $oDocument->page_title );

			$iPageID        = $oDocument->page_id;
			$sPageTitle     = $oDocument->page_title;
			$sPageNamespace = $oDocument->page_namespace;

			$oTitle = Title::makeTitle( $sPageNamespace, $sPageTitle );

			$bIsRedirect = (int)$oTitle->isRedirect();

			$sContent = $this->oMainControl->parseTextForIndex( $oDocument->old_text, $oTitle );
			$aSections = $this->oMainControl->extractEditSections( $oDocument->old_text );
			$aRedirects = $this->oMainControl->getRedirects( $oTitle );

			$iTimestamp = $oDocument->page_touched;
			$aEditors = $oExtendedSearchBase->getEditorsFromDbForCertainPageId( $iPageID );
			$aCategories = $oExtendedSearchBase->getCategoriesFromDbForCertainPageId( $iPageID );
			if ( empty( $aCategories ) ) $aCategories = array( 'notcategorized' );

			$oSolrDocument = $this->makeSingleDocument(
					$sPageTitle, $sContent, $iPageID, $sPageNamespace, $iTimestamp,
					$aCategories, $aEditors, $aRedirects, $bIsRedirect, $aSections
			);

			$this->oMainControl->addDocument( $oSolrDocument, $this->mode, self::S_ERROR_MSG_KEY );

			wfRunHooks( 'BSExtendedSearchBuildIndexAfterAddArticle', array( $oTitle, $oSolrDocument ) );
		}
	}

	/**
	 * Descructor for BuildIndexMwArticles class
	 */
	public function __destruct() {
		if ( $this->oDocumentsDb !== null )
			$this->oDbr->freeResult( $this->oDocumentsDb );
	}

}