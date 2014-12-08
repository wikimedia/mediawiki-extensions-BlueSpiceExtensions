<?php
/**
 * Manages search options for ExtendedSearch for MediaWiki
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
	protected $oRequestContext = null;

	/**
	 * Instance of search service
	 * @var object of search service
	 */
	protected static $oInstance = null;

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
		$aSearchOptions['fq'] = array( 'wiki:(' . $this->getCustomerId() . ')' );
		$aSearchOptions['sort'] = $this->aSearchOptions['sort'];

		$vNamespace = $this->checkSearchstringForNamespace(
			$sSearchString,
			$sSolrSearchString,
			$aSearchOptions['fq']
		);

		$aOptions = array();
		$aOptions['searchString'] = 'titleEdge:('.$sSolrSearchString.') OR title:('.$sSolrSearchString.')';
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
	 * Processes incoming search request
	 */
	public function readInSearchRequest() {
		global $wgCanonicalNamespaceNames, $wgExtraNamespaces, $wgContentNamespaces;
		$this->aOptions['searchStringRaw'] = $this->oSearchRequest->sInput;
		$this->aOptions['searchStringOrig'] = ExtendedSearchBase::preprocessSearchInput( $this->oSearchRequest->sInput );

		self::$searchStringRaw = $this->aOptions['searchStringRaw'];

		$sCustomerId = $this->getCustomerId();
		$sLogOp = ' OR ';
		$aFq = array();
		$aBq = array();

		$scope = ( BsConfig::get( 'MW::ExtendedSearch::DefScopeUser' ) == 'title' )
			? 'title'
			: 'text';
		$this->aOptions['scope'] = $scope;

		$vNamespace = $this->checkSearchstringForNamespace(
			$this->aOptions['searchStringRaw'],
			$this->aOptions['searchStringOrig'],
			$aFq,
			BsConfig::get( 'MW::ExtendedSearch::ShowFacets' )
		);

		$this->aOptions['searchStringWildcarded'] = SearchService::wildcardSearchstring( $this->aOptions['searchStringOrig'] );
		$this->aOptions['searchStringForStatistics'] = $this->aOptions['searchStringWildcarded'];

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

		$sSearchStringTitle = implode( $sLogOp, $aSearchTitle );
		$sSearchStringText = implode( $sLogOp, $aSearchText );

		$this->aOptions['searchStringFinal'] = ( $this->aOptions['scope'] === 'title' )
			? $sSearchStringTitle
			: $sSearchStringTitle . $sLogOp . $sSearchStringText;

		// filter query
		$aFq[] = 'redirect:0';
		$aFq[] = 'special:0';
		$aFq[] = 'wiki:('.$sCustomerId.')';

		// $this->aOptions['namespaces'] HAS TO BE an array with numeric indices of type string!
		$this->aOptions['namespaces'] = $this->oSearchRequest->aNamespaces;

		$aNamespaces = array_slice( $wgCanonicalNamespaceNames, 2 );
		$aNamespaces = $aNamespaces + $wgExtraNamespaces;

		$bTagNamespace = false;
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
					$bTagNamespace = true;
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
					$bTagNamespace = true;
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
			$bTagNamespace = true;
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

			$bTagNamespace = true;
			$aFq[] = ( BsConfig::get( 'MW::ExtendedSearch::ShowFacets' ) )
				? '{!tag=na}namespace:("' . implode( '" "', $aFqNamespaces ) . '")'
				: 'namespace:("' . implode( '" "', $aFqNamespaces ) . '")';
		}

		// $this->aOptions['cats'] = $this->oSearchRequest->sCat; // string, defaults to '' if 'search_cat' not set in REQUEST
		$this->aOptions['cats'] = $this->oSearchRequest->sCategories; // array of strings or empty array
		if ( !empty( $this->aOptions['cats'] ) ) {
			if ( isset( $this->oSearchRequest->sOperator ) ) {
				switch ( $this->oSearchRequest->sOperator ) {
					case 'AND':
						$sLogOp = ' AND ';
						break;
					default:
				}
			}
			$sFqCategories = ( BsConfig::get( 'MW::ExtendedSearch::ShowFacets' ) ) ? '{!tag=ca}' : '';
			$sFqCategories .= 'cat:("' . implode( '"' . $sLogOp . '"', $this->aOptions['cats'] ) . '")';
			$aFq[] = $sFqCategories;
		}

		$this->aOptions['type'] = $this->oSearchRequest->aType;
		if ( !empty( $this->aOptions['type'] ) ) {
			$sFqType = ( BsConfig::get( 'MW::ExtendedSearch::ShowFacets' ) ) ? '{!tag=ty}' : '';
			$sFqType .= 'type:("' . implode( '"' . $sLogOp . '"', $this->aOptions['type'] ) . '")';
			$aFq[] = $sFqType;
		}

		$this->aOptions['editor'] = $this->oSearchRequest->sEditor;
		if ( !empty( $this->aOptions['editor'] ) ) {
			// there may be spaces in name of editor. solr analyses those to two
			// terms (editor's names) thus we wrap the name into quotation marks
			// todo: better: in schema.xml define field editor not to be tokenized
			//       at whitespace
			// but: +editor:("Robert V" "Mathias S") is already split correctly!
			$sFqEditor = ( BsConfig::get( 'MW::ExtendedSearch::ShowFacets' ) ) ? '{!tag=ed}' : '';
			$sFqEditor .= 'editor:("' . implode( '"' . $sLogOp . '"', $this->aOptions['editor'] ) . '")';
			$aFq[] = $sFqEditor;
		}

		// Boost query
		foreach ( $wgContentNamespaces as $iNs ) {
			$aBq[] = "namespace:{$iNs}^2";
		}
		// We want that files are also seen as a content namespace
		$aBq[] = "namespace:999^2";

		$searchLimit = BsConfig::get( 'MW::ExtendedSearch::LimitResults' );

		$this->aOptions['offset'] = $this->oSearchRequest->iOffset;
		$this->aOptions['order'] = $this->oSearchRequest->sOrder;
		$this->aOptions['asc'] = $this->oSearchRequest->sAsc;
		$this->aOptions['searchLimit'] = ( $searchLimit == 0 ) ? 15 : $searchLimit;
		$this->aOptions['titleExists'] = ExtendedSearchBase::titleExists( $this->oSearchRequest->sInput, $this->aOptions );
		$this->aOptions['bExtendedForm'] = $this->oSearchRequest->bExtendedForm;

		$this->aSearchOptions['defType'] = 'edismax';
		$this->aSearchOptions['fl'] = 'uid,type,title,path,namespace,cat,ts,redirects,overall_type';
		$this->aSearchOptions['fq'] = $aFq;
		$this->aSearchOptions['sort'] = $this->aOptions['order'] . ' ' . $this->aOptions['asc'];
		$this->aSearchOptions['hl'] = 'on';
		$this->aSearchOptions['hl.fl'] = 'titleWord, titleReverse, sections, textWord, textReverse';
		$this->aSearchOptions['hl.snippets'] = BsConfig::get( 'MW::ExtendedSearch::HighlightSnippets' );
		$this->aSearchOptions['bq'] = implode( ' ', $aBq );

		if ( BsConfig::get( 'MW::ExtendedSearch::ShowFacets' ) ) {
			$this->aSearchOptions['facet'] = 'on';
			$this->aSearchOptions['facet.sort'] = 'false';
			$this->aSearchOptions['facet.mincount'] = '1';
			$this->aSearchOptions['facet.missing'] = 'true';
			$this->aSearchOptions['facet.field'] = array();

			$this->aSearchOptions['facet.field'][] = ( $bTagNamespace === true )
				? '{!ex=na}namespace'
				: 'namespace';

			$this->aSearchOptions['facet.field'][] = ( isset( $sFqCategories ) )
				? '{!ex=ca}cat'
				: 'cat';

			$this->aSearchOptions['facet.field'][] = ( isset( $sFqType ) )
				? '{!ex=ty}type'
				: 'type';

			$this->aSearchOptions['facet.field'][] = ( isset( $sFqEditor ) )
				? '{!ex=ed}editor'
				: 'editor';
		}
	}

	/**
	 * Reads in searchstring and checks if a namespace is in it
	 * @param string $sSearchString given searchstring
	 * @param string $sSolrSearchString the solr searchstring
	 * @param array $aQueryFq solr filter query
	 * @param boolean $bWtihTag flag for tagging
	 * @return int|boolean id of namespace or false
	 */
	public function checkSearchstringForNamespace( $sSearchString, &$sSolrSearchString, &$aQueryFq, $bWtihTag = false ) {
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

		$aQueryFq[] = ( $bWtihTag )
			? '{!tag=na}namespace:(' . $iNamespace . ')'
			: 'namespace:(' . $iNamespace . ')';

		return $iNamespace;
	}

}