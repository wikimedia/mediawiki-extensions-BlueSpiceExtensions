<?php
/**
 * Indexer for ExtendedSearch for MediaWiki
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
 * Searcher for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class SearchIndex {

	/**
	 * Instance of SearchService
	 * @var SearchService object
	 */
	protected $oSearchService = null;
	/**
	 * Instance of SearchOptions
	 * @var object SearchOptions object
	 */
	protected $oSearchOptions = null;
	/**
	 * Instance of SearchRequest
	 * @var object SearchRequest object
	 */
	protected $oSearchRequest = null;
	/**
	 * RequestContext
	 * @var object RequestContext object
	 */
	protected $oContext;
	/**
	 * SearchUriBuilder object
	 * @var object SearchUriBuilder object
	 */
	protected $oUriBuilder = null;
	/**
	 * Instance of SearchIndex
	 * @var object SearchIndex object
	 */
	protected static $oInstance = null;

	/**
	 * Constructor for SearchIndex class
	 * @param SearchService $oSearchService current search service
	 * @param SearchRequest $oSearchRequest current search request
	 * @param SearchOptions $oSearchOptions current search options
	 * @param BsSearchUriBuilder $oSearchUriBuilder current search uri builder
	 * @param RequestContext $oContext Current search service
	 */
	public function __construct( $oSearchService, $oSearchRequest, $oSearchOptions, $oSearchUriBuilder, $oContext ) {
		$this->oSearchRequest = $oSearchRequest;
		$this->oSearchOptions = $oSearchOptions;
		$this->oUriBuilder = $oSearchUriBuilder;
		$this->oSearchService = $oSearchService;
		$this->oContext = $oContext;
	}

	/**
	 * Return a instance of SearchIndex.
	 * @return SearchIndex Instance of SearchIndex
	 */
	public static function getInstance() {
		if ( self::$oInstance === null ) {
			self::$oInstance = new self();
		}

		return self::$oInstance;
	}

	/**
	 * This functions searches the index for a given search term
	 * @param array &$aMonitor Set of options.
	 * @return ViewSearchResult View that describes search results
	 */
	public function search( &$aMonitor ) {
		/* Jump to page */
		if ( BsConfig::get( 'MW::ExtendedSearch::JumpToTitle' )
			&& ( $this->oSearchOptions->getOption( 'titleExists' ) === true )
			&& ( $this->oSearchRequest->bSft === true ) ) {

			$this->oContext->getOutput()->redirect(
				$this->oSearchOptions->getOption( 'existingTitleObject' )->getFullURL()
			);
		}

		if ( empty( $this->oSearchRequest->sInput ) ) {
			if ( $this->oSearchOptions->getOption( 'searchStringOrig' ) == '' ) {
				return $this->createErrorMessageView( 'bs-extendedsearch-nosearchterm' );
			} else {
				$vbe = new ViewBaseElement();
				$vbe->setAutoElement( false );
				return $vbe;
			}
		}

		$query = $this->oSearchOptions->getSolrQuery();
		try {
			$oHits = $this->oSearchService->search(
				$query['searchString'], $query['offset'],
				$query['searchLimit'], $query['searchOptions']
			);
		} catch ( Exception $e ) {
			if ( stripos( $e->getMessage(), 'Communication Error' ) !== false ) {
				$sUrl = SpecialPage::getTitleFor( 'Search' )->getFullURL();

				$sParams = 'search='.urlencode( $this->oSearchOptions->getOption( 'searchStringRaw' ) );
				$sParams .= ( $this->oSearchOptions->getOption( 'scope' ) == 'title' )
					? '&go='
					: '&fulltext=Search';

				foreach ( $this->oSearchOptions->getOption( 'namespaces' ) as $namespace ) {
					$sParams .= "&ns{$namespace}=1";
				}
				$sUrl .= ( ( strpos( $sUrl, '?' ) === false ) ? '?' : '&').$sParams;

				return $this->oContext->getOutput()->redirect( $sUrl, '404' );
			}

			wfDebugLog( 'ExtendedSearch', $e->getMessage() );
			return $this->createErrorMessageView( 'bs-extendedsearch-invalid-query' );
		}

		$iNumFound = $oHits->response->numFound;

		$bFuzzy = ( $iNumFound == 0 );
		// Make a fuzzy query
		if ( $bFuzzy ) {
			$aFuzzyQuery = $this->oSearchOptions->getSolrFuzzyQuery();
			try {
				$oHits = $this->oSearchService->search(
					$aFuzzyQuery['searchString'], $aFuzzyQuery['offset'],
					$aFuzzyQuery['searchLimit'], $aFuzzyQuery['searchOptions']
				);
			} catch ( Exception $e ) {
				return $this->createErrorMessageView( 'bs-extendedsearch-invalid-query' );
			}

			$aSpell = array(
				'sim' => $this->oSearchService->getSpellcheck(
						$this->oSearchOptions->getOption( 'searchStringRaw' ),
						$this->oSearchOptions->getSearchOptionsSim()
					),
				'url' => $this->oUriBuilder->buildUri(
						SearchUriBuilder::ALL,
						SearchUriBuilder::INPUT|SearchUriBuilder::MLT|SearchUriBuilder::ORDER_ASC_OFFSET
					)
			);

			$iNumFound = $oHits->response->numFound;
		}

		$this->logSearch(
			$this->oSearchOptions->getOption( 'searchStringForStatistics' ),
			$iNumFound,
			$this->oSearchOptions->getOption( 'scope' ),
			$this->oSearchOptions->getOptionBool( 'files' )
		);

		$bFacetted = (bool)BsConfig::get( 'MW::ExtendedSearch::ShowFacets' );

		$oSearchResult = new BsSearchResult( $this->oContext, $this->oSearchOptions, $this->oUriBuilder, $oHits );

		if ( $bFuzzy ) {
			$oSearchResult->setData( 'spell', $aSpell );
		}

		return $oSearchResult->createSearchResult( $aMonitor, $iNumFound, $bFuzzy, $bFacetted );
	}

	/**
	 * Writes a given search request to database log.
	 * @param string $term Search term
	 * @param int $iNumFound Number of hits
	 * @param string $scope What was the scope of the search?
	 * @param string $files Were files searched as well?
	 * @return bool always false.
	 */
	public function logSearch( $term, $iNumFound, $scope, $files ) {
		if ( !BsConfig::get( 'MW::ExtendedSearch::Logging' ) ) return false;

		$oDbw = wfGetDB( DB_MASTER );

		$term = BsCore::sanitize( $term, '', BsPARAMTYPE::SQL_STRING );

		$user = ( BsConfig::get( 'MW::ExtendedSearch::LogUsers' ) )
			? RequestContext::getMain()->getUser()->getId()
			: '';

		$effectiveScope = ( $files ) ? $scope.'-files' : $scope;
		$data = array(
			'stats_term' => $term,
			'stats_ts' => wfTimestamp( TS_MW ),
			'stats_user' => $user,
			'stats_hits' => $iNumFound,
			'stats_scope' => $effectiveScope
		);

		$oDbw->insert( 'bs_searchstats', $data );

		return true;
	}

	/**
	 * Renders error message
	 * @param string $sMessage I18N key of error message
	 * @return ViewBaseElement Renders error message.
	 */
	public function createErrorMessageView( $sMessage ) {
		$res = new ViewBaseElement();
		$res->setTemplate( '<div id="bs-es-searchterm-error">' . wfMessage( 'bs-extendedsearch-error' )->plain() . ' {message}</div>' );
		$res->addData( array( 'message' => wfMessage( $sMessage )->plain() ) );
		return $res;
	}


}
