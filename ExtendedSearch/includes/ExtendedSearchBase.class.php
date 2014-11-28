<?php
/**
 * Base class for ExtendedSearch for MediaWiki
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
/* Changelog
 * v0.1
 * FIRST CHANGES
 */
/**
 * Base class for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ExtendedSearchBase {

	/**
	 * Instance of RequestContext
	 * @var object RequestContext
	 */
	protected $oContext = null;
	/**
	 * Instance of SearchService
	 * @var object $oSearchService
	 */
	protected $oSearchService = null;
	/**
	 * Instance of SearchRequest.
	 * @var object SearchRequest
	 */
	protected $oSearchRequest = null;
	/**
	 * Instance of SearchOptions.
	 * @var object SearchOptions
	 */
	protected $oSearchOptions = null;
	/**
	 * Instance of SearchUriBuilder.
	 * @var object SearchUriBuilder
	 */
	protected $oSearchUriBuilder = null;
	/**
	 * Instance of SearchIndex.
	 * @var object SearchIndex.
	 */
	protected $oSearchIndex = null;
	/**
	 * Instance of ExtendedSearchBase
	 * @var object ExtendedSearchBase
	 */
	protected static $oInstance = null;

	/**
	 * Constructor of ExtendedSearchBase class
	 */
	public function __construct( $oContext ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		try {
			$this->oSearchService = SearchService::getInstance();
		} catch ( BsException $e ) {
			wfProfileOut( 'BS::'.__METHOD__ );
			return null;
		}

		$this->oContext = $oContext;
		$this->oSearchRequest = new SearchRequest();
		$this->oSearchOptions = new SearchOptions( $this->oSearchRequest, $this->oContext );
		$this->oSearchUriBuilder = new SearchUriBuilder( $this->oSearchRequest, $this->oSearchOptions );
		$this->oSearchIndex = new SearchIndex( $this->oSearchService, $this->oSearchRequest,
			$this->oSearchOptions, $this->oSearchUriBuilder, $this->oContext );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Return a instance of ExtendedSearchBase.
	 * @return ExtendedSearchBase Instance of ExtendedSearchBase
	 */
	public static function getInstance( $Context ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( self::$oInstance === null ) {
			self::$oInstance = new self( $Context );
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return self::$oInstance;
	}

	/**
	 * Checks if curl-extension is activated
	 * @return boolean
	 */
	public static function isCurlActivated() {
		return in_array( 'curl', get_loaded_extensions() );
	}

	/**
	 * Magic getter method
	 * @param string $sName Name of variable to get.
	 * @return mixed Value of the requested member variable.
	 */
	public function __get( $sName ) {
		return ( isset( $this->$sName ) ) ? $this->$sName : null;
	}

	/**
	 * Renders the inner content of search result page.
	 * @return ViewBaseElement View with inner content of search result page.
	 */
	public function renderSpecialpage() {
		$this->oSearchRequest->init();
		$this->oSearchOptions->readInSearchRequest();
		$this->oSearchUriBuilder->init();

		// Form and results views are added via addItem to a ViewBaseElement
		$oSearchformView = new ViewBaseElement();
		$oSearchform = new ViewExtendedSearchFormPage();
		$oSearchform->setRequest( $this->oSearchRequest );
		$aMonitor = array();

		if ( $this->oSearchOptions->getOptionBool( 'bExtendedForm' ) ) {
			$aMonitor['linkToExtendedPageMessageKey'] = 'bs-extendedsearch-specialpage-form-return-to-simple';
			$aMonitor['linkToExtendedPageUri'] = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL );

			$this->renderExtendedForm( $oSearchform );
			$oSearchform->setOptions( $aMonitor );
			$oSearchformView->addItem( $oSearchform );
			return $oSearchformView;
		} else {
			$aMonitor['linkToExtendedPageMessageKey'] = 'bs-extendedsearch-specialpage-form-expand-to-extended';
			$aMonitor['linkToExtendedPageUri'] = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL | SearchUriBuilder::EXTENDED );
		}

		$oSearchform->setOptions( $aMonitor );
		$oSearchformView->addItem( $oSearchform );

		$this->oContext->getOutPut()->addHTML( $oSearchformView->execute() );

		return $this->getResults( false );
	}

	/**
	 * Starts a search request
	 * @param boolean $bAjax Ajax request or not
	 * @return object ViewBaseElement
	 */
	public function getResults( $bAjax ) {
		if ( $bAjax === true ) {
			$this->oSearchRequest->init();
			$this->oSearchOptions->readInSearchRequest();
			$this->oSearchUriBuilder->init();
		}

		$oView = new ViewBaseElement();
		$oView->setId( 'bs-extendedsearch-specialpage-body' );
		$aMonitor = array();

		try {
			$oResultView = $this->search( $aMonitor );
		} catch ( BsException $e ) {
			if ( $e->getMessage() == 'redirect' ) return;
			throw $e;
		}

		$vNoOfResultsFound = new ViewNoOfResultsFound();
		$vNoOfResultsFound->setOptions( $aMonitor );

		$oView->addItem( $vNoOfResultsFound );
		$oView->addItem( $oResultView );

		return $oView;
	}

	/**
	 * Renders extended search options form.
	 * @param array $aMonitor List that contains form view.
	 * @return ViewBaseElement View that describes search options.
	 */
	public function renderExtendedForm( &$aMonitor ) {
		$aHiddenFieldsInForm = array();
		$aHiddenFieldsInForm['search_asc'] = $this->oSearchOptions->getOption( 'asc' );
		$aHiddenFieldsInForm['search_order'] = $this->oSearchOptions->getOption( 'order' );// score|titleSort|type|ts
		$aHiddenFieldsInForm['search_submit'] = '1';

		$aMonitor->setOptions(
			array(
				'hiddenFields' => $aHiddenFieldsInForm,
				'files' => $this->oSearchOptions->getOption( 'files' ),
				'method' => 'get',
				'scope' => $this->oSearchOptions->getOption( 'scope' )
			)
		);

		$vOptionsFormWiki = $aMonitor->getOptionsForm( 'wiki', '' );
		$this->getExtendedFormNamespacesAndFilesBox( $vOptionsFormWiki );

		$this->getExtendedFormCategoriesBox( $vOptionsFormWiki );
		$this->getExtendedFormEditorsBox( $vOptionsFormWiki );
	}

	/**
	 * Get namespace and files box for extended form
	 * @param object $vOptionsFormWiki ViewSearchExtendedOptionsForm
	 * @global object $wgContLang Content language
	 */
	private function getExtendedFormNamespacesAndFilesBox( $vOptionsFormWiki ) {
		global $wgContLang;
		$vNamespaceBox = $vOptionsFormWiki->getBox( 'NAMESPACE-FIELD', 'bs-extendedsearch-search-namespace', 'na[]' );
		$aMwNamespaces = $wgContLang->getNamespaces();
		$aSelectedNamespaces = $this->oSearchOptions->getOption( 'namespaces' );

		if ( BsConfig::get( 'MW::SortAlph' ) ) asort( $aMwNamespaces );

		foreach ( $aMwNamespaces as $namespace ) {
			$iNsIndex = $wgContLang->getNsIndex( $namespace );
			if ( $iNsIndex < 0 ) continue;
			if ( $iNsIndex == 0 ) $namespace = wfMessage( 'bs-ns_main' )->plain();
			$vNamespaceBox->addEntry(
				$iNsIndex,
				array(
					'value' => $iNsIndex,
					'text' => $namespace,
					'selected' => in_array( (string) $iNsIndex, $aSelectedNamespaces )
				)
			);
		}

		$checkboxSearchFilesAttributes = array(
			'type' => 'checkbox',
			'id' => 'bs-extendedsearch-checkbox-searchfiles'
		);

		if ( BsConfig::get( 'MW::ExtendedSearch::SearchFiles' ) || $this->oSearchOptions->getOption( 'files' ) ) {
			$checkboxSearchFilesAttributes['checked'] = 'checked';
		}
		$checkboxSearchFiles = Xml::input( 'search_files', false, 1, $checkboxSearchFilesAttributes );
		$checkboxSearchFiles .= wfMessage( 'bs-extendedsearch-files' )->plain();

		$vNamespaceBox->dirtyAppend( '<br />'.$checkboxSearchFiles );
	}

	/**
	 * Get ncategories box for extended form
	 * @param object $vOptionsFormWiki ViewSearchExtendedOptionsForm
	 */
	private function getExtendedFormCategoriesBox( $vOptionsFormWiki ) {
		$oDbr = wfGetDB( DB_SLAVE );
		$catRes = $oDbr->select(
			array( 'category' ),
			array( 'cat_id', 'cat_title' ),
			'',
			null,
			array( 'ORDER BY' => 'cat_title asc' )
		);

		if ( $oDbr->numRows( $catRes ) != 0 ) {
			$vCategoryBox = $vOptionsFormWiki->getBox( 'CATEGORY-FIELD', 'bs-extendedsearch-search-category', 'ca[]' );
			$aSelectedCategories = $this->oSearchOptions->getOption( 'cats' );
			foreach ( $catRes as $row ) {
				$vCategoryBox->addEntry(
					$row->cat_title,
					array(
						'value' => $row->cat_title,
						'text' => $row->cat_title,
						'selected' => in_array( $row->cat_title, $aSelectedCategories )
					)
				);
			}
		}
		$oDbr->freeResult( $catRes );
	}

	/**
	 * Get editors box for extended form
	 * @param object $vOptionsFormWiki ViewSearchExtendedOptionsForm
	 */
	private function getExtendedFormEditorsBox( $vOptionsFormWiki ) {
		$oDbr = wfGetDB( DB_SLAVE );
		$vEditorsBox = $vOptionsFormWiki->getBox( 'EDITORS-FIELD', 'bs-extendedsearch-search-editors', 'ed[]' );
		$edRes = $oDbr->select(
			array( 'revision' ),
			array( 'DISTINCT rev_user' ),
			'',
			null,
			array( 'ORDER BY' => 'rev_user_text' )
		);

		$aSelectedEditors = $this->oSearchOptions->getOption( 'editor' );
		foreach ( $edRes as $row ) {
			$oUser = User::newFromId( $row->rev_user );
			if ( !is_object( $oUser ) || User::isIP( $oUser->getName() ) ) continue;

			$vEditorsBox->addEntry(
				$oUser->getName(),
				array(
					'value' => $oUser->getName(),
					'text' => $oUser->getName(),
					'selected' => in_array( $oUser->getName(), $aSelectedEditors )
				)
			);
		}
		$oDbr->freeResult( $edRes );
	}

	/**
	 * Starts a search for a given search request.
	 * @param array $aMonitor Set of options.
	 * @return ViewBaseElement View for search results.
	 */
	public function search( &$aMonitor ) {
		try {
			$vItem = $this->oSearchIndex->search( $aMonitor );
		} catch ( BsException $e ) {
			if ( $e->getMessage() == 'redirect' ) throw $e;
			$vItem = new ViewBaseElement();
			$vItem->setTemplate( 'Error: {error}' );
			$vItem->addData( array( 'error' => $e->getMessage() ) );
		}

		return $vItem;
	}

	/**
	 * Sanitze search input to prevent XSS
	 * @param string $sSearchString Raw search string.
	 * @return string sanitized search string.
	 */
	public static function sanitzeSearchString( $sSearchString ) {
		$sSearchString = trim( $sSearchString );
		$sSearchString = htmlspecialchars( $sSearchString, ENT_QUOTES, 'UTF-8' );

		return $sSearchString;
	}

	/**
	 * Normalize search string in order to be processed by search service.
	 * @param string $sSearchString Raw search string.
	 * @return string Normalized search string.
	 */
	public static function preprocessSearchInput( $sSearchString ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Uppercase reserved words for the lovely lucene
		$sSearchString = mb_strtolower( $sSearchString );
		$sSearchString = str_ireplace(
			array( ' and ', ' or ', ' not ' ),
			array( ' AND ', ' OR ', ' NOT ' ),
			' '.$sSearchString.' '
		);

		if ( ( substr_count( $sSearchString, '"' ) % 2 ) != 0 ) {
			$sSearchString = str_replace( '"', '\\"', $sSearchString );
		}

		$sSearchString = str_replace( array( '_', '/' ), ' ', $sSearchString );

		$sSearchString = str_replace(
			array( ':', '{', '}', '(', ')', '[', ']', '+', '&', '.', '/' ),
			array( '\\:', '\\{', '\\}', '\\(', '\\)', '\\[', '\\]', '\\+', '\\&', '\\.', '\\/' ),
			$sSearchString
		);
		$sSearchString = str_replace(
			array( '\\\\{', '\\\\}' ),
			array( '\\{', '\\}' ),
			$sSearchString
		);
		$sSearchString = trim( $sSearchString );
		wfProfileOut( 'BS::'.__METHOD__ );

		return $sSearchString;
	}

	/**
	 * For a given SearchInput (by the user) the Existence of an article with exactly this title is evaluated.
	 * @param String $sParamSearchInput
	 * @return boolean
	 */
	public static function titleExists( $sParamSearchInput, &$aOptions ) {
		$sParamSearchInput = trim( $sParamSearchInput );
		if ( empty( $sParamSearchInput ) ) return false;

		/* Normalize $sParamSearchInput first:
		 * - get rid of leading or trailing whitespace
		 * - get rid of characters that are not permitted in the title (by mediawiki)
		 * - get rid of more than one space at a time
		 */
		$thisTitleMightExist = trim( str_replace( BsCore::getForbiddenCharsInArticleTitle(), ' ', $sParamSearchInput ) );
		do {
			$beforeStrReplace = $thisTitleMightExist;
			$thisTitleMightExist = str_replace( '  ', ' ', $thisTitleMightExist );
		}
		while ( $beforeStrReplace != $thisTitleMightExist );

		$oTitle = Title::newFromText( $thisTitleMightExist );
		if ( ( $oTitle !== null ) && $oTitle->exists() ) {
			$aOptions['existingTitleObject'] = $oTitle;
			return true;
		}
		// If first attempt to create a Title-Object without success...
		// ... remove leading and trailing '"'-characters (solves Ticket#2010062310000113)
		$oTitle = Title::newFromText( trim( $thisTitleMightExist, '"' ) );
		if ( ( $oTitle !== null ) && $oTitle->exists() ){
			$aOptions['existingTitleObject'] = $oTitle;
			return true;
		}

		return false;
	}

	/**
	 * Starts a search for Autocomplete
	 * @param String $sSearchString The string to be searched for.
	 * @return String JSON of search results.
	 */
	public static function getAutocompleteData( $sSearchString ) {
		if ( self::isCurlActivated() === false ) return '';

		$vNsSearch = false;
		$oDocuments = self::searchAutocomplete( $sSearchString, $vNsSearch );

		$aResults = array();
		$iID = 0;
		if ( !empty( $oDocuments ) ) {
			self::generateAutocompleteResults(
				$oDocuments, $sSearchString, $iID, $vNsSearch, $aResults
			);
		}

		$iSearchfiles = ( BsConfig::get( 'MW::ExtendedSearch::SearchFiles' ) ) ? '1' : '0' ;

		$sShortAndEscaped = self::sanitzeSearchString(
			BsStringHelper::shorten(
				$sSearchString,
				array(
					'max-length' => '60',
					'position' => 'middle',
					'ellipsis-characters' => '...'
				)
			)
		);

		$sLabel = wfMessage( 'bs-extendedsearch-searchfulltext' )->escaped() . '<br />';
		$sLabel .= '<b>' . $sShortAndEscaped . '</b>';

		$aOptions = array();
		$bTitleExists = self::titleExists( $sSearchString, $aOptions );
		$sEcpSearchString = self::sanitzeSearchString( $sSearchString );

		wfRunHooks( 'BSExtendedSearchAutocomplete', array( &$aResults, $sSearchString, &$iID, $bTitleExists, $sEcpSearchString ) );

		$aLinkParams = array(
			'search_scope' => 'text',
			'q' => $sEcpSearchString,
			'search_files' => $iSearchfiles
		);

		$oItem = new stdClass();
		$oItem->id = ++$iID;
		$oItem->value = $sEcpSearchString;
		$oItem->label = $sLabel;
		$oItem->type = '';
		$oItem->link = SpecialPage::getTitleFor( 'SpecialExtendedSearch' )->getFullUrl( $aLinkParams );
		$oItem->attr = 'bs-extendedsearch-ac-noresults';

		$aResults[] = $oItem;

		return FormatJson::encode( $aResults );
	}

	/**
	 * Starts a autocomplete search
	 * @param string $sSearchString Reference to given search string
	 * @param boolean $vNsSearch bool always false
	 * @return array Array of Apache_Solr_Documents
	 */
	private static function searchAutocomplete( &$sSearchString, &$vNsSearch ) {
		$oSerachService = SearchService::getInstance();
		$oSearchRequest = new SearchRequest();
		$oSearchRequest->init();
		$oSearchOptions = new SearchOptions( $oSearchRequest, RequestContext::getMain() );
		$oSearchOptions->readInSearchRequest();

		$sSearchString = urldecode( $sSearchString );
		$sSolrSearchString = self::preprocessSearchInput( $sSearchString );

		$aQuery = $oSearchOptions->getSolrAutocompleteQuery( $sSearchString, $sSolrSearchString );
		try {
			$oHits = $oSerachService->search(
				$aQuery['searchString'],
				$aQuery['offset'],
				$aQuery['searchLimit'],
				$aQuery['searchOptions']
			);
		} catch ( Exception $e ) {
			return '';
		}

		$oDocuments = $oHits->response->docs;

		if ( $aQuery['namespace'] !== false ) {
			$vNsSearch = $aQuery['namespace'];
		}

		$bEscalateToFuzzy = ( $oHits->response->numFound == 0 ); // boolean!
		// Escalate to fuzzy
		if ( $bEscalateToFuzzy ) {
			$oSearchOptions->setOption( 'scope', 'title' );

			$aFuzzyQuery = $oSearchOptions->getSolrFuzzyQuery( $sSolrSearchString );
			$aFuzzyQuery['searchLimit'] = BsConfig::get( 'MW::ExtendedSearch::AcEntries' );
			$aFuzzyQuery['searchOptions']['facet'] = 'off';
			$aFuzzyQuery['searchOptions']['hl'] = 'off';

			try {
				$oHits = $oSerachService->search(
					$aFuzzyQuery['searchString'],
					$aFuzzyQuery['offset'],
					$aFuzzyQuery['searchLimit'],
					$aFuzzyQuery['searchOptions']
				);
			} catch ( Exception $e ) {
				return '';
			}

			$oDocuments = $oHits->response->docs;
		}
		return $oDocuments;
	}

	/**
	 * Generates result entires for autocomplete
	 * @param array $aDocuments Array of Apache_solr_Documents
	 * @param string $sSearchString Given search string
	 * @param integer $iNum Number of Results
	 * @param mixed $vNsSearch false or integer namespace id
	 * @param array $aResults Reference to results array
	 */
	private static function generateAutocompleteResults( $aDocuments, $sSearchString, $iNum, $vNsSearch, &$aResults ) {
		$sLabelText = '';
		foreach ( $aDocuments as $oDoc ) {
			if ( $oDoc->namespace != '999' ) {
				$iNamespace = ( $oDoc->namespace == '1000' ) ? NS_SPECIAL : $oDoc->namespace;
				$oTitle = Title::makeTitle( $iNamespace, $oDoc->title );
			} else {
				continue;
			}

			if ( !$oTitle->userCan( 'read' ) ) continue;

			$sLabelText = self::highlightTitle( $oTitle, $sSearchString );

			// Adding namespace
			if ( $oTitle->getNamespace() !== NS_MAIN ) {
				$sLabelText = BsNamespaceHelper::getNamespaceName( $oTitle->getNamespace() ) . ':' .$sLabelText;
			}

			//If namespace is in searchstring remove it from display
			if ( $vNsSearch !== false ) {
				$sNamespace = BsNamespaceHelper::getNamespaceName( $vNsSearch );
				$sLabelText = str_replace( $sNamespace.':', '', $sLabelText );
			}

			$oItem = new stdClass();
			$oItem->id = ++$iNum;
			$oItem->value = $oTitle->getPrefixedText();
			$oItem->label = $sLabelText;
			$oItem->type = $oDoc->type;
			$oItem->link = $oTitle->getFullURL();
			$oItem->attr = '';

			$aResults[] = $oItem;
		}
	}

	/**
	 * Highlights title for a given search string
	 * @param object $oTitle Title which should be highlighted
	 * @param string $sSearchString search string
	 * @return string highlighted title
	 */
	private static function highlightTitle( $oTitle, $sSearchString ) {
		$sPartOfTitle = '';
		$sEscapedPattern = '';
		$aSearchStringParts = array();
		$sModifiedSearchString = str_replace( '/', ' ', $sSearchString );
		$sLabelText = BsStringHelper::shorten(
			$oTitle->getText(),
			array( 'max-length' => '54', 'position' => 'middle', 'ellipsis-characters' => '...' )
		);

		$iPosition = mb_stripos( $sLabelText, $sSearchString );

		if ( $iPosition !== false ) {
			$sPartOfTitle = mb_substr( $sLabelText, $iPosition, mb_strlen( $sSearchString ) );
			$sEscapedPattern = preg_quote( $sPartOfTitle, '#' );
			$sLabelText = preg_replace( '#'.$sEscapedPattern.'#i', '<b>'.$sPartOfTitle.'</b>', $sLabelText , 1 );
		} else {
			$aOccurrences = array();
			$aSearchStringParts = explode( ' ', $sModifiedSearchString );

			foreach ( $aSearchStringParts as $sPart ) {
				if ( empty( $sPart ) ) continue;

				$sModifiedPart = mb_strtolower( $sPart );
				if ( in_array( $sModifiedPart, $aOccurrences ) ) continue;

				$iPosition = mb_stripos( $sLabelText, $sPart );

				if ( $iPosition !== false ) {
					$sPartOfTitle = mb_substr( $sLabelText, $iPosition, mb_strlen( $sPart ) );
					$sEscapedPattern = preg_quote( $sPartOfTitle, '#' );
					$sLabelText = preg_replace( '#'.$sEscapedPattern.'#i', '['.$sPartOfTitle.']', $sLabelText, 1 );

					$aOccurrences[] = $sModifiedPart;
				}
			}

			$sLabelText = str_replace( array( '[', ']' ), array( '<b>', '</b>' ), $sLabelText );
		}

		return $sLabelText;
	}

	/**
	 * Creates MoreLinkThis View
	 * @param Title $oTitle Current title object
	 * @param string $sOrigin origin of request
	 * @return View $oViewMlt MoreLikeThis view
	 */
	public function getViewMoreLikeThis( $oTitle ) {
		$oViewMlt = new ViewMoreLikeThis;
		if ( $oTitle->isSpecialPage() ) return $oViewMlt;

		$oResults = $this->getMltData( $oTitle );

		$aMlt = array();
		//$aMlt[] = implode( ', ', $oResults->interestingTerms );
		if ( $oResults !== null && !empty( $oResults->response->docs ) ) {
			foreach ( $oResults->response->docs as $oRes ) {
				if ( count( $aMlt )  === 5 ) break;

				$oMltTitle = ( $oRes->namespace != 999 )
					? Title::makeTitle( $oRes->namespace, $oRes->title )
					: Title::makeTitle( NS_FILE, $oRes->title );

				if ( !$oMltTitle->userCan( 'read' )
					|| $oMltTitle->getArticleID() === $oTitle->getArticleID()
					|| $oMltTitle->isRedirect() ) {
					continue;
				}

				$sHtml = $oMltTitle->getPrefixedText();
				$aMlt[] = Linker::link( $oMltTitle, $sHtml );
			}
		}

		if ( empty( $aMlt ) ) {
			$aMlt[] = wfMessage( 'bs-extendedsearch-no-mlt-found' )->text();
		}
		$oViewMlt->setOption( 'mlt', $aMlt );

		return $oViewMlt;
	}

	/**
	 * Generates list of most searched terms
	 * @return string list of most searched terms
	 */
	public static function getRecentSearchTerms( $iCount, $iTime ) {
		$oDbr = wfGetDB( DB_SLAVE );
		$iCount = BsCore::sanitize( $iCount, 0, BsPARAMTYPE::INT );
		$iTime = BsCore::sanitize( $iTime, 0, BsPARAMTYPE::INT );

		$aConditions = array();
		if ( $iTime !== 0 ) {
			$iTimeInSec = $iTime * 24 * 60 * 60;
			$iTimeStamp = wfTimestamp( TS_UNIX ) - $iTimeInSec;
			$iTimeStamp = wfTimestamp( TS_MW, $iTimeStamp );
			$aConditions = array( 'stats_ts >= '.$iTimeStamp );
		}

		$res = $oDbr->select(
			'bs_searchstats',
			'stats_term',
			$aConditions
		);

		$aResults = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			$aTerms = array();

			foreach ( $res as $row ) {
				$sTerm = str_replace( array( '*', '\\' ), '', $row->stats_term );
				if ( substr_count( $sTerm, '~' ) > 0 ) {
					$aTermParts = explode( '~', $sTerm );
					$sFuzzy = array_pop( $aTermParts );
					$sTerm = implode( '', $aTermParts );
				}

				$sTerm = mb_strtolower( $sTerm );
				if ( array_key_exists( $sTerm, $aTerms ) ) {
					$aTerms[$sTerm] += 1;
				} else {
					$aTerms[$sTerm] = 1;
				}
			}

			arsort( $aTerms );
			$aResults[] = '<ol>';
			$i = 1;

			foreach ( $aTerms as $key => $value ) {
				if ( $i > $iCount ) break;
				$aResults[] = '<li>' . htmlspecialchars( $key, ENT_QUOTES, 'UTF-8' ) . ' (' . $value . ')</li>';
				$i++;
			}

			$aResults[] = '</ol>';
		}

		return implode( "\n", $aResults );
	}

	/**
	 * Get more like this response
	 * @param object $oTitle Title object
	 * @return object null|Apache_Solr_Response
	 */
	public function getMltData( $oTitle ) {
		$aMltQuery = $this->oSearchOptions->getSolrMltQuery( $oTitle );
		try {
			$oResponse = $this->oSearchService->mlt(
				$aMltQuery['searchString'],
				$aMltQuery['offset'],
				$aMltQuery['searchLimit'],
				$aMltQuery['searchOptions']
			);
		} catch ( Exception $e ) {
			return null;
		}

		return $oResponse;
	}

}