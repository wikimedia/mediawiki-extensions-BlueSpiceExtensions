<?php
/**
 * Manages search options for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @author     Mathias Scheer <scheer@hallowelt.com>
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/**
 * The search request is stored in a object of class SearchRequest
 * All relevant data during searchtime can be found here in object of class SearchOptions
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
	protected $aSearchOptions = array();
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
	protected $oRequestContext = null;

	/**
	 * Instance of search service
	 * @var object of search service
	 */
	protected static $oInstance = null;

	/**
	 *
	 * @var SearchRequest
	 */
	protected $oSearchRequest = null;

	/**
	 * Buffer used to assemble the 'facet.field' search option
	 * @var array
	 */
	protected $aFacetFields = array();

	/**
	 * This is just in case somebody
	 * set 0 to "MW::ExtendedSearch::LimitResults"
	 * @var int
	 */
	protected $iSearchLimitFailSettingDefault = 25;

	/**
	 * Constructor  for SearchOptions class
	 * @param SearchRequest $oSearchRequest search request
	 */
	public function __construct( $oSearchRequest, $oContext ) {
		$this->oSearchRequest = $oSearchRequest;
		$this->oRequestContext = $oContext;
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

		Hooks::run( 'BSExtendedSearchGetCustomerId', array( &$sCustomerId ) );

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
		if ( $this->aSolrQuery !== null ) {
			return $this->aSolrQuery;
		}

		$this->aSolrQuery = array(
			'searchString' => $this->aOptions['searchStringFinal'],
			'offset' => $this->aOptions['offset'],
			'searchLimit' => $this->aOptions['searchLimit'],
			'searchOptions' => $this->aSearchOptions
		);

		return $this->aSolrQuery;
	}

	/**
	 * Creates an array that can be used as a autocomplete search query to Solr.
	 * @return array List of url parameters.
	 */
	public function getSolrAutocompleteQuery( $sSearchString, $sSolrSearchString ) {
		$aSearchOptions = array();
		$aSearchOptions['fl'] = 'type,title,namespace';
		$aSearchOptionsFq = array();
		$vNamespace = $this->makeAutoCompleteFilterQuery( $sSearchString, $sSolrSearchString, $aSearchOptionsFq );
		$aSearchOptions['fq'] = $aSearchOptionsFq;
		$aSearchOptions['sort'] = $this->aSearchOptions['sort'];

		$aOptions = array();
		$sWildcardedSearchString = SearchService::wildcardSearchstring( $sSolrSearchString );
		$aOptions['searchString'] = 'titleEdge:('.$sWildcardedSearchString.' OR '.$sSolrSearchString.')'
					. ' OR title:('.$sWildcardedSearchString.' OR '.$sSolrSearchString.')';
		$aOptions['searchLimit'] = BsConfig::get( 'MW::ExtendedSearch::AcEntries' );

		$aQuery = array(
			'searchString' => $aOptions['searchString'],
			'offset' => $this->aOptions['offset'],
			'searchLimit' => $aOptions['searchLimit'],
			'searchOptions' => $aSearchOptions,
			'namespace' => $vNamespace
		);

		return $aQuery;
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
		$fuzzySearchString .= ( $lastSignOfStringIsTilde ) ? '' : '~';
		$fuzzySearchString .= BsConfig::get( 'MW::ExtendedSearch::DefFuzziness' );
		$this->aOptions['searchStringForStatistics'] = $fuzzySearchString;

		$aFuzzySearch = array();
		$aFuzzySearch['searchStringInTitle'] = '(titleWord:(' . $fuzzySearchString . ') OR redirects:(' . $fuzzySearchString . ') OR sections:("' . $fuzzySearchString . '")';
		$aFuzzySearch['searchStringInFulltext'] = 'textWord:(' . $fuzzySearchString . ')';

		$this->aOptions['searchStringFuzzy'] = ( $this->aOptions['scope'] == 'title' )
			? $aFuzzySearch['searchStringInTitle']
			: implode( ' OR ', $aFuzzySearch );

		$this->aOptions['searchStringFuzzy'] .= ')';

		$this->aSolrFuzzyQuery = array(
			'searchString' => $this->aOptions['searchStringFuzzy'],
			'offset' => $this->aOptions['offset'],
			'searchLimit' => $this->aOptions['searchLimit'],
			'searchOptions' => $this->aSearchOptions
		);

		return $this->aSolrFuzzyQuery;
	}

	/**
	 * Creates an array that can be used as a more like this search query to Solr.
	 * @return array List of url parameters.
	 */
	public function getSolrMltQuery( $oTitle ) {
		$aSearchOptions = array();

		$aNamespaces = array_values( BsConfig::get( 'MW::ExtendedSearch::MltNS' ) );
		$sFqNamespaces = 'namespace:(' . implode( ' ', $aNamespaces ) . ')';

		$aSearchOptions['fl'] = 'title,namespace';
		$aSearchOptions['fq'][] = 'wiki:(' . $this->getCustomerId() . ')';
		$aSearchOptions['fq'][] = $sFqNamespaces;

		$aSearchOptions['mlt'] = 'true';
		$aSearchOptions['mlt.fl'] = 'textMlt'; // todo: titleWord, textWord
		$aSearchOptions['mlt.boost'] = 'true';
		$aSearchOptions['mlt.mindf'] = '5';
		$aSearchOptions['mlt.mintf'] = '3';
		$aSearchOptions['mlt.maxqt'] = '15';
		$aSearchOptions['mlt.count'] = '10';

		//$aSearchOptions['mlt.interestingTerms'] = 'list';
		//http://localhost:8080/solr/mlt?q=hwid:2084&start=0&rows=10&fl=title,score&mlt.fl=title,text&mlt.boost=true&mlt.qf=title%5E10.0
		//&mlt.mindf=1&mlt.mintf=1&mlt.interestingTerms=details&mlt.maxqt=10&mlt.minwl=5mlt.maxqt=10&mlt.minwl=5
		$aSolrMltQuery = array(
			'searchString' => 'hwid:' . $oTitle->getArticleID(),
			'offset' => 0,
			'searchLimit' => 10,
			'searchOptions' => $aSearchOptions
		);

		return $aSolrMltQuery;
	}

	/**
	 * Creates an array that can be used as a similar search query to Solr.
	 * @return array List of url parameters.
	 */
	public function getSearchOptionsSim() {
		return array(
			'fl' => 'title,namespace,score',
			'fq' => array( 'overall_type:wiki', "wiki:".$this->getCustomerId() ),
			'spellcheck' => 'true',
			'spellcheck.collate' => 'true'
		);
	}


	/**
	 * The buffer for filter query assembly
	 * @var array
	 */
	protected $aFq = array();
	/**
	 * Processes incoming search request
	 */
	public function readInSearchRequest() {
		$this->aFq[] = 'redirect:0';
		$this->aFq[] = 'special:0';
		$this->aFq[] = 'wiki:(' . $this->getCustomerId() . ')';

		$this->readInBasicOptions();
		$this->readInSearchTerm();

		$this->readInNamespaces();
		$this->readInCategories();
		$this->readInTypes();
		$this->readInEditors();

		$this->assembleSearchOptions();
	}

	/**
	 * Reads in searchstring and checks if a namespace is in it
	 * @param string $sSearchString given searchstring
	 * @param string $sSolrSearchString the solr searchstring
	 * @param array $aQueryFq solr filter query
	 * @param boolean $bWithTag flag for tagging
	 * @return int|boolean id of namespace or false
	 */
	public function checkSearchstringForNamespace( $sSearchString, &$sSolrSearchString, &$aQueryFq, $bWithTag = false ) {
		if ( empty( $sSearchString ) ) {
			return false;
		}

		if ( substr_count( $sSearchString, ':' ) === 0 ) {
			return false;
		}

		$aParts = explode( ':', $sSearchString );
		if ( count( $aParts ) !== 2 ) {
			return false;
		}

		if ( empty( $aParts[0] ) || empty( $aParts[1] ) ) {
			return false;
		}

		$iNamespace = BsNamespaceHelper::getNamespaceIndex( $aParts[0] );
		if ( empty( $iNamespace ) || !is_int( $iNamespace ) ) {
			return false;
		}

		// Check for special namespace
		if ( $iNamespace === NS_SPECIAL ) {
			$iNamespace = 1000;
		}

		$sSolrSearchString = $aParts[1];

		$aQueryFq[] = ( $bWithTag )
			? '{!tag=na}namespace:(' . $iNamespace . ')'
			: 'namespace:(' . $iNamespace . ')';

		return $iNamespace;
	}

	protected function readInNamespaces() {
		global $wgCanonicalNamespaceNames, $wgExtraNamespaces;

		$vNamespace = $this->checkSearchstringForNamespace(
			$this->aOptions['searchStringRaw'],
			$this->aOptions['searchStringOrig'],
			$this->aFq,
			BsConfig::get( 'MW::ExtendedSearch::ShowFacets' )
		);

		$this->aOptions['namespaces'] = $this->oSearchRequest->aNamespaces; //HAS TO BE an array with numeric indices of type string!

		$aNamespaces = array_slice( $wgCanonicalNamespaceNames, 2 );
		$aNamespaces = $aNamespaces + $wgExtraNamespaces;

		if ( $vNamespace === false ) {
			$this->aOptions['files'] = ( $this->oSearchRequest->bSearchFiles === true )
				? true
				: false;

			$oUser = RequestContext::getMain()->getUser();
			if ( !$oUser->getOption( 'searcheverything' ) ) {
				if ( empty( $this->aOptions['namespaces'] ) && $this->oSearchRequest->bNoSelect === false ) {
					$this->aOptions['namespaces'] = array();

					$aOptions = $oUser->getOptions();
					foreach ( $aOptions as $sOpt => $sValue ) {
						if ( strpos( $sOpt, 'searchNs' ) !== false && $sValue == true ) {
							$this->aOptions['namespaces'][] = '' . str_replace( 'searchNs', '', $sOpt );
						}
					}

					$aAllowedTypes = explode( ',' , BsConfig::get( 'MW::ExtendedSearch::IndexFileTypes' ) );
					$aAllowedTypes = array_map( 'trim', $aAllowedTypes );
					$aSearchFilesFacet = array_intersect( $this->oSearchRequest->aType, $aAllowedTypes );

					if ( ( $this->aOptions['files'] === true || !empty( $aSearchFilesFacet ) )
						&& $oUser->isAllowed( 'searchfiles' ) ) {
						$this->aOptions['namespaces'][] = '999';
						$this->aOptions['namespaces'][] = '998';
					}
				} else {
					$aTmp = array();
					foreach ( $this->aOptions['namespaces'] as $iNs ) {
						if ( BsNamespaceHelper::checkNamespacePermission( $iNs, 'read' ) === true ) {
							$aTmp[] = $iNs;
						}
					}
					$this->aOptions['namespaces'] = $aTmp;
				}
			} else {
				if ( empty( $this->aOptions['namespaces'] ) ) {
					$aTmp = array();
					foreach ( $aNamespaces as $iNs ) {
						if ( BsNamespaceHelper::checkNamespacePermission( $iNs, 'read' ) === true ) {
							$this->aOptions['namespaces'][] = $iNs;
						}
					}
				} else {
					$aTmp = array();
					foreach ( $this->aOptions['namespaces'] as $iNs ) {
						if ( !BsNamespaceHelper::checkNamespacePermission( $iNs, 'read' ) ) {
							$aTmp[] = $iNs;
						}
					}

					if ( !empty( $aTmp ) ) {
						$this->aOptions['namespaces'] = array_diff( $this->aOptions['namespaces'], $aTmp );
					}
				}
			}
		} else {
			$this->aOptions['namespaces'][] = '' . $vNamespace;
		}

		$this->aOptions['namespaces'] = array_unique( $this->aOptions['namespaces'] );

		if ( !empty( $this->aOptions['namespaces'] ) ) {
			$aFqNamespaces = array();
			foreach ( $this->aOptions['namespaces'] as $sNamespace ) {
				$aFqNamespaces[] = $sNamespace;
				if ( $sNamespace == '999' ) {
					$filesAlreadyAddedInLoopBefore = true;
				}
			}

			if ( !isset( $filesAlreadyAddedInLoopBefore ) && $this->aOptions['files'] === true
				&& $oUser->isAllowed( 'searchfiles' ) ) {
				$aFqNamespaces[] = '999';
			}

			$this->appendFilterQueryAndFacetFields( $aFqNamespaces, 'namespace', 'na' );
		}
		else {
			/*
			 * This is to make sure that the 'namespace' facet is displayed
			 * even if no namespace is selected.
			 * TODO: '$this->aFacetFields' should not be appended outside of
			 * 'appendFilterQueryAndFacetFields'. Unfortunately this is a
			 * special case in the logic that can not be handled any better
			 * without doing a major refactoring of this and several other
			 * classes.
			 */
			$this->aFacetFields[] = 'namespace';
		}
	}

	protected function readInCategories() {
		$this->aOptions['cats'] = $this->oSearchRequest->sCategories; // array of strings or empty array
		$this->appendFilterQueryAndFacetFields( $this->aOptions['cats'], 'cat', 'ca' );
	}

	protected function readInTypes() {
		$this->aOptions['type'] = $this->oSearchRequest->aType;
		$this->appendFilterQueryAndFacetFields( $this->aOptions['type'], 'type', 'ty' );
	}

	protected function readInEditors() {
		$this->aOptions['editor'] = $this->oSearchRequest->sEditor;
		$this->appendFilterQueryAndFacetFields( $this->aOptions['editor'], 'editor', 'ed' );
	}

	protected function makeBoostQuery() {
		global $bsgExtendedSearchBoostQuerySettings;

		$aBq = array();
		foreach( $bsgExtendedSearchBoostQuerySettings as $sFieldName => $aValueConfs ) {
			foreach( $aValueConfs as $mValue => $iBoostFactor ) {
				$aBq[] = $sFieldName . ':' . $mValue . '^' . $iBoostFactor;
			}
		}

		return $aBq;
	}

	protected function assembleSearchOptions() {
		Hooks::run(
			'BSExtendedSearchSearchOptionsAssembleSearchOptions',
			[ $this, &$this->aOptions, &$this->aFq, &$this->aFacetFields ]
		);
		$this->aSearchOptions['defType'] = 'edismax';
		$this->aSearchOptions['fl'] = 'uid,type,title,path,namespace,cat,ts,redirects,overall_type';
		$this->aSearchOptions['fq'] = $this->aFq;
		$this->aSearchOptions['sort'] = $this->aOptions['order'] . ' ' . $this->aOptions['asc'];
		$this->aSearchOptions['hl'] = 'on';
		$this->aSearchOptions['hl.fl'] = 'titleWord, titleReverse, sections, textWord, textReverse';
		$this->aSearchOptions['hl.snippets'] = BsConfig::get( 'MW::ExtendedSearch::HighlightSnippets' );
		$this->aSearchOptions['bq'] = implode( ' ', $this->makeBoostQuery() );

		if ( BsConfig::get( 'MW::ExtendedSearch::ShowFacets' ) ) {
			$this->aSearchOptions['facet'] = 'on';
			$this->aSearchOptions['facet.sort'] = 'false';
			$this->aSearchOptions['facet.mincount'] = '1';
			$this->aSearchOptions['facet.missing'] = 'true';
			$this->aSearchOptions['facet.field'] = array();

			foreach( $this->aFacetFields as $sFacetField ) {
				$this->aSearchOptions['facet.field'][] = $sFacetField;
			}
		}
	}

	protected function readInBasicOptions() {
		$this->aOptions['searchStringRaw'] = self::$searchStringRaw = $this->oSearchRequest->sInput;
		$this->aOptions['searchStringOrig'] = ExtendedSearchBase::preprocessSearchInput( $this->oSearchRequest->sInput );
		$this->aOptions['fset'] = $this->oSearchRequest->aFacetSettings;

		$searchLimit = BsConfig::get( 'MW::ExtendedSearch::LimitResults' );
		$this->aOptions['offset'] = $this->oSearchRequest->iOffset;
		$this->aOptions['order'] = $this->oSearchRequest->sOrder;
		$this->aOptions['asc'] = $this->oSearchRequest->sAsc;
		$this->aOptions['searchLimit'] = ( $searchLimit == 0 ) ? $this->iSearchLimitFailSettingDefault : $searchLimit;
		$this->aOptions['titleExists'] = ExtendedSearchBase::titleExists( $this->oSearchRequest->sInput, $this->aOptions );
		$this->aOptions['bExtendedForm'] = $this->oSearchRequest->bExtendedForm;

		$this->aOptions['searchStringWildcarded'] = SearchService::wildcardSearchstring( $this->aOptions['searchStringOrig'] );
		$this->aOptions['searchStringForStatistics'] = $this->aOptions['searchStringOrig'];

		//This overrides setting from SearchRequest::processInput
		//TODO: Check if still needed; find better place for this
		$oRequest = RequestContext::getMain()->getRequest();
		$scope = $oRequest->getVal( 'search_scope', BsConfig::get( 'MW::ExtendedSearch::DefScopeUser' ) ) == 'title'
			? 'title'
			: 'text';
		$this->aOptions['scope'] = $scope;
	}

	protected function readInSearchTerm() {
		$aSearchTitle = array(
			'title:(' . $this->aOptions['searchStringOrig'] . ')^5',
			'titleWord:(' . $this->aOptions['searchStringOrig'] . ')^2',
			'titleReverse:(' . $this->aOptions['searchStringWildcarded'] . ')',
			'redirects:(' . $this->aOptions['searchStringOrig'] . ')'
		);
		$aSearchText = array(
			'textWord:(' . $this->aOptions['searchStringOrig'] .')^2',
			'textReverse:(' . $this->aOptions['searchStringWildcarded'] . ')',
			'sections:(' . $this->aOptions['searchStringOrig'] . ')'
		);

		$this->aSearchOptions['qf'] = '';

		$sLogOp = ' OR ';
		$sSearchStringTitle = implode( $sLogOp, $aSearchTitle );
		$sSearchStringText = implode( $sLogOp, $aSearchText );

		$this->aOptions['searchStringFinal'] = ( $this->aOptions['scope'] === 'title' )
			? $sSearchStringTitle
			: $sSearchStringTitle . $sLogOp . $sSearchStringText;
	}

	protected function getFacetOperator( $sTagName ) {
		/*
		 * This is not a good solution as it is decoupled from the facet
		 * definition in "SearchResult::createFacets".
		 * But without a major refactoring there is not nice way to do this.
		 */
		$sLogOp = ' OR ';
		if ( in_array( $sTagName, array( 'ca', 'ed' ) ) ) {
			$sLogOp = ' AND ';
		}
		if( isset( $this->aOptions['fset'][$sTagName]['op'] ) ) {
			if( strtoupper( $this->aOptions['fset'][$sTagName]['op'] ) === 'OR' ) {
				$sLogOp = ' OR ';
			} else {
				$sLogOp = ' AND ';
			}
		}
		return $sLogOp;
	}

	protected function appendFilterQueryAndFacetFields( $aTerms, $sFieldName, $sTagName ) {
		//Allow extensions that register own document types to the
		//index to modify the query
		Hooks::run(
			'BSExtendedSearchSearchOptionsAppendFilterQueryAndFacetFields',
			array( $this, &$aTerms, $sFieldName, $sTagName )
		);

		$sFq = '';
		$sFacetField = $sFieldName;
		$sLogOp = $this->getFacetOperator( $sTagName );
		if ( !empty( $aTerms ) ) {
			$sFacetField = "{!ex=$sTagName}$sFieldName";
			if( BsConfig::get( 'MW::ExtendedSearch::ShowFacets' ) ) {
				$sFq = "{!tag=$sTagName}";
			}

			$sFq .= $sFieldName.':("' . implode( '"' . $sLogOp . '"', $aTerms ) . '")';
			$this->aFq[] = $sFq;
		}
		//We can add it in every case, because it get's only used by
		//'assembleSearchOptions' if 'MW::ExtendedSearch::ShowFacets' is true
		$this->aFacetFields[] = $sFacetField;
	}

	protected function makeAutoCompleteFilterQuery( $sSearchString, &$sSolrSearchString, &$aSearchOptionsFq ) {
		$vNamespace = $this->checkSearchstringForNamespace( $sSearchString, $sSolrSearchString, $aSearchOptionsFq );

		if( empty( $aSearchOptionsFq ) ) {
			$oUser = RequestContext::getMain()->getUser();
			$aOptions = $oUser->getOptions();
			$aNamespaces = [ 1000 ]; //For some strange reason 1000 is NS_SPECIAL within the SOLR index
			foreach ( $aOptions as $sOpt => $sValue ) {
				if ( strpos( $sOpt, 'searchNs' ) !== false && $sValue == true ) {
					$aNamespaces[] = '' . str_replace( 'searchNs', '', $sOpt );
				}
			}
			$aSearchOptionsFq[] = 'namespace:("'.implode( '" OR "', $aNamespaces ).'")';
		}

		$aSearchOptionsFq[] = 'wiki:(' . $this->getCustomerId() . ')';

		return $vNamespace;
	}
}
