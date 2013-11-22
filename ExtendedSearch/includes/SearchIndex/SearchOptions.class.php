<?php
/**
 * Manages search options for ExtendedSearch for MediaWiki
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
 * FIRST CHANGES
 */
/**
 * User-Input is stored in a object of class SearchRequest
 * All relevant data during searchtime can be found here in object of class SearchOptions
 * The class itself can generate all input for different solr-queries to be passed to
 * Apache_Solr_Service
 * @author Mathias
 */
/**
 * Manages search options for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class SearchOptions {

	/**
	 * Stores the original search string as entered into the form.
	 * @var string The original search string
	 */
	public static $searchStringRaw = '';
	/**
	 * Unique ID in order to distinguish this wiki in the index from others.
	 * @var string A unique ID
	 */
	protected $sCustomerId = null;
	/**
	 * Internal datastore
	 * @var array List of options.
	 */
	protected $aOptions = array(); // internal datastore
	/**
	 * Data being passed as $params to instance of BsSearchService, member function searchs($query, $offset = 0, $limit = 10, $params = array())
	 * @var array List of options.
	 */
	protected $aSearchOptions = array(); // data being passed as $params to instance of BsSearchService, member function searchs($query, $offset = 0, $limit = 10, $params = array())
	/**
	 * List of Solr query options
	 * @var array List of options.
	 */
	protected $aSolrQuery = null;
	/**
	 * List of Solr fuzzy query options
	 * @var array List of options.
	 */
	protected $aSolrFuzzyQuery = null;
	/**
	 * Request Context
	 * @var RequestContext RequestContext object
	 */
	protected $oRequestContext;
	/**
	 * Instance of search service
	 * @var object of search service
	 */
	protected static $oInstance = null;

	/**
	 * Constructor  for SearchOptions class
	 * @param BsSearchService $searchService Reference to search service.
	 */
	public function __construct() {
		$this->readInSearchRequest();
	}

	/**
	 * Return a instance of SearchRequest.
	 * @return SearchRequest Instance of SearchRequest
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
	 * Fetch unique ID for this wiki instance.
	 * @return string Unique ID
	 */
	protected function getCustomerId() {
		if ( $this->sCustomerId !== null ) return $this->sCustomerId;
		$sCustomerId = BsConfig::get( 'MW::ExtendedSearch::CustomerID' );

		if ( empty( $sCustomerId ) || strpos( $sCustomerId, '?' ) !== false || strpos( $sCustomerId, '*' ) !== false ) {
			throw new BsException( 'CustomerId must not be empty (settings extended search)' );
		}

		wfRunHooks( 'BSExtendedSearchGetCustomerId', array( &$sCustomerId ) );

		$this->sCustomerId = $sCustomerId;
		return $this->sCustomerId;
	}

	/**
	 * Set a value for a certain option
	 * @param string $sKey Key of option
	 * @param mixed $vValue Value of option
	 */
	public function setOption( $sKey, $vValue ) {
		$this->aOptions[$sKey] = $vValue;
	}

	/**
	 * Get vaule for a certain option
	 * @param string $sKey
	 * @return mixed The value.
	 */
	public function getOption( $sKey ) {
		return $this->aOptions[$sKey];
	}

	/**
	 * Get complete options array
	 * @return array List of options 
	 */
	public function getOptionsArray(){
		return $this->aOptions;
	}

	/**
	 * Get bool value of an option
	 * @param string $sKey Key for option
	 * @return bool Bool value of the option
	 */
	public function getOptionBool( $sKey ) {
		if ( !isset( $this->aOptions[$sKey] ) ) return false;
		return (bool) $this->aOptions[$sKey];
	}

	/**
	 * Creates an array that can be used as a search query to Solr.
	 * @return array List of url parameters.
	 */
	public function getSolrQuery() {
		if ( $this->aSolrQuery !== null ) return $this->aSolrQuery;

		$this->aSolrQuery = array(
			'searchString'  => $this->aOptions['searchStringFinal'],
			'offset'        => $this->aOptions['offset'],
			'searchLimit'   => $this->aOptions['searchLimit'],
			'searchOptions' => $this->aSearchOptions
		);

		return $this->aSolrQuery;
	}

	/**
	 * Creates an array that can be used as a autocomplete search query to Solr.
	 * @return array List of url parameters.
	 */
	public function getSolrAutocompleteQuery() {
		$aSearchOptions = array();
		$aSearchOptions['fl'] = 'type,title,namespace';
		$aSearchOptions['fq'] = $this->aSearchOptions['fq'];
		$aSearchOptions['sort'] = $this->aSearchOptions['sort'];

		$aSolrAutocompleteQuery = array(
			'searchString'  => $this->aOptions['searchStringFinal'],
			'offset'        => $this->aOptions['offset'],
			'searchLimit'   => $this->aOptions['searchLimit'],
			'searchOptions' => $aSearchOptions
		);

		return $aSolrAutocompleteQuery;
	}

	/**
	 * Creates an array that can be used as a search fuzzy query to Solr.
	 * @return array List of url parameters.
	 */
	public function getSolrFuzzyQuery( $sSearchString = '' ) {
		if ( $this->aSolrFuzzyQuery !== null ) return $this->aSolrFuzzyQuery;

		// Prefix, wildcard and fuzzy queries aren't analyzed, and thus won't match synonyms
		if ( empty( $sSearchString ) ) {
			$fuzzySearchString = $this->aOptions['searchStringOrig'];
		} else { 
			$fuzzySearchString = $sSearchString;
		}
		$lastSignOfStringIsTilde = ( strrpos( $fuzzySearchString , '~' ) === ( strlen( $this->aOptions['searchStringOrig'] ) -1 ) );
		$fuzzySearchString .= ( ( $lastSignOfStringIsTilde ) ? '' : '~' );
		$fuzzySearchString .= BsConfig::get( 'MW::ExtendedSearch::DefFuzziness' );
		$this->aOptions['searchStringForStatistics'] = $fuzzySearchString;

		$aFuzzySearch = array();
		$aFuzzySearch['searchStringInTitle'] = '(titleWord:(' . $fuzzySearchString . ') OR redirects:(' . $fuzzySearchString . ') OR sections:("' . $fuzzySearchString . '")';
		$aFuzzySearch['searchStringInFulltext'] = 'textWord:(' . $fuzzySearchString . ')';

		$this->aOptions['searchStringFuzzy'] = ( $this->aOptions['scope'] == 'title' )
			? $aFuzzySearch['searchStringInTitle']
			: implode( ' OR ', $aFuzzySearch );

		$this->aOptions['searchStringFuzzy'] .= ') AND redirect:0';

		$this->aSolrFuzzyQuery = array(
			'searchString'  => $this->aOptions['searchStringFuzzy'],
			'offset'        => $this->aOptions['offset'],
			'searchLimit'   => $this->aOptions['searchLimit'],
			'searchOptions' => $this->aSearchOptions
		);

		return $this->aSolrFuzzyQuery;
	}

	/**
	 * Creates an array that can be used as a autocomplete search query to Solr.
	 * @return array List of url parameters.
	 */
	public function getSolrMltQuery( $oTitle ) {
		$aSearchOptions = array();
		$aNamespaces = array();

		$sFqNamespaces = '{!tag=na}+namespace:( ';
		$aContentNamespaces = BsConfig::get( 'MW::ExtendedSearch::MltNS' );
		foreach ( $aContentNamespaces as $key => $value ) {
			$aNamespaces[] = '' . $value . '';
		}

		$sFqNamespaces = $sFqNamespaces . implode( ' ', $aNamespaces ) . ' )';

		$this->aOptions['mlt_title'] = '';
		$this->aOptions['mlt_link']  = '';
		$this->aOptions['mlt_id']    = '';

		$aSearchOptions['fl'] = 'title,namespace';
		$aSearchOptions['fq'][] = '+wiki:(' . $this->getCustomerId() . ')';
		$aSearchOptions['fq'][] = $sFqNamespaces;

		$aSearchOptions['mlt']       = 'true';
		$aSearchOptions['mlt.fl']    = 'titleMlt,textMlt'; // todo: titleWord, textWord
		$aSearchOptions['mlt.boost'] = 'true';
		$aSearchOptions['mlt.qf']    = 'titleMlt^10.0 textMlt^0.1';
		$aSearchOptions['mlt.mindf'] = '5';
		$aSearchOptions['mlt.mintf'] = '3';
		$aSearchOptions['mlt.maxqt'] = '15';
		$aSearchOptions['mlt.count'] = '10';

		//$aSearchOptions['mlt.interestingTerms'] = 'list';
			//http://localhost:8080/solr/mlt?q=hwid:2084&start=0&rows=10&fl=title,score&mlt.fl=title,text&mlt.boost=true&mlt.qf=title%5E10.0&mlt.mindf=1&mlt.mintf=1&mlt.interestingTerms=details&mlt.maxqt=10&mlt.minwl=5
			//mlt.maxqt=10&mlt.minwl=5
		$aSolrMltQuery = array(
			'searchString'  => 'hwid:' . $oTitle->getArticleID() . '',
			'offset'        => 0,
			'searchLimit'   => 6,
			'searchOptions' => $aSearchOptions
		);

		return $aSolrMltQuery;
	}

	/**
	 * Creates an array that can be used as a similar search query to Solr.
	 * @return array List of url parameters.
	 */
	public function getSearchOptionsSim() {
		$sCustomerId = $this->getCustomerId();
		return array(
				'fl' => 'title,namespace,score',
				'fq' => array( '+overall_type:wiki', "+wiki:$sCustomerId" ),
				'spellcheck' => 'true',
				'spellcheck.collate' => 'true'
			);
	}

	/**
	 * Retrieves parameters from defaults and search options.
	 */
	protected function readInSearchRequest() {
		$this->oRequestContext = RequestContext::getMain();
		$oSearchRequest = SearchRequest::getInstance();

		$this->aOptions['searchStringRaw']  = $oSearchRequest->sInput;
		$this->aOptions['searchStringOrig'] = SearchService::preprocessSearchInput( $oSearchRequest->sInput );

		self::$searchStringRaw = $this->aOptions['searchStringRaw'];

		$vNamespace = ExtendedSearchBase::getInstance()->checkSearchstringForNamespace( $this->aOptions['searchStringRaw'], $this->aOptions['searchStringOrig'] );

		$this->aOptions['searchStringWildcarded']    = SearchService::wildcardSearchstring( $this->aOptions['searchStringOrig'] );
		$this->aOptions['searchStringForStatistics'] = $this->aOptions['searchStringWildcarded'];
		$this->aOptions['searchStrComp']             = array();
		$this->aOptions['bExtendedForm']             = $oSearchRequest->bExtendedForm;

		$scope = BsConfig::get( 'MW::ExtendedSearch::DefScopeUser' ) == 'title' ? 'title' : 'text';

		if ( $oSearchRequest->sScope == 'title' ) $scope = 'title';
		if ( $oSearchRequest->sScope == 'text' ) $scope = 'text';
		$this->aOptions['scope'] = $scope;

		// titleWord, titleReverse, textWord, textReverse sind "analyzed" (u.a. gestemmt)
		// Wird ein Query mit Wilcards ausgeführt, wird darauf vom Solr kein Analyzer angewendet => der Treffer liegt dann also wahrscheinlicher im Feld *Reverse

		global $wgContLang;
		$sLanguage = $wgContLang->getCode();
		if ( $sLanguage === 'de' || $sLanguage === 'de-formal' ) {
			$sLang = 'de';
		} else {
			$sLang = 'en';
		}

		// require stopword lists for sentence recognation
		require( 'Stopwords_'.$sLang.'.php' );

		$aSerachTerms = explode( ' ', $this->aOptions['searchStringRaw'] );
		$sChar = '';

		// If entered search term has more than 4 words it will be checked for real sentence
		if ( count( $aSerachTerms ) > 3 ) {
			$iOccurences = 0;
			foreach ( $aSerachTerms as $sTerm ) {
				$sTerm = mb_strtolower( $sTerm );

				if ( in_array( $sTerm, $aStopwords ) ) {
					$iOccurences++;
				}
			}

			// More than two words of the search term are on the stopword list
			// it is handeled as as a sentence ans will be a phrasequery
			if ( $iOccurences >= 2 ) {
				$sChar = '"';
			}
		}

		// Not nice but if titleWord occurs several times it is scored much higher
		$sSearchStringTitle   = '(titleWord:("' . $this->aOptions['searchStringOrig'] . '")^50'.
								' OR titleWord:("' . $this->aOptions['searchStringOrig'] . '")^50'.
								' OR titleWord:(' . $sChar . $this->aOptions['searchStringOrig'] . $sChar . ')^20'.
								' OR titleReverse:(' . $this->aOptions['searchStringWildcarded'] . ')'.
								' OR redirects:(' . $sChar . $this->aOptions['searchStringOrig'] . $sChar . ')';

		$sSearchStringText    = 'textWord:(' . $sChar . $this->aOptions['searchStringOrig'] . $sChar .')^0.1'.
								' OR textWord:(' . $sChar . $this->aOptions['searchStringOrig'] . $sChar . ')^0.1'.
								' OR textReverse:(' . $this->aOptions['searchStringWildcarded'] . ')'.
								' OR sections:('. $sChar . $this->aOptions['searchStringOrig'] . $sChar . ')';

		$this->aOptions['searchStrComp']['searchStrInTitle'] = $sSearchStringTitle;
		$this->aOptions['searchStrComp']['searchStrInFulltext'] = $sSearchStringText;

		$this->aOptions['searchStringFinal'] = ( $this->aOptions['scope'] === 'title' )
			? $this->aOptions['searchStrComp']['searchStrInTitle']
			: implode( ' OR ', $this->aOptions['searchStrComp'] );

		// Do not show redircets and specialpages
		$this->aOptions['searchStringFinal'] .= ') AND redirect:0  AND special:0';

		$sCustomerId = $this->getCustomerId();

		$fq = array();
		$fq[] = '+wiki:('.$sCustomerId.')';

		// $this->aOptions['namespaces'] HAS TO BE an array with numeric indices of type string!
		$this->aOptions['namespaces'] = $oSearchRequest->aNamespaces;
		$this->aOptions['sayt'] = $oSearchRequest->sSearchAsYouType;

		global $wgCanonicalNamespaceNames, $wgExtraNamespaces;
		$aNamespaces = array_slice( $wgCanonicalNamespaceNames, 2 );
		$aNamespaces = $aNamespaces + $wgExtraNamespaces;
		$oUser = $this->oRequestContext->getUser();

		if ( empty( $this->aOptions['namespaces'] ) && $vNamespace === false ) {
			$this->aOptions['namespaces'] = array();

			// Main
			if ( BsNamespaceHelper::checkNamespacePermission( NS_MAIN, 'read' ) !== false ) {
				$this->aOptions['namespaces'][] = '' . NS_MAIN;
			}

			// Namespace 1000 are specialpages
			$this->aOptions['namespaces'][] = '1000';

			if ( in_array( $oSearchRequest->sRequestOrigin, array( 'search_form_body', 'uri_builder', 'ajax' ) )
				|| $oUser->getOption( 'searcheverything' ) == 1 ) {

				foreach ( $aNamespaces as $key => $value ) {
					if ( BsNamespaceHelper::checkNamespacePermission( $key, 'read' ) === false ) continue;

					$this->aOptions['namespaces'][] = '' . $key;
				}
			} else {
				wfRunHooks( 'BSExtendedSearchEmptyNamespacesElse', array( $this, $oSearchRequest ) );
				global $wgNamespacesToBeSearchedDefault;

				foreach ( $aNamespaces as $key => $value ) {
					if ( $oUser->getBoolOption( 'searchNs'.$key, false )
						|| isset( $wgNamespacesToBeSearchedDefault[$key] ) ) {
						if ( BsNamespaceHelper::checkNamespacePermission( $key, 'read' ) === false ) continue;
						$this->aOptions['namespaces'][] = '' . $key;
					}
				}
			}

			if ( ( $oSearchRequest->bSearchFiles === true && $oUser->isAllowed( 'searchfiles' ) )
				|| $this->aOptions['sayt'] == 1 ) {
				$this->aOptions['namespaces'][] = '998 999';
			}

			$this->aOptions['namespaces'] = array_unique( $this->aOptions['namespaces'] );
		}

		/*
		 * If checkbox "search_files" is unchecked in the search form, this value is NOT PASSED AT ALL!
		 * In that case it is not possible to set $this->sFiles = $this->sDefaultSearchFiles, because the User probably wanted files not to be searched AND $this->sDefaultSearchFiles could be set to 'files'$
		 * Thus a hidden field was added to the search form with name="search_origin" and value="search_form_body"
		 * If that field can be read with the POST-data, the DefaultSearchFiles has to be omitted
		 */
		if ( $oSearchRequest->bSearchFiles === true ) {
			$this->aOptions['files'] = true;
		} else {
			$this->aOptions['files'] = false;
		}

		if ( $vNamespace === false && ( !empty( $this->aOptions['namespaces'] ) || $this->aOptions['files'] === true ) ) {
			// todo (according to Markus): files should not be coded as namespace
			// => i.e. modify solr's schema
			$aFqNamespaces = array();

			if ( empty( $this->aOptions['namespaces'] ) && $oSearchRequest->sRequestOrigin != 'uri_builder' ) {
				// if NO namespace selected search ALL namespaces
				// namespace 0 not in keys of wgCanonicalNamespaceNames
				// todo: just wondering: if NO namespace selected just SKIP +namespace(..)! Or is there a problem with namespace 999?
				foreach ( $aNamespaces as $sNamespace => $value ) {
					if ( BsNamespaceHelper::checkNamespacePermission( $na, 'read' ) === false ) continue;
					$aFqNamespaces[] = $sNamespace;
				}
			} else {
				foreach ( $this->aOptions['namespaces'] as $sNamespace ) {
					$aFqNamespaces[] = $sNamespace;
					if ( $sNamespace == '999' ) $filesAlreadyAddedInLoopBefore = true;
				}
			}

			if ( !isset( $filesAlreadyAddedInLoopBefore ) && $this->aOptions['files'] === true && $oUser->isAllowed( 'searchfiles' ) ) {
				$aFqNamespaces[] = '999';
			}

			$fq[] = '{!tag=na}+namespace:(' . implode( ' ', $aFqNamespaces ) . ')';
		}

		if ( empty( $oSearchRequest->aNamespaces ) && $oSearchRequest->sOrigin != 'titlebar' ) {
			$this->aOptions['namespaces'] = array();
		}

		if ( $vNamespace !== false ) {
			$fq[] = '{!tag=na}+namespace:(' . $vNamespace . ')';
			$this->aOptions['namespaces'][] = '' . $vNamespace . '';
		}
		// $this->aOptions['cats'] = $oSearchRequest->sCat; // string, defaults to '' if 'search_cat' not set in REQUEST
		$this->aOptions['cats'] = $oSearchRequest->sCategories; // array of strings or empty array

		if ( !empty( $this->aOptions['cats'] ) ) {
			if ( isset( $oSearchRequest->sOperator ) ) {
				switch ( $oSearchRequest->sOperator ) {
					case 'AND': 
						$sOperator = ' AND ';
						break;
					case 'OR':
						$sOperator = ' OR ';
						break;
					default:
						$sOperator = ' OR ';
				}
			} else {
				$sOperator = ' OR ';
			}
			$sFqCategories = '{!tag=ca}+cat:(';
			$sFqCategories .= implode( $sOperator, $this->aOptions['cats'] );
			$sFqCategories .= ')';
			$fq[] = $sFqCategories;
		}

		$this->aOptions['type'] = $oSearchRequest->sType;

		if ( !empty( $this->aOptions['type'] ) ) {
			$sFqType = '{!tag=ty}+type:(';
			$sFqType .= implode( ' ', $this->aOptions['type'] );
			$sFqType .= ')';
			$fq[] = $sFqType;
		}

		$this->aOptions['editor'] = $oSearchRequest->sEditor;

		if ( !empty( $this->aOptions['editor'] ) ) {
			// there may be spaces in name of editor. solr analyses those to two
			// terms (editor's names) thus we wrap the name into quotation marks
			// todo: better: in schema.xml define field editor not to be tokenized
			//       at whitespace
			// but: +editor:("Robert V" "Mathias S") is already split correctly!
			$sFqEditor = '{!tag=ed}+editor:("';
			$sFqEditor .= implode( '" "', $this->aOptions['editor'] );
			$sFqEditor .= '")';
			$fq[] = $sFqEditor;
		}

		$searchLimit = BsConfig::get( 'MW::ExtendedSearch::LimitResults' );

		$this->aOptions['offset']       = $oSearchRequest->iOffset;
		$this->aOptions['order']        = $oSearchRequest->sOrder;
		$this->aOptions['asc']          = $oSearchRequest->sAsc;
		$this->aOptions['searchLimit']  = ( $searchLimit == 0 ) ? 25 : $searchLimit;
		$this->aOptions['titleExists']  = $this->titleExists( $oSearchRequest->sInput );
		$this->aOptions['format']       = $oSearchRequest->sFormat;

		$this->aSearchOptions['fl']          = 'wiki,uid,hwid,type,title,overall_type,path,namespace,cat,score,ts,redirect,redirects';
		$this->aSearchOptions['fq']          = $fq;
		$this->aSearchOptions['sort']        = $this->aOptions['order'] . ' ' . $this->aOptions['asc'];
		$this->aSearchOptions['hl']          = 'on';
		$this->aSearchOptions['hl.fl']       = 'titleWord, titleReverse, sections, textWord, textReverse';
		$this->aSearchOptions['hl.snippets'] = BsConfig::get( 'MW::ExtendedSearch::HighlightSnippets' );

		if ( BsConfig::get( 'MW::ExtendedSearch::ShowFacets' ) ) {
			$this->aSearchOptions['facet']          = 'on';
			$this->aSearchOptions['facet.sort']     = 'false';
			$this->aSearchOptions['facet.field']    = array( '{!ex=na}namespace', '{!ex=ca}cat', '{!ex=ov}overall_type', '{!ex=ty}type', '{!ex=ed}editor' );
			$this->aSearchOptions['facet.mincount'] = '1';
			$this->aSearchOptions['facet.missing']  = 'true';
		}
	}

	/**
	 * For a given SearchInput (by the user) the Existence of an article with exactly this title is evaluated.
	 * @param String $sParamSearchInput
	 * @return boolean
	 */
	public function titleExists( $sParamSearchInput ) {
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
			$this->aOptions['existingTitleObject'] = &$oTitle;
			return true;
		}
		// If first attempt to create a Title-Object without success...
		// ... remove leading and trailing '"'-characters (solves Ticket#2010062310000113)
		$oTitle = Title::newFromText( trim( $thisTitleMightExist, '"' ) );
		if ( ( $oTitle !== null ) && $oTitle->exists() ){
			$this->aOptions['existingTitleObject'] = &$oTitle;
			return true;
		}

		return false;
	}

}