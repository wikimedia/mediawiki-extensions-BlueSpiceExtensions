<?php
/**
 * Controls index building mechanism for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @package    BlueSpice_Extensions
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
 * Controls index building mechanism for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class BuildIndexMainControl {

	/**
	 * Instance of current search service
	 * @var BsSearchService
	 */
	public $oSearchService = null;
	/**
	 * Number of documents in index.
	 * @var int
	 */
	public $iDocsInIndex = 1;
	/**
	 * Path to lock file.
	 * @var string Path. 
	 */
	public $sFilePathLockFile = '';
	/**
	 * Indicates whether builder was called from console
	 * @var bool True if called in cli mode.
	 */
	public $bCommandLineMode = false;
	/**
	 * Reference to instance of base class.
	 * @var ExtendedSearchBaseMW Search base class.
	 */
	protected $oExtendedSearchBase = null;
	/**
	 * Types to be indexed.
	 * @var array List of type keys (strings)
	 */
	protected $aTypes = array();
	/**
	 * Limit offset and number of documents to be indexed.
	 * @var string {Offset}{Limit}
	 */
	protected $aLimit = array();
	/**
	 * Path to file where index progress is stored.
	 * @var string Path
	 */
	protected $sFilePathIndexProgTxt = '';
	/**
	 * Path to log file.
	 * @var string Path. 
	 */
	protected $sFilePathLogFile = '';
	/**
	 * Unique ID that identifies a certain Wiki
	 * @var string Unique ID
	 */
	protected $sCustomerId = '';
	/**
	 * Text fragments to be replaced
	 * @var array Fragments
	 */
	protected $aFragsToBeReplaced = array( '&nbsp;', '[Bearbeiten]', '[ Bearbeiten ]', '[ edit ]', '[edit]' );
	/**
	 * Instance of search service
	 * @var object of search service
	 */
	protected static $oInstance = null;

	/**
	 * Constructor for BuildIndexMainControl class
	 */
	public function __construct() {
		$this->oSearchService = SearchService::getInstance();

		$this->sFilePathIndexProgTxt = BSDATADIR.DS.'index_prog.txt';
		$this->sFilePathLogFile = BSDATADIR.DS.'ExtendedSearch.log';
		$this->sFilePathLockFile = BSDATADIR.DS.'ExtendedSearch.lock';

		//Possible values of PHP_SAPI (not all): apache, cgi (until PHP 5.3), cgi-fcgi, cli
		$this->bCommandLineMode = ( PHP_SAPI === 'cli' );

		$this->sCustomerId = BsConfig::get( 'MW::ExtendedSearch::CustomerID' );

		// Set major types to be indexed
		$this->aTypes['wiki'] = (bool)BsConfig::get( 'MW::ExtendedSearch::IndexTypesWiki' );
		$this->aTypes['special'] = (bool)BsConfig::get( 'MW::ExtendedSearch::IndexTypesSpecial' );
		$this->aTypes['repo'] = (bool)BsConfig::get( 'MW::ExtendedSearch::IndexTypesRepo' );
		$this->aTypes['linked'] = (bool)BsConfig::get( 'MW::ExtendedSearch::IndexTyLinked' );
		$this->aTypes['special-linked'] = (bool)BsConfig::get( 'MW::ExtendedSearch::IndexTypesSpecialLinked' );
	}

	/**
	 * Setter for major types that should be indexed
	 */
	public function setIndexTypes( $aTypes ) {
		$this->aTypes = $aTypes;
	}

	/**
	 * Return a instance of BuildIndexMainControl.
	 * @return BuildIndexMainControl Instance of BuildIndexMainControl
	 */
	public static function getInstance() {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( self::$oInstance === null ) {
			self::$oInstance = new self();
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return self::$oInstance;
	}

	/**
	 * Triggers a search index update for a specified article.
	 * @param Article $oArticle MediaWiki article object of article to be indexed.
	 * @param string $sText Text to be indexed (optional, fetched from article if not present)
	 */
	public function updateIndexWiki( $oArticle ) {
		if ( is_null( $oArticle ) ) return;
		$oBuildIndexMwArticles = new BuildIndexMwArticles( $this );

		$oTitle = $oArticle->getTitle();
		$oRevision = Revision::newFromTitle( $oTitle );
		if ( is_null( $oTitle ) || is_null( $oRevision ) ) return;

		wfRunHooks( 'BS::ExtendedSearch::UpdateIndexWiki', array( &$oTitle, &$oRevision ) );
		if ( is_null( $oTitle ) ) return;

		$iPageID = $oTitle->getArticleID();
		$iPageNamespace = $oTitle->getNamespace();
		$sPageTitle = $oTitle->getText();
		$iPageTimestamp = $oTitle->getTouched();
		$aPageCategories = $this->getCategoriesFromDbForCertainPageId( $iPageID );
		$aPageEditors = $this->getEditorsFromDbForCertainPageId( $iPageID );
		$bRedirect = $oTitle->isRedirect();

		$sPageContent = BsPageContentProvider::getInstance()->getContentFromRevision( $oRevision );

		if ( $bRedirect === true ) {
			$oRedirectTitle = ContentHandler::makeContent( $sPageContent, null, CONTENT_MODEL_WIKITEXT )->getUltimateRedirectTarget();
			if ( $oRedirectTitle instanceof Title ) {
				$oArticle = new Article( $oRedirectTitle );
				$this->updateIndexWiki( $oArticle );
			}
		}

		$aSections = $this->extractEditSections( $sPageContent );
		$sPageContent = $this->parseTextForIndex( $sPageContent, $oTitle );

		$aRedirects = $this->getRedirects( $oTitle );

		// http://www.mediawiki.org/wiki/Manual:WfTimestamp
		// wfTimestamp( TS_MW ) returns actual UTC in format YmdHis which results in gmdate( 'YmdHis', time() );
		// do not use date( 'YmdHis' ); it does not return GMT but timestamp with timezone-offset
		if ( strpos( $iPageTimestamp, '1970' ) === 0 ) $iPageTimestamp = wfTimestamp( TS_MW );

		$oSolrDocument = $oBuildIndexMwArticles->makeSingleDocument( $sPageTitle, $sPageContent, $iPageID, $iPageNamespace, $iPageTimestamp, $aPageCategories, $aPageEditors, $aRedirects, $bRedirect, $aSections );
		try {
			$this->oSearchService->addDocument( $oSolrDocument );
		} catch ( Exception $e ) {
			$this->logFile( 'write', __METHOD__ . ' - Error in _sendRawPost ' . $e->getMessage() );
		}

		try {
			// Indexing file links 
			$this->buildIndexLinked( '', $iPageID );
		} catch ( Exception $e ) {}

		$this->commitAndOptimize();
	}

	/**
	 * Triggers a search index update for a file.
	 * @param File $oFile file object.
	 */
	public function updateIndexFile( $oFile ) {
		$oIndexFile = new BuildIndexMwSingleFile( $this, $oFile );
		try {
			$oIndexFile->indexCrawledDocuments();
		} catch ( Exception $e ) {
			$this->logFile( 'write', __METHOD__ . ' - Error in _sendRawPost ' . $e->getMessage() );
		}

		$this->commitAndOptimize();

		return true;
	}

	/**
	 * Triggers deletion of a specified file from search index.
	 * @param int $id Article id of page to be deleted.
	 * @param string $path path to the file.
	 */
	public function deleteIndexFile( $iID, $sPath ) {
		$sUniqueID = $this->getUniqueId( $iID, $sPath );
		try {
			$this->oSearchService->deleteByQuery( 'uid:'.$sUniqueID );
		} catch ( Exception $e ) {
			$this->logFile( 'write', __METHOD__ . ' - Error in _sendRawPost ' . $e->getMessage() );
		}

		$this->commitAndOptimize();

		return true;
	}

	/**
	 * Triggers deletion of a specified item from search index.
	 * @param int $iID Article id of page to be deleted.
	 */
	public function deleteFromIndexWiki( $iID ) {
		$sUniqueID = $this->getUniqueId( $iID );
		try {
			$this->oSearchService->deleteById( $sUniqueID );
		} catch ( Exception $e ) {
			$this->logFile( 'write', __METHOD__ . ' - Error in _sendRawPost ' . $e->getMessage() );
		}

		$this->commitAndOptimize();

		return true;
	}

	/**
	 * Triggers search index update for a given title.
	 * @param Title $title MediaWiki title object of article to be updated.
	 */
	public function updateIndexWikiByTitleObject( $oTitle ) {
		$oArticle = new Article( $oTitle );
		$this->updateIndexWiki( $oArticle );

		return true;
	}

	/**
	 * Triggers index building for wiki
	 * @param string $mode I18N string that is used in progress bar
	 */
	public function buildIndexWiki( $mode ) {
		if ( $this->aTypes['wiki'] !== true ) return;
		$oBuildIndexInstance = new BuildIndexMwArticles( $this );
		$oBuildIndexInstance->setMode( $mode );

		$noOfResults = $oBuildIndexInstance->crawl();
		if ( !empty( $noOfResults ) ) {
			$oBuildIndexInstance->indexCrawledDocuments();
		}
	}

	/**
	 * Triggers index building for wiki specialpages
	 * @param string $mode I18N string that is used in progress bar
	 */
	public function buildIndexSpecial( $mode ) {
		if ( $this->aTypes['special'] !== true ) return;
		$oBuildIndexInstance = new BuildIndexMwSpecial( $this );
		$oBuildIndexInstance->setMode( $mode );

		$noOfResults = $oBuildIndexInstance->crawl();
		if ( !empty( $noOfResults ) ) {
			$oBuildIndexInstance->indexCrawledDocuments();
		}
	}

	/**
	 * Triggers index building for file repository
	 * @param string $mode I18N string that is used in progress bar
	 */
	public function buildIndexRepo( $mode ) {
		if ( $this->aTypes['repo'] !== true ) return;
		$oBuildIndexInstance = new BuildIndexMwRepository( $this );
		$oBuildIndexInstance->setMode( $mode );

		$noOfResults = $oBuildIndexInstance->crawl();
		if ( !empty( $noOfResults ) ) {
			$oBuildIndexInstance->indexCrawledDocuments();
		}
	}

	/**
	 * Triggers index building for external file repository
	 * @param string $sMode I18N string that is used in progress bar
	 */
	public function buildIndexExternalRepo( $sMode ) {
		if ( $this->aTypes['repo'] !== true ) return;
		$oBuildIndexInstance = new BuildIndexMwExternalRepository( $this );
		$oBuildIndexInstance->setMode( $sMode );

		$noOfResults = $oBuildIndexInstance->crawl();
		if ( !empty( $noOfResults ) ) {
			$oBuildIndexInstance->indexCrawledDocuments();
		}
	}

	/**
	 * Triggers index building for linked files
	 * @param string $sMode I18N string that is used in progress bar
	 * @param integer $iArticleID id of article to be indexed
	 */
	public function buildIndexLinked( $sMode, $iArticleID = null ) {
		if ( $this->aTypes['linked'] !== true ) return;
		$aErrorMessageKeys = array();
		$everythingsOk = BuildIndexMwLinked::areYouAbleToRunWithSystemSettings( $aErrorMessageKeys );
		if ( !$everythingsOk ) {
			foreach ( $aErrorMessageKeys as $key => $value ) {
				$this->writeLog( $sMode, wfMessage( $key )->plain(), 0 );
			}
			return;
		}

		global $wgDBprefix;
		$oBuildIndexInstance = new BuildIndexMwLinked( $this );
		$oBuildIndexInstance->setMode( $sMode );
		$oBuildIndexInstance->setDbPrefix( $wgDBprefix );

		$noOfResults = $oBuildIndexInstance->crawl( $iArticleID );
		if ( !empty( $noOfResults ) ) {
			$oBuildIndexInstance->indexCrawledDocuments();
		}
	}

	/**
	 * Triggers index building for linked files in special places like sharepoint
	 * @param string $sMode I18N string that is used in progress bar
	 */
	public function buildIndexSpecialLinked( $sMode ) {
		if ( $this->aTypes['special-linked'] !== true ) return;
		$aErrorMessageKeys = array();
		$everythingsOk = BuildIndexMwSpecialLinked::areYouAbleToRunWithSystemSettings( $aErrorMessageKeys );
		if ( !$everythingsOk ) {
			foreach ( $aErrorMessageKeys as $key => $value ) {
				$this->writeLog( $sMode, wfMessage( $key )->plain(), 0 );
			}
			return;
		}

		global $wgDBprefix;
		$oBuildIndexInstance = new BuildIndexMwSpecialLinked( $this );
		$oBuildIndexInstance->setMode( $sMode );
		$oBuildIndexInstance->setDbPrefix( $wgDBprefix );

		$noOfResults = $oBuildIndexInstance->crawl();
		if ( !empty( $noOfResults ) ) {
			$oBuildIndexInstance->indexCrawledDocuments();
		}
	}

	/**
	 * Completely (re)builds search index.
	 * @return string Always empty
	 */
	public function buildIndex() {
		// defaults to false; script execution is terminated if client disconnects
		ignore_user_abort( true );

		flush();

		libxml_use_internal_errors( true );

		ob_start();
		ob_flush();

		if ( !$this->bCommandLineMode ) header( "Content-Encoding: none" );

		// todo: what if zlib.output_compression = On in php.ini
		$this->logFile( 'create' );

		$this->logFile( 'write', date( "d.m.Y H:i:s" ) . "\n" );

		$sRes = '';
		try {
			if ( file_exists( $this->sFilePathLockFile ) ) {
				return $sRes = wfMessage( 'bs-extendedsearch-indexinginprogress' )->plain();
			}
			$this->lockFile( 'createLock' );

			$this->buildIndexWiki( wfMessage( 'bs-extendedsearch-indexing_wiki_articles' )->plain() );
			$this->buildIndexSpecial( wfMessage( 'bs-extendedsearch-indexing_specialpages' )->plain() );
			$this->buildIndexRepo( wfMessage( 'bs-extendedsearch-indexing_files_in_repo' )->plain() );
			$this->buildIndexLinked( wfMessage( 'bs-extendedsearch-indexing_linked_files' )->plain() );
			$this->buildIndexExternalRepo( wfMessage( 'bs-extendedsearch-indexing_external_files_in_repo' )->plain() );

			wfRunHooks( 'BSExtendedSearchBuildIndex', array( $this ) );
		} catch ( BsException $e ) {
			$this->logFile( 'deleteLock' );
			$sRes .= "Instance ExtendedSearchBase returned following BsException in procedure buildIndex(): {$e->getMessage()}";
		}

		$this->commitAndOptimize( true );

		$this->writeLog(
			wfMessage( 'bs-extendedsearch-finished' )->plain(),
			$this->iDocsInIndex . ' ' . wfMessage( 'bs-extendedsearch-docs-in-index' )->plain(),
			100
		);
		$this->logFile( 'write', date( "d.m.Y H:i:s" ) );

		$this->writeLog( 'unlink' );
		$this->lockFile( 'deleteLock' );

		ob_end_clean();

		libxml_clear_errors();

		return $sRes;
	}

	/**
	 * Main method to add documents to search index
	 * @param Apache_Solr_Document $oSolrDocument to send to search service and add to the index
	 * @param string $sMode In case of an error trigger writeLog with this mode (i18n'ned string)
	 * @param string $sMessageOnError In case of an error trigger writeLog with this message
	 * @param int $iTryagain Defaults to 0. In case of exception method is called recursively with tryagain incremented automatically
	 */
	public function addDocument( $oSolrDocument, $sMode, $sMessageOnError, $iTryagain = 0 ) {
		// Add the document to the index
		try {
			$this->oSearchService->addDocument( $oSolrDocument );
			$this->iDocsInIndex++;
			wfRunHooks( 'BS:ExtendedSearch:AddDocumentLog', array( $oSolrDocument ) );
		} catch ( Exception $ex ) {
			if ( $iTryagain >= 2 ) { // todo: make no of retries configurable
				$this->writeLog( $sMode, $sMessageOnError, 0 );
				// todo: count documents, that have not been able to index and output this statistic
			} else {
				// maybe solr has had too much to do so it was not always able to answer
				sleep( 3 ); // todo: make timespan to wait configurable
				$this->addDocument( $oSolrDocument, $sMode, $sMessageOnError, ++$iTryagain );
			}
		}
	}

	/**
	 * Central routine for converting document attributes to Apache_Solr_Document,
	 * that can be transferred to the Solr-Server and be stored in the index.
	 * @param string $sOverallType Of type 'wiki', 'repo', 'linked', 'external' or 'special-linked' (sharepoint)
	 * @param string $sType Of type 'wiki', 'doc', 'ppt', 'xls', 'pdf', 'txt', 'html', 'sql', 'sh', ...
	 * @param string $sTitle Title or filename
	 * @param string $sText The body of the wiki-page or the document
	 * @param int $iID Id is -1 in case it's no wiki-article
	 * @param int $iNamespace Namespace of document, is 999 in case it's no wiki-article
	 * @param mixed $vPath Path to document if external (not in wiki). Might be empty or null
	 * @param unknown $iTimestamp Timestamp
	 * @param array $aCategories Categories
	 * @param array $aEditors Editors
	 * @return Apache_Solr_Document
	 */
	public function makeDocument( $sOverallType, $sType, $sTitle, $sText, $iID, $iNamespace,
			$vPath, $iTimestamp, $aCategories = array(), $aEditors = array(),
			$aRedirects = array(), $bIsRedirect = 0, $aSections = array(), $iIsSpecial = 0 ) {
		$oDoc = new Apache_Solr_Document();

		$oDoc->wiki = $this->sCustomerId;
		$oDoc->overall_type = $sOverallType;
		$oDoc->type = $sType;
		$oDoc->title = $sTitle;
		$oDoc->text = $sText;
		$oDoc->hwid = $iID;
		$oDoc->namespace = $iNamespace;
		$oDoc->path = $vPath;
		$oDoc->cat = $aCategories;
		$oDoc->editor = $aEditors;
		$oDoc->uid  = $this->getUniqueId( $iID, $vPath );
		$oDoc->redirects = $aRedirects;
		$oDoc->redirect = $bIsRedirect;
		$oDoc->sections = $aSections;
		$oDoc->special = $iIsSpecial;

		// Date must be of the format 1995-12-31T23:59:59Z
		// If makeDocument is trigged by onArticleSaveComplete for example,
		// the timestamp is wfTimestamp and in this case you don't have to change it.
		// Only in case the timestamp is from the database.
		if ( preg_match( "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z/", $iTimestamp ) ) {
			$oDoc->ts = $iTimestamp;
		} else {
			if ( preg_match( "/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $iTimestamp ) ) {
				$oDoc->ts = preg_replace( "/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", "$1-$2-$3T$4:$5:$6Z", $iTimestamp );
			} else {
				global $wgDBtype;
				switch( $wgDBtype ) {
					case 'oracle' : 
						$oDoc->ts = preg_replace( "/(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2}):(\d{2}).(\d{6})/", "$3-$2-$1T$4:$5:$6Z", $iTimestamp ); 
						break;
					case 'postgres': 
						$oDoc->ts = preg_replace( "/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})\+00/", "$1-$2-$3T$4:$5:$6Z", $iTimestamp ); 
						break;
				}
			}
		}

		return $oDoc;
	}

	/**
	 * Reads out table %dbPrefix%categorylinks for certain page_id
	 * @param int $pageId ID of article that category links should be read for.
	 * @return array Categorynames as values
	 */
	public function getCategoriesFromDbForCertainPageId( $iPageID ) {
		$oDbr = wfGetDB( DB_SLAVE );

		// returns false on failure
		$oDbResTableCategories = $oDbr->select(
				'categorylinks',
				'DISTINCT cl_to',
				array( 'cl_from' => $iPageID )
		);

		$aCategories = array();
		if ( $oDbResTableCategories && $oDbr->numRows( $oDbResTableCategories ) > 0 ) {
			while ( $rowTableCategories = $oDbr->fetchObject( $oDbResTableCategories ) ) {
				$aCategories[] = $rowTableCategories->cl_to;
			}
		}
		$oDbr->freeResult( $oDbResTableCategories );

		return $aCategories;
	}

	/**
	 * Reads out table %dbPrefix%revision for certain page_id
	 * @param int $pageId ID of article that revisions should be read for.
	 * @return array editors as values
	 */
	public function getEditorsFromDbForCertainPageId( $iPageID ) {
		$oDbr = wfGetDB( DB_SLAVE );

		// returns false on failure
		$oDbResTableRevision = $oDbr->select(
				'revision',
				'DISTINCT rev_user_text',
				array( 'rev_page' => $iPageID )
		);

		$aEditors = array();
		if ( $oDbr->numRows( $oDbResTableRevision ) > 0 ) {
			$oUser = null;
			$sEditor = '';
			while ( $rowTableRevision = $oDbr->fetchObject( $oDbResTableRevision ) ) {
				$sEditor = $rowTableRevision->rev_user_text;
				$oUser = User::newFromName( $sEditor );
				if ( !is_object( $oUser ) ) $sEditor = 'unknown';
				$aEditors[] = $sEditor;
			}
		}
		$oDbr->freeResult( $oDbResTableRevision );

		return $aEditors;
	}

	/**
	 * Returns a unique id from parameter information.
	 * @param int $iID Id of an article.
	 * @param string $sPath Path to a file.
	 * @return string The unique ID in the index for a given document-id/-title/-path
	 */
	public function getUniqueId( $iID, $sPath = null ) {
		$sPath = str_replace( array( '/', '\\' ), '', $sPath );
		if ( ( $iID == -1 ) && empty( $sPath ) )
				throw new BsException( 'getUniqueId in BuildIndex has been called with $id == -1 and invalid $sPath: '.$sPath );
		/* md5 encoding of $path has several advantages
		 *    - almost unique and thus injective hash of any filepath
		 *    - 32 alphanumeric characters [0-9,a-f] => thus robust for encoding
		 *    - shorter and better performing than sha1 */
		return $this->sCustomerId.'-'.( ( $iID == -1 ) ? md5( $sPath ) : $iID );
	}

	/**
	 * Handles the search index lock file.
	 * @param string $sMode createLock, deleteLock
	 * @return bool False if there is no logfile, true if no mode is given, no return value otherwise.
	 */
	public function lockFile( $sMode = '' ) {
		if ( empty( $this->sFilePathLogFile ) ) return false;
		if ( $sMode == '' ) return true;

		switch ( $sMode ) {
			case 'createLock':
				touch( $this->sFilePathLockFile );
				return true;
			case 'deleteLock':
				if ( file_exists( $this->sFilePathLockFile ) ) unlink( $this->sFilePathLockFile );
				return true;
		}
	}

	/**
	 * Handles the search index log file.
	 * @param string $sMode delete, create, exists, is_writebale, write
	 * @param string $sData Text to write to file.
	 * @return bool False if there is no logfile, true if no mode is given, no return value otherwise.
	 */
	public function logFile( $sMode = '', $sData = '' ) {
		if ( empty( $this->sFilePathLogFile ) ) return false;
		if ( $sMode == '' ) return true;

		switch ( $sMode ) {
			case 'delete':
				if ( $this->logFile( 'exists' ) ) unlink( $this->sFilePathLogFile );
				return;
			case 'create':
				if ( $this->logFile( 'exists' ) ) $this->logFile( 'delete' );
				touch( $this->sFilePathLogFile );
				return true;
			case 'exists':
				return file_exists( $this->sFilePathLogFile );
			case 'write':
				if ( is_writable( $this->sFilePathLogFile ) ) {
					$rFh = fopen( $this->sFilePathLogFile, 'a' );
					fwrite( $rFh, $sData );
					fclose( $rFh );
				}
		}
	}

	/**
	 * Write log output
	 * @param string $sMode Information about the indexing area
	 * @param string $sMessage Message to log.
	 * @param string $sProgress Progress value between 0 and 100
	 * @return void Returns no value if mode was unlink and no value otherwise.
	 */
	public function writeLog( $sMode, $sMessage = '', $sProgress = '' ) {
		if ( $sMode == 'unlink' ) {
			if ( !$this->bCommandLineMode ) sleep( 2 );
			$this->writeLog( '__FINISHED__' );
			if ( !$this->bCommandLineMode ) sleep( 2 );
			unlink( $this->sFilePathIndexProgTxt );
			return;
		}

		$sMessage = addslashes( $sMessage );

		$bExtendedSearchIndexVerbose = true;
		if ( $bExtendedSearchIndexVerbose && $sMode != '__FINISHED__' ) {
			$sOutput = "{$this->iDocsInIndex}: {$sMode}: {$sProgress}% - {$sMessage}";

			if ( $this->logFile() ) {
				$this->logFile( 'write', "{$sOutput}\n" ); // output to logFile
			}
		}

		// dont need index_prog.txt output on cmd
		if ( $this->bCommandLineMode ) return;

		$sLine = '["'.$sMode.'", "'.$sMessage.'", "'.$sProgress.'"]';
		$rFh = fopen( $this->sFilePathIndexProgTxt, 'w' ); // output one line to file, recreating file each time (for ajax progress bar)
		fwrite( $rFh, $sLine );
		fclose( $rFh );
	}

	/**
	 * Parses text to be valid for indeing
	 * @param string $sText Text to be parsed
	 * @param object $oTitle Title object
	 * @return string Parsed text or empty on failure
	 */
	public function parseTextForIndex( $sText, $oTitle ) {
		$sParsedText = '';
		if ( empty( $sText ) || is_null( $oTitle ) ) return $sParsedText;

		// find all tags and sorround them with pre tags
		$sText = preg_replace_callback( '#<.*?>#', array( $this, 'preTags' ), $sText );

		// find all behaviour switches and replace them with nothing
		$sText = preg_replace( '#__[A-Z_]+__#', '', $sText );

		try {
			$sParsedText = BsCore::getInstance()->parseWikiText( $sText, $oTitle );
		} catch ( Exception $e ) {
			return $sParsedText;
		}

		$sParsedText = strip_tags( $sParsedText );
		$sParsedText = str_replace( $this->aFragsToBeReplaced, ' ', $sParsedText );
		$sParsedText = html_entity_decode( $sParsedText );

		return $sParsedText;
	}

	/**
	 * Callback function to return every tag surrounded with pre tags to avoid parsing
	 * @param array $aTags Array of matches
	 * @return string pre surrounded tag
	 */
	public function preTags( $aTags ) {
		return '<pre>' . $aTags[0] . '</pre>';
	}

	/**
	 * Extracts the edit sections out of a given text
	 * @param string $sText Text to be parsed
	 * @return array array of sections
	 */
	public function extractEditSections( $sText ) {
		$aSections = array();
		if ( empty( $sText ) ) return $aSections;

		$aMatches  = array();
		$aLines = explode( "\n", $sText );
		foreach ( $aLines as $sLine ) {
			if ( preg_match( '#^(=){1,6}(.*?)(=){1,6}$#', $sLine, $aMatches ) ) {
				$aSections[] = trim( $aMatches[2] );
			}
		}

		return $aSections;
	}

	/**
	 * Returns array of redirects for a given title
	 * @param object $oTitle Title object
	 * @return array array of redirects
	 */
	public function getRedirects( $oTitle ) {
		$aRedirects = array();
		if ( is_null( $oTitle ) ) return $aRedirects;

		foreach ( $oTitle->getRedirectsHere() as $oRedirect ) {
			$aRedirects[] = $oRedirect->getPrefixedText();
		}

		return $aRedirects;
	}

	/**
	 * Triggers commit and optimize xml update messages
	 * @param boolean $bOptimize optimize index or not
	 * @return object Always null
	 */
	public function commitAndOptimize( $bOptimize = false ) {
		// http://wiki.apache.org/solr/UpdateXmlMessages#A.22commit.22_and_.22optimize.22
		try {
			$this->oSearchService->commit( true, true, true, 60 );

			// Don't optimize on every call it is very expensive
			if ( $bOptimize === true ) {
				$this->oSearchService->optimize( true );
			}
		} catch ( Exception $e ) {}
	}

}