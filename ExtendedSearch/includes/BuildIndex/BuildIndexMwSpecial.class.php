<?php
/**
 * Controls article index building mechanism for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/**
 * Controls Specialpage index building mechanism for ExtendedSearch for MediaWiki
 */
class BuildIndexMwSpecial extends AbstractBuildIndexAll {

	/**
	 * I18n key for general error message
	 */
	const S_ERROR_MSG_KEY = 'error-indexing-wiki';

	/**
	 * List of documents to be indexed.
	 * @var resource Result of db query.
	 */
	protected $oDocumentsDb = null;

	/**
	 * Constructor for BuildIndexMwArticles class
	 * @param BuildIndexMainControl $oMainControl Instance to decorate.
	 */
	public function __construct( $oMainControl ) {
		parent::__construct( $oMainControl );
	}

	/**
	 * Prepares list of documents to be indexed for wikis.
	 * @return int Number of documents to be indexed.
	 */
	public function crawl() {
		$this->oDocumentsDb = SpecialPageFactory::getUsablePages( RequestContext::getMain()->getUser() );
		$this->totalNoDocumentsCrawled = count( $this->oDocumentsDb );

		return $this->totalNoDocumentsCrawled;
	}

	/**
	 * Creates a document object that can be indexed by Solr.
	 * @param string $sPageTitle Title or filename
	 * @param string $sContent The body of the wiki-page or the document
	 * @param int $iPageID Id is -1 in case it's no wiki-article
	 * @param int $sPageNamespace Namespace of document, is 999 in case it's no wiki-article
	 * @return Apache_Solr_Document
	 */
	public function makeSingleDocument( $sPageTitle, $sContent, $iPageID, $sPageNamespace, $sPath, $iTimestamp, $iIsSpecial ) {
		return $this->oMainControl->makeDocument(
				'special', '', $sPageTitle, $sContent, $iPageID, $sPageNamespace,
				'', $sPath, $iTimestamp, array(), array(), array(), 0, array(), $iIsSpecial
		);
	}

	/**
	 * Indexes all documents that were found in crawl() method.
	 */
	public function indexCrawledDocuments() {
		foreach ( $this->oDocumentsDb as $oSpecialPage ) {
			$this->count++;
			if ( !$this->oMainControl->bCommandLineMode ) set_time_limit( $this->iTimeLimit );

			$sPageTitle = $oSpecialPage->getTitle()->getText();
			$iTimestamp = wfTimestampNow();
			$iIsSpecial = (int)$oSpecialPage->getTitle()->isSpecialPage();

			$this->writeLog( $sPageTitle );

			$oSolrDocument = $this->makeSingleDocument( $sPageTitle, '', -1, 1000, $sPageTitle, $iTimestamp, $iIsSpecial );

			$this->oMainControl->addDocument( $oSolrDocument, $this->mode, self::S_ERROR_MSG_KEY );
		}
	}

	/**
	 * Descructor for BuildIndexMwArticles class
	 */
	public function __destruct() {
		if ( $this->oDocumentsDb !== null )
			unset( $this->oDocumentsDb );
	}

}