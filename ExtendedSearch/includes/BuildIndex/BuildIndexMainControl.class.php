<?php
/**
 * Controls index building mechanism for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
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
	public $iDocsInIndex = 0;
	/**
	 * Path to lock file.
	 * @var string Path. 
	 */
	public $sFilePathLockFile = '';
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
	 * List of file types that can be indexed.
	 * @var array List of file extensions. Caution: these need to be known to the indexer.
	 */
	protected $aFiletypes = array();
	/**
	 * Maximum execution time per document to be indexed.
	 * @var int Execution time in seconds. 
	 */
	protected $iTimeLimit = 0;
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
	 * Maximum size for documents to be indexed.
	 * @var int File size in bytes.
	 */
	protected $iMaxDocSize = 0;
	/**
	 * Unique ID that identifies a certain Wiki
	 * @var string Unique ID
	 */
	protected $sCustomerId = '';
	/**
	 * Indicates whether builder was called from console
	 * @var bool True if called in cli mode.
	 */
	protected $bCommandLineMode = false;
	/**
	 * Array of file types that should be indexed
	 * @var array
	 */
	protected $aFileTypes = array();
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
		$this->sFilePathLogFile      = BSDATADIR.DS.'ExtendedSearch.log';
		$this->sFilePathLockFile     = BSDATADIR.DS.'ExtendedSearch.lock';

		//Possible values of PHP_SAPI (not all): apache, cgi (until PHP 5.3), cgi-fcgi, cli
		$this->bCommandLineMode = ( PHP_SAPI === 'cli' );

		/* $limit is null OR string with syntax "{start},{range}"
		 * if not set it defaults to false
		 *     then the existence of $_GET['start'] and $_GET['range'] is checked
		 *     in case of presence a limit is set with these parameters casted as integers */
		if ( !$this->bCommandLineMode ) {
			global $wgRequest;
			$aLimitStart = $wgRequest->getInt( 'start', -1 );
			$aLimitRange = $wgRequest->getInt( 'range', -1 );
			if ( $aLimitStart !== -1 && $aLimitRange !== -1 ) {
				$this->aLimit = $aLimitStart.','.$aLimitRange;
			}
		}

		if ( !empty( $this->aLimit ) ) {
			$this->aLimit = explode( ',', $this->aLimit );
			if ( count( $this->aLimit ) != 2 ) {
				throw new BsException( 'Invalid limit in '.__FILE__.', method'.__METHOD__ );
			}
		}

		if ( !$this->bCommandLineMode ) {
			$this->iTimeLimit = ini_get( 'max_execution_time' );
		} else {
			$this->iTimeLimit = 120;
		}

		$this->setFileTypes();
		$this->processTypes();
		$this->sCustomerId = BsConfig::get( 'MW::ExtendedSearch::CustomerID' );

		// Maximum file size in MB
		$iMaxFileSize = (int) ini_get( 'post_max_size' );
		if ( empty( $iMaxFileSize ) || $iMaxFileSize <= 0 ) $iMaxFileSize = 32;
		$this->iMaxDocSize = $iMaxFileSize * 1024 * 1024; // Make bytes out of it
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
	 * Triggers index building for wiki
	 * @param string $mode I18N string that is used in progress bar
	 */
	public function buildIndexWiki( $mode ) {
		if ( $this->aTypes['wiki'] !== true ) return;
		$oBuildIndexInstance = new BuildIndexMwArticles( $this );
		$oBuildIndexInstance
			->setMode( $mode )
			->setTimeLimit( $this->iTimeLimit )
			->setLimit( $this->aLimit );
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
		if ( $this->aTypes['speical'] !== true ) return;
		$oBuildIndexInstance = new BuildIndexMwSpecial( $this );
		$oBuildIndexInstance
			->setMode( $mode )
			->setTimeLimit( $this->iTimeLimit )
			->setLimit( $this->aLimit );
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
		$oBuildIndexInstance
			->setMode( $mode )
			->setTimeLimit( $this->iTimeLimit )
			->setFileTypes( $this->aFiletypes )
			->setMaxDocSize( $this->iMaxDocSize )
			->setLimit( $this->aLimit );
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
		$oBuildIndexInstance
			->setMode( $sMode )
			->setTimeLimit( $this->iTimeLimit )
			->setFileTypes( $this->aFiletypes )
			->setMaxDocSize( $this->iMaxDocSize )
			->setLimit( $this->aLimit );
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
		global $wgDBprefix;
		$aErrorMessageKeys = array();
		$everythingsOk = BuildIndexMwLinked::areYouAbleToRunWithSystemSettings( $aErrorMessageKeys );
		if ( !$everythingsOk ) {
			foreach ( $aErrorMessageKeys as $key => $value ) {
				$this->writeLog( $sMode, wfMessage( $key )->plain(), 0 );
			}
			return;
		}
		$oBuildIndexInstance = new BuildIndexMwLinked( $this );
		$oBuildIndexInstance
			->setMode( $sMode )
			->setTimeLimit( $this->iTimeLimit )
			->setFileTypes( $this->aFiletypes )
			->setMaxDocSize( $this->iMaxDocSize )
			->setLimit( $this->aLimit )
			->setDbPrefix( $wgDBprefix );
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
		global $wgDBprefix;
		$aErrorMessageKeys = array();
		$everythingsOk = BuildIndexMwSpecialLinked::areYouAbleToRunWithSystemSettings( $aErrorMessageKeys );
		if ( !$everythingsOk ) {
			foreach ( $aErrorMessageKeys as $key => $value ) {
				$this->writeLog( $sMode, wfMessage( $key )->plain(), 0 );
			}
			return;
		}
		$oBuildIndexInstance = new BuildIndexMwSpecialLinked( $this );
		$oBuildIndexInstance
			->setMode( $sMode )
			->setTimeLimit( $this->iTimeLimit )
			->setFileTypes( $this->aFiletypes )
			->setMaxDocSize( $this->iMaxDocSize )
			->setLimit( $this->aLimit )
			->setDbPrefix( $wgDBprefix );
		$noOfResults = $oBuildIndexInstance->crawl();
		if ( !empty( $noOfResults ) ) {
			$oBuildIndexInstance->indexCrawledDocuments();
		}
	}

	/**
	 * Completely (re)builds search index.
	 * @param SearchService $oSearchService
	 * @param string $aLimit Number and offset of articles to be indexed. Format {iLimitStart},{iLimitRange}
	 * @param array $aFiletypes List of file types to be indexed.
	 * @param array $aTypes List of document types to be indexed.
	 * @param int $iTimeLimit Maximum execution time per document in seconds.
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

		$this->oSearchService->commit( true, true, true, 60 );
		$this->oSearchService->optimize( true );

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

		$oDoc->wiki         = $this->sCustomerId;
		$oDoc->overall_type = $sOverallType;
		$oDoc->type         = $sType;
		$oDoc->title        = $sTitle;
		$oDoc->text         = $sText;
		$oDoc->hwid         = $iID;
		$oDoc->namespace    = $iNamespace;
		$oDoc->path         = $vPath;
		$oDoc->cat          = $aCategories;
		$oDoc->editor       = $aEditors;
		$oDoc->uid          = $this->getUniqueId( $iID, $vPath );
		$oDoc->redirects    = $aRedirects;
		$oDoc->redirect     = $bIsRedirect;
		$oDoc->sections     = $aSections;
		$oDoc->special      = $iIsSpecial;

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
			$sOutput = "{$sMode}: {$sMessage} ...{$sProgress}%";

			if ( $this->logFile() ) {
				$this->logFile( 'write', "{$sOutput}\n" ); // output to logFile
			}
		}

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

		$oParser        = new Parser();
		$oParserOptions = new ParserOptions();

		if ( preg_match( '#<rss.*?>#', $sText ) ) {
			return $sParsedText;
		}

		try {
			$sParsedText = $oParser->parse( $sText, $oTitle, $oParserOptions )->getText();
		} catch ( Exception $e ) {
			return $sParsedText;
		}
		$sParsedText = strip_tags( $sParsedText );
		$sParsedText = str_replace( $this->aFragsToBeReplaced, ' ', $sParsedText );

		return $sParsedText;
	}

	/**
	 * Extracts the edit sections out of a given text
	 * @param string $sText Text to be parsed
	 * @return Array array of sections
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
	 * @return Array array of redirects
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
	 * Sets the file types that should be indexed
	 */
	public function setFileTypes() {
		$vTempFileTypes = BsConfig::get( 'MW::ExtendedSearch::IndexFileTypes' );
		$vTempFileTypes = str_replace( array( ' ', ';' ), array( '', ',' ), $vTempFileTypes );
		$vTempFileTypes = explode( ',', $vTempFileTypes );
		foreach ( $vTempFileTypes as $value ) {
			$this->aFiletypes[$value] = true;
		}
		unset( $vTempFileTypes );

		return true;
	}

	/**
	 * Prepare available types against default values.
	 * @param array $aTypes List of types.
	 * @return BuildIndexMainControl Return self for method chaining.
	 */
	protected function processTypes() {
		$this->aTypes['wiki'] = (bool) BsConfig::get( 'MW::ExtendedSearch::IndexTypesWiki' );
		$this->aTypes['speical'] = (bool) BsConfig::get( 'MW::ExtendedSearch::IndexTypesSpecial' );
		$this->aTypes['repo'] = (bool) BsConfig::get( 'MW::ExtendedSearch::IndexTypesRepo' );
		$this->aTypes['linked'] = (bool) BsConfig::get( 'MW::ExtendedSearch::IndexTyLinked' );
		$this->aTypes['special-linked'] = (bool) BsConfig::get( 'MW::ExtendedSearch::IndexTypesSpecialLinked' );

		return true;
	}

}