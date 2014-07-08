<?php
/**
 * Indexer for ExtendedSearch for MediaWiki
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
 * Indexer for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class SearchIndex {

	/**
	 * Instance of SearchService
	 * @var SearchService object of SearchService
	 */
	protected $oSearchService;
	/**
	 * Instance of SearchOptions
	 * @var SearchOptions object of SearchOptions
	 */
	protected $oSearchOptions;
	/**
	 * Instance of SearchRequest
	 * @var SearchRequest object of SearchRequest
	 */
	protected $oSearchRequest;
	/**
	 * RequestContext
	 * @var RequestContext object of RequestContext
	 */
	protected $oContext;
	/**
	 * View that renders search results
	 * @var ViewSearchResult ViewSearchResult object
	 */
	protected $vSearchResult;
	/**
	 * Maximum number of facet items
	 * @var int Number
	 */
	protected $iMaxFacetLength = 20;
	/**
	 * SearchUriBuilder object
	 * @var SearchUriBuilder SearchUriBuilder object
	 */
	protected $oUriBuilder = null;
	/**
	 * Instance of search service
	 * @var object of search service
	 */
	protected static $oInstance = null;

	/**
	 * Constructor for SearchIndexMW class
	 * @param BsSearchService $searchServiceObject Current search service
	 */
	public function __construct( $oSearchRequest, $oSearchOptions, $oSearchUriBuilder, $oContext ) {
		$this->oSearchRequest = $oSearchRequest;
		$this->oSearchOptions = $oSearchOptions;
		$this->oUriBuilder = $oSearchUriBuilder;
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
	 * Shorten facet string.
	 * @param string $sFacet Name of facet
	 * @return string Shortened name of facet
	 */
	public function reduceMaxFacetLength( $sFacet ) {
		$sFacet = str_replace( '_', ' ', $sFacet );
		return BsStringHelper::shorten(
					$sFacet,
					array(
						'max-length' => $this->iMaxFacetLength,
						'position' => 'middle'
					)
				);
	}

	/**
	 * Shorten facet string.
	 * @param string $sFacet Name of facet
	 * @return string Shortened name of facet
	 */
	public function getFacetTitle( $sFacet ) {
		$sFacet = str_replace( '_', ' ', $sFacet );
		$sTitle = '';
		if ( mb_strlen( $sFacet ) >= $this->iMaxFacetLength ) {
			$sTitle = $sFacet;
		}

		return $sTitle;
	}

	/**
	 * This functions searches the index for a given search term
	 * @param BsSearchRequest $oSearchService Current search request
	 * @param array &$aMonitor Set of options.
	 * @return ViewSearchResult View that describes search results
	 */
	public function search( $oSearchService, &$aMonitor ) {
		$this->oSearchService = $oSearchService;
		global $wgScriptPath;

		/* Jump to page */
		if ( BsConfig::get( 'MW::ExtendedSearch::JumpToTitle' )
			&& ( $this->oSearchOptions->getOption( 'titleExists' ) === true )
			&& ( $this->oSearchRequest->sOrigin == 'titlebar' )
			&& ( $this->oSearchRequest->bAutocomplete === false ) ) {

			$this->oContext->getOutput()->redirect(
				$this->oSearchOptions->getOption( 'existingTitleObject' )->getFullURL()
			);
		}

		if ( !$this->oSearchRequest->isSearchable() ) {
			if ( $this->oSearchRequest->sOrigin != '' && $this->oSearchOptions->getOption( 'searchStringOrig' ) == '' ) {
				return $this->createErrorMessageView( 'bs-extendedsearch-nosearchterm' );
			} else {
				$vbe = new ViewBaseElement();
				$vbe->setAutoElement( false );
				return $vbe;
			}
		}

		$query = $this->oSearchOptions->getSolrQuery();
		try {
			// signature of BsSearchService, member function searchs($query, $offset = 0, $limit = 10, $params = array())
			$oHits = $this->oSearchService->search( $query['searchString'], $query['offset'], $query['searchLimit'], $query['searchOptions'] );
		} catch ( Exception $e ) {
			// bs-extendedsearch-invalid-query
			if ( $e->getMessage() == '"0" Status: Communication Error' ) {
				$sUrl = SpecialPage::getTitleFor( 'Search' )->getFullURL();

				$sParams = 'search='.urlencode( $this->oSearchOptions->getOption( 'searchStringRaw' ) );
				$sParams .= ( $this->oSearchOptions->getOption( 'scope' ) == 'title' ) ? '&go=' : '&fulltext=Search';
				foreach ( $this->oSearchOptions->getOption( 'namespaces' ) as $namespace ) {
					$sParams .= "&ns{$namespace}=1";
				}
				$sUrl .= ( ( strpos( $sUrl, '?' ) === false ) ? '?' : '&').$sParams;

				return $this->oContext->getOutput()->redirect( $sUrl, '404' );
			}

			return $this->createErrorMessageView( 'bs-extendedsearch-invalid-query' );
		}

		$iNumFound = $oHits->response->numFound;

		$bEscalateToFuzzy = ( $iNumFound == 0 ); // boolean!
		// escalate to fuzzy
		if ( $bEscalateToFuzzy ) {
			$aFuzzyQuery = $this->oSearchOptions->getSolrFuzzyQuery();
			try {
				$oHits = $this->oSearchService->search( $aFuzzyQuery['searchString'], $aFuzzyQuery['offset'], $aFuzzyQuery['searchLimit'], $aFuzzyQuery['searchOptions'] );
			} catch ( Exception $e ) {
				return $this->createErrorMessageView( 'bs-extendedsearch-invalid-query' );
			}

			$iNumFound = $oHits->response->numFound;
		}

		$this->logSearch(
			$this->oSearchOptions->getOption( 'searchStringForStatistics' ),
			$iNumFound,
			$this->oSearchOptions->getOption( 'scope' ),
			$this->oSearchOptions->getOptionBool( 'files' )
		);

		$this->vSearchResult = new ViewSearchResult();

		$this->vSearchResult->setOption( 'siteUri', $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::MLT ) );

		if ( $iNumFound == 0 || $bEscalateToFuzzy ) {
			$this->vSearchResult->addSpell(
				array(
					'script_path' => $wgScriptPath,
					'sim' => $this->oSearchService->getSpellcheck( $this->oSearchOptions->getOption( 'searchStringRaw' ), $this->oSearchOptions->getSearchOptionsSim() ),
					'url' => $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::INPUT | SearchUriBuilder::MLT | SearchUriBuilder::ORDER_ASC_OFFSET )
				)
			);
		}

		if ( BsConfig::get( 'MW::ExtendedSearch::ShowCreateSugg' ) ) {
			if ( $iNumFound == 0 || $bEscalateToFuzzy || !$this->oSearchOptions->getOption( 'titleExists' ) ) {
				$this->vSearchResult->addSuggest(
					array(
						'search' => $this->oSearchOptions->getOption( 'searchStringRaw' ),
						'script_path' => $wgScriptPath
					)
				);
			}
		}

		$aMonitor['NoOfResultsFound'] = $iNumFound;
		$aMonitor['SearchTerm'] = $this->oSearchOptions->getOption( 'searchStringRaw' );
		$aMonitor['EscalatedToFuzzy'] = $bEscalateToFuzzy;

		if ( $iNumFound == 0 ) return $this->vSearchResult;

		//--------- Navigation

		$aPaging = array();

		$searchLimit = $this->oSearchOptions->getOption( 'searchLimit' );

		$loopsCalculated = ( $iNumFound / $searchLimit ) + 1;
		$this->vSearchResult->setOption( 'activePage', 1 );
		$url_offset = $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::OFFSET );

		for ( $i = 1; $i < $loopsCalculated; $i++ ) {
			$offset_step = ( ( $i - 1 ) * $searchLimit );
			if ( $offset_step == $this->oSearchOptions->getOption( 'offset' ) ) {
				$this->vSearchResult->setOption( 'activePage', $i );
			}
			$aPaging[$i] = "{$url_offset}&search_offset={$offset_step}";
		}

		$this->vSearchResult->setOption( 'pages', $aPaging );

		$aSortTypes = array(
			'titleSort' => 'bs-extendedsearch-sort-title',
			'score' => 'bs-extendedsearch-sort-relevance',
			'type' => 'bs-extendedsearch-sort-type',
			'ts' => 'bs-extendedsearch-sort-ts'
		);

		$aSorting = array(
			'sorttypes' => $aSortTypes,
			'sortactive' => isset( $aSortTypes[$this->oSearchOptions->getOption( 'order' )] )
					? $this->oSearchOptions->getOption( 'order' )
					: 'score',
			'sortdirection' => ( $this->oSearchOptions->getOption( 'asc' ) == 'asc' ) ? 'asc' : 'desc',
			'sorturl' => $this->oUriBuilder->buildUri(
					SearchUriBuilder::ALL,
					SearchUriBuilder::MLT|SearchUriBuilder::ORDER_ASC_OFFSET
				)
			);

		$this->vSearchResult->setOption( 'numFound', $iNumFound );
		$this->vSearchResult->setOption( 'sorting', $aSorting );

		//---------- end navigation

		//---------- begin facets

		if ( BsConfig::get( 'MW::ExtendedSearch::ShowFacets' ) ) {
			$this->vSearchResult->setOption( 'showfacets', true );

			// possible sortoders: count, name, checked
			// 1 = desc, -1 = asc
			if ( BsConfig::get( 'MW::SortAlph' ) ) {
				$this->sortorder = array( 'name' => -1 );
			} else {
				$this->sortorder = array( 'count' => 1 );
			}

			// --------------- begin facet namespace

			if ( !is_null( $oHits->facet_counts->facet_fields->namespace ) ) {
				$vFacetBoxNamespaces = $this->vSearchResult->generateViewFacetBox();
				$vFacetBoxNamespaces->setOption( 'i18n-key-facet-title', 'bs-extendedsearch-facet-namespace' );
				$vFacetBoxNamespaces->setOption( 'uri-facet-delete', $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::NAMESPACES|SearchUriBuilder::FILES ) );

				$facetNamespaceAll = array(); // alters to: array( 0 => array( 'checked' => true ), 1 => array( 'count' => 15 ), 999 => array( 'checked' => true, 'count' => 2 ) )

				$aNamespaces = $this->oSearchOptions->getOption( 'namespaces' );

				if ( !empty( $aNamespaces ) ) {
					foreach ( $aNamespaces as $key => $namespace ) {
						$facetNamespaceAll[$namespace]['checked'] = true;
					}
					unset( $aNamespaces );
				}

				$aFacetsNamespaceInHits = $oHits->facet_counts->facet_fields->namespace; // stdClass object { 0 => (int)6, 999 => (int)1, _empty_ => (int)0 }
				foreach ( $aFacetsNamespaceInHits as $namespace => $count ) {
					if ( BsNamespaceHelper::checkNamespacePermission( $namespace, 'read' ) === false ) {
						unset( $facetNamespaceAll[$namespace] );
						continue;
					}
					if ( $namespace == '_empty_' ) continue;
					$facetNamespaceAll[$namespace]['count'] = $count;
				}

				foreach ( $facetNamespaceAll as $namespace => $attributes ) {
					$facetNamespaceAll[$namespace]['title'] = '';
					if ( $namespace == '999' ) {
						$namespaceTitle = wfMessage( 'bs-extendedsearch-facet-namespace-files' )->plain();
					} elseif ( $namespace == '998' ) {
						$namespaceTitle = wfMessage( 'bs-extendedsearch-facet-namespace-extfiles' )->plain();
					} elseif ( $namespace == '0' ) {
						$namespaceTitle = wfMessage( 'bs-ns_main' )->plain();
					} else {
						$namespaceTitle = BsNamespaceHelper::getNamespaceName( $namespace, false );
						$facetNamespaceAll[$namespace]['title'] = $this->getFacetTitle( $namespaceTitle );
						if ( empty( $namespaceTitle ) ) {
							unset( $facetNamespaceAll[$namespace] );
							continue;
						} else {
							$namespaceTitle = $this->reduceMaxFacetLength( $namespaceTitle );
						}
					}
					$facetNamespaceAll[$namespace]['name'] = $namespaceTitle;
				}

				uasort( $facetNamespaceAll, array( $this, 'compareEntries' ) );

				$allNamespacesAvailableParamsOnly = array();
				foreach ( $facetNamespaceAll as $namespace => $attributes ) {
					if ( !isset( $attributes['count'] ) ) continue;

					$aDataSet = array();
					if ( isset( $attributes['checked'] ) ) $aDataSet['checked'] = true;

					if ( $namespace == '999' ) {
						$uri = $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::NAMESPACES | SearchUriBuilder::FILES );
						$uri .= ( isset( $attributes['checked'] ) ) ? '&search_files=0' : '&search_files=1';
					} else {
						$uri = $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::NAMESPACES );
					}

					foreach ( $facetNamespaceAll as $namespaceUrl => $attributesUrl ) {
						$bOwnUrlAndNotAlreadyChecked = ( ( $namespace == $namespaceUrl ) && !isset( $attributesUrl['checked'] ) );
						$bOtherUrlAndAlreadyChecked = ( ( $namespace != $namespaceUrl ) && isset( $attributesUrl['checked'] ) );
						if ( $bOwnUrlAndNotAlreadyChecked || $bOtherUrlAndAlreadyChecked ) {
							$uri .= '&na[]='.$namespaceUrl;
						}
					}

					$aDataSet['uri'] = $uri;
					$aDataSet['diff'] = 'na[]='.$namespace;
					$aDataSet['name'] = $attributes['name'];
					$aDataSet['title'] = $attributes['title'];
					$aDataSet['count'] = ( isset( $attributes['count'] ) ) ? (int)$attributes['count'] : 0;

					$vFacetBoxNamespaces->addData( $aDataSet );
					$allNamespacesAvailableParamsOnly[] = 'na[]='.$namespace;
				}

				$allNamespacesAvailableParamsOnly = implode( '&', $allNamespacesAvailableParamsOnly );
				$vFacetBoxNamespaces->setOption(
					'uri-facet-all',
					$this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::NAMESPACES|SearchUriBuilder::FILES ).'&'.$allNamespacesAvailableParamsOnly );
				$vFacetBoxNamespaces->setOption( 'uri-facet-all-diff', $allNamespacesAvailableParamsOnly );
			}
			// --------------- end facet namespaces

			// --------------- begin facet categories

			if ( !is_null( $oHits->facet_counts->facet_fields->cat ) ) {
				$vFacetBoxCategories = $this->vSearchResult->generateViewFacetBox();
				$vFacetBoxCategories->setOption( 'i18n-key-facet-title', 'bs-extendedsearch-facet-category' );
				$vFacetBoxCategories->setOption( 'uri-facet-delete', $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::CATS ) );

				$facetCategoriesAll = array();

				$aCats = $this->oSearchOptions->getOption( 'cats' );
				if ( !empty( $aCats ) ) {
					foreach ( $aCats as $key => $categoryName ) {
						$facetCategoriesAll[$categoryName]['checked'] = true;
					}
					unset( $aCats );
				}

				$aFacetsCategoriesInHits = $oHits->facet_counts->facet_fields->cat; // stdClass object { catName => (int)1, anotherCatName => (int)6, _empty_ => (int)1 }
				foreach ( $aFacetsCategoriesInHits as $cat => $count ) {
					if ( $cat == '_empty_' ) continue;
					$facetCategoriesAll[$cat]['count'] = $count;
				}

				foreach ( $facetCategoriesAll as $cat => $attributes ) {
					$facetCategoriesAll[$cat]['title'] = $this->getFacetTitle( $cat );
					if ( $cat == 'notcategorized' ) {
							$catTitle = wfMessage( 'bs-extendedsearch-facet-uncategorized' )->plain();
					} else {
						$catTitle = $this->reduceMaxFacetLength( $cat );
					}
					$facetCategoriesAll[$cat]['name'] = $catTitle;
				}

				uasort( $facetCategoriesAll, array( $this, 'compareEntries' ) );

				$allCategoriesAvailableParamsOnly = array();
				foreach ( $facetCategoriesAll as $cat => $attributes ) {
					$aDataSet = array();
					if ( isset( $attributes['checked'] ) ) $aDataSet['checked'] = true;

					$uri = $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::CATS );
					foreach ( $facetCategoriesAll as $catUrl => $attributesUrl ) {
						$bOwnUrlAndNotAlreadyChecked = ( ( $cat == $catUrl ) && !isset( $attributesUrl['checked'] ) );
						$bOtherUrlAndAlreadyChecked = ( ( $cat != $catUrl ) && isset( $attributesUrl['checked'] ) );
						if ( $bOwnUrlAndNotAlreadyChecked || $bOtherUrlAndAlreadyChecked )
							$uri .= '&ca[]='.$catUrl;
					}

					$aDataSet['uri'] = $uri;
					$aDataSet['diff'] = 'ca[]='.$cat;
					$aDataSet['name'] = $attributes['name'];
					$aDataSet['title'] = $attributes['title'];
					$aDataSet['count'] = ( isset( $attributes['count'] ) ) ? (int)$attributes['count'] : 0;

					$vFacetBoxCategories->addData( $aDataSet );
					$allCategoriesAvailableParamsOnly[] = 'ca[]='.$cat;
				}

				$allCategoriesAvailableParamsOnly = implode( '&', $allCategoriesAvailableParamsOnly );
				$vFacetBoxCategories->setOption( 'uri-facet-all', $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::CATS ).'&'.$allCategoriesAvailableParamsOnly );
				$vFacetBoxCategories->setOption( 'uri-facet-all-diff', $allCategoriesAvailableParamsOnly );
			}
			// --------------- end facet categories

			// --------------- begin facet type

			if ( !is_null( $oHits->facet_counts->facet_fields->type ) ) {
				$vFacetBoxTypes = $this->vSearchResult->generateViewFacetBox();
				$vFacetBoxTypes->setOption( 'i18n-key-facet-title', 'bs-extendedsearch-facet-type' );
				$vFacetBoxTypes->setOption( 'uri-facet-delete', $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::TYPE ) );

				$facetTypeAll = array();

				$aTypes = $this->oSearchOptions->getOption( 'type' );
				if ( !empty( $aTypes ) ) {
					foreach ( $aTypes as $key => $type ) {
						$facetTypeAll[$type]['checked'] = true;
					}
					unset( $aTypes );
				}

				$aFacetsTypeInHits = $oHits->facet_counts->facet_fields->type;
				foreach ( $aFacetsTypeInHits as $type => $count ) {
					if ( $type == '_empty_' ) continue;
					if ( $type != 'wiki' && !$this->oContext->getUser()->isAllowed( 'searchfiles' ) ) {
						continue;
					}
					$facetTypeAll[$type]['count'] = $count;
				}

				foreach ( $facetTypeAll as $type => $attributes ) {
					$facetTypeAll[$type]['title'] = $this->getFacetTitle( $type );
					$facetTypeAll[$type]['name'] = $this->reduceMaxFacetLength( $type );
				}

				uasort( $facetTypeAll, array( $this, 'compareEntries' ) );

				$allTypesAvailableParamsOnly = array();
				foreach ( $facetTypeAll as $type => $attributes ) {
					$aDataSet = array();
					if ( isset( $attributes['checked'] ) ) $aDataSet['checked'] = true;

					$uri = $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::TYPE );

					foreach ( $facetTypeAll as $typeUrl => $attributesUrl ) {
						$bOwnUrlAndNotAlreadyChecked = ( ( $type == $typeUrl ) && !isset( $attributesUrl['checked'] ) );
						$bOtherUrlAndAlreadyChecked = ( ( $type != $typeUrl ) && isset( $attributesUrl['checked'] ) );

						if ( $bOwnUrlAndNotAlreadyChecked || $bOtherUrlAndAlreadyChecked ) {
							$uri .= '&ty[]='.$typeUrl;
						}
					}

					$aDataSet['uri'] = $uri;
					$aDataSet['diff'] = 'ty[]='.$type;
					$aDataSet['name'] = $attributes['name'];
					$aDataSet['title'] = $attributes['title'];
					$aDataSet['count'] = ( isset( $attributes['count'] ) ) ? (int)$attributes['count'] : 0;

					$vFacetBoxTypes->addData($aDataSet);
					$allTypesAvailableParamsOnly[] = 'ty[]='.$type;
				}

				$allTypesAvailableParamsOnly = implode( '&', $allTypesAvailableParamsOnly );
				$vFacetBoxTypes->setOption( 'uri-facet-all', $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::TYPE ).'&'.$allTypesAvailableParamsOnly );
				$vFacetBoxTypes->setOption( 'uri-facet-all-diff', $allTypesAvailableParamsOnly );
			}
			// --------------- end facet type

			// --------------- begin facet editor

			if ( !is_null( $oHits->facet_counts->facet_fields->editor ) ) {
				$vFacetBoxEditors = $this->vSearchResult->generateViewFacetBox();
				$vFacetBoxEditors->setOption( 'i18n-key-facet-title', 'bs-extendedsearch-facet-editors' );
				$vFacetBoxEditors->setOption( 'uri-facet-delete', $this->oUriBuilder->buildUri(SearchUriBuilder::ALL, SearchUriBuilder::EDITOR ) );

				$facetEditorAll = array();

				$aEditors = $this->oSearchOptions->getOption( 'editor' );
				if ( !empty( $aEditors ) ) {
					foreach ( $this->oSearchOptions->getOption( 'editor' ) as $key => $editor ) {
						$facetEditorAll[$editor]['checked'] = true;
					}
					unset( $aEditors );
				}

				$aFacetsEditorInHits = $oHits->facet_counts->facet_fields->editor;
				foreach ( $aFacetsEditorInHits as $editor => $count ) {
					// todo: previously for _empty_ entry wfMessage( 'facet-noeditors' )
					// was displayed with count and without link
					// treat analogue to categories: index with field editor: anonymous
					if ( $editor == '_empty_' ) continue;
					$facetEditorAll[$editor]['count'] = $count;
				}

				foreach ( $facetEditorAll as $editor => $attributes ) {
					$facetEditorAll[$editor]['title'] = $this->getFacetTitle( $editor );
					$facetEditorAll[$editor]['name'] = $this->reduceMaxFacetLength( $editor );
				}

				uasort( $facetEditorAll, array( $this, 'compareEntries' ) );

				$allEditorsAvailableParamsOnly = array();
				foreach ( $facetEditorAll as $editor => $attributes ) {
					if ( $editor == 'unknown' ) $attributes['name'] = wfMessage( 'bs-extendedsearch-unknown' )->plain();

					$aDataSet = array();
					if ( isset( $attributes['checked'] ) ) $aDataSet['checked'] = true;

					$uri = $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::EDITOR );
					foreach ( $facetEditorAll as $editorUrl => $attributesUrl ) {
						$bOwnUrlAndNotAlreadyChecked = ( ( $editor == $editorUrl ) && !isset( $attributesUrl['checked'] ) );
						$bOtherUrlAndAlreadyChecked = ( ( $editor != $editorUrl ) && isset( $attributesUrl['checked'] ) );

						if ( $bOwnUrlAndNotAlreadyChecked || $bOtherUrlAndAlreadyChecked ) {
							$uri .= '&ed[]='.$editorUrl;
						}
					}

					$aDataSet['uri']   = $uri;
					$aDataSet['diff']  = 'ed[]='.$editor;
					$aDataSet['name']  = $attributes['name'];
					$aDataSet['title'] = $attributes['title'];
					$aDataSet['count'] = ( isset( $attributes['count'] ) ) ? (int)$attributes['count'] : 0;

					$vFacetBoxEditors->addData( $aDataSet );
					$allEditorsAvailableParamsOnly[] = 'ed[]='.$editor;
				}

				$allEditorsAvailableParamsOnly = implode( '&', $allEditorsAvailableParamsOnly );
				$vFacetBoxEditors->setOption( 'uri-facet-all', $this->oUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::TYPE ).'&'.$allEditorsAvailableParamsOnly );
				$vFacetBoxEditors->setOption( 'uri-facet-all-diff', $allEditorsAvailableParamsOnly );
			}
			// --------------- end facet editor
		}

		//---------- end facets

		//---------- begin results

		$oDocuments = $oHits->response->docs;

		$sImgPath = $wgScriptPath . '/extensions/BlueSpiceExtensions/ExtendedSearch/resources/images';

		$aImageLinks = array(
			'doc' => '<img src="' . $sImgPath . '/word.gif" alt="doc" /> ',
			'ppt' => '<img src="' . $sImgPath . '/ppt.gif" alt="ppt" /> ',
			'xls' => '<img src="' . $sImgPath . '/xls.gif" alt="xls" /> ',
			'pdf' => '<img src="' . $sImgPath . '/pdf.gif" alt="pdf" /> ',
			'txt' => '<img src="' . $sImgPath . '/txt.gif" alt="txt" /> ',
			'default' => '<img src="' . $sImgPath . '/page.gif" alt="page" /> '
		);

		foreach ( $oDocuments as $oDocument ) {
			//Show Page Title and link it
			$sLinkIcon = $aImageLinks['default'];

			if ( $oDocument->namespace == '999' ) {
				$iNamespace = NS_FILE;
			} else {
				$iNamespace = $oDocument->namespace;
			}
			$oTitle = Title::makeTitle( $iNamespace, $oDocument->title );

			// external files will never exist for mediawiki
			if ( $oDocument->namespace != '998' ) {
				if ( !$oTitle->exists() ) continue;
			}
			$oSkin = RequestContext::getMain()->getSkin();
			$sSearchLink = false;

			wfRunHooks( 'BSExtendedSearchFormatLink', array( &$sSearchLink, $oDocument, $oSkin, &$sLinkIcon ) );

			if ( !$sSearchLink ) {
				if ( $oDocument->type == 'wiki' ) {
					if ( !$oTitle->userCan( 'read' ) ) continue;

					$sHtml = null;
					if ( isset( $oHits->highlighting->{$oDocument->uid}->titleWord ) ) {
						$sHtml = $oHits->highlighting->{$oDocument->uid}->titleWord[0];
					} elseif(  isset( $oHits->highlighting->{$oDocument->uid}->titleReverse ) ) {
						$sHtml = $oHits->highlighting->{$oDocument->uid}->titleReverse[0];
					}

					if ( !is_null( $sHtml ) ) {
						if ( $oDocument->namespace != '0' && $oDocument->namespace != '998' && $oDocument->namespace != '999' ) {
							$sHtml = BsNamespaceHelper::getNamespaceName( $oDocument->namespace ). ':' . $sHtml;
						}
						$sHtml = str_replace ( '_', ' ', $sHtml );
					}

					$sSearchLink = BsLinkProvider::makeLink(
						$oTitle,
						$sHtml,
						$aCustomAttribs = array(
							'class' => 'bs-extendedsearch-result-headline'
						),
						$aQuery = array(),
						$aOptions = array( 'known' )
					);

					if ( isset( $oHits->highlighting->{$oDocument->uid}->sections ) ) {
						$oParser = new Parser();
						$sSection = strip_tags( $oHits->highlighting->{$oDocument->uid}->sections[0], '<em>' );
						$sSectionAnchor = $oParser->guessSectionNameFromWikiText( $sSection );
						$sSectionLink = BsLinkProvider::makeLink( $oTitle, $sSection, $aCustomAttribs = array(), $aQuery = array(), $aOptions = array( 'known' ) );

						$aMatches = array();
						preg_match( '#.*?href="(.*?)".*?#', $sSectionLink, $aMatches );
						if ( isset( $aMatches[1] ) ) {
							$sAnchor = $aMatches[1] . $sSectionAnchor;
							$sSectionLink = str_replace( $aMatches[1], $sAnchor, $sSectionLink );
						}
						$sSearchLink .= ' <span class="bs-extendedsearch-sectionresult">('. wfMessage( 'bs-extendedsearch-section' )->plain() . $sSectionLink . ')</span>';
					}
				} elseif ( $this->oContext->getUser()->isAllowed( 'searchfiles' ) ) {
					$sLinkIcon = ( isset( $aImageLinks[$oDocument->type] ) )
						? $aImageLinks[$oDocument->type]
						: $aImageLinks['default'];

					if ( $oDocument->overall_type == 'repo' ) {
						$sSearchLink = Linker::makeMediaLinkObj( $oTitle );
					} elseif ( $oDocument->overall_type == 'special-linked' ) {
						$si_title = $oDocument->title;
						$si_link  = $oDocument->path;

						$sLink = Linker::makeExternalLink( $si_link, $si_title, '' );

						$sSearchLink = str_replace( '<a', '<a target="_blank"', $sLink );
					} else {
						$si_title = $oDocument->title;
						$si_link  = $oDocument->path;

						$sSearchLink = '<a target="_blank" href="file:///' . $si_link . '">' . $si_title . '</a>';
					}
				} else {
					continue;
				}
			}

			$catstr = '';
			$iCats = 0;
			if ( isset( $oDocument->cat ) ) {
				if ( is_array( $oDocument->cat ) ) {
					$catlinks = array();
					$iItems = 0;
					foreach ( $oDocument->cat as $c ) {
						if ( $c == 'notcategorized' ) continue;
						$oCatTitle = Title::makeTitle( NS_CATEGORY, $c );
						$catstr = BsLinkProvider::makeLink( $oCatTitle, $oCatTitle->getText() );

						if ( $iItems === 3 ) {
							$catlinks[] = BsLinkProvider::makeLink( $oTitle, '...' );
							break;
						} else {
							$catlinks[] = $catstr;
						}

						$iItems++;
					}
					$catstr = implode( ', ', $catlinks );
					$iCats = count( $catlinks );
				} else {
					if ( $oDocument->cat != 'notcategorized' ) {
						$oCatTitle = Title::makeTitle( NS_CATEGORY, $oDocument->cat );
						$catstr = BsLinkProvider::makeLink( $oCatTitle, $oCatTitle->getText() );
					}
					$iCats = 1;
				}
			}

			// If text is empty no Notice will be thrown
			$aHighlightsnippets = null;
			if ( $this->oSearchOptions->getOption( 'scope' ) != 'title' ) {
				$oHighlightData = $oHits->highlighting->{$oDocument->uid};
				if ( isset( $oHighlightData->textWord ) ) {
					$aHighlightsnippets = $oHighlightData->textWord;
				} elseif ( isset( $oHighlightData->textReverse ) ) {
					$aHighlightsnippets = $oHighlightData->textReverse;
				}
			}

			$sRedirect = '';
			if ( isset( $oDocument->redirects ) ) {
				if ( is_array( $oDocument->redirects ) ) {
					$aRedirects = array();
					foreach ( $oDocument->redirects as $sRedirect ) {
						$oTitle = Title::newFromText( $sRedirect );
						$aRedirects[] = BsLinkProvider::makeLink( $oTitle );
					}
					$sRedirect = wfMessage( 'bs-extendedsearch-redirect', implode( ', ', $aRedirects ) )->plain();
				} else {
					$oTitle = Title::newFromText( $oDocument->redirects );
					$sRedirect = wfMessage( 'bs-extendedsearch-redirect', BsLinkProvider::makeLink( $oTitle ) )->plain();
				}
			}

			$sTimestamp = sprintf(
				'%s - %s',
				$this->oContext->getLanguage()->date( $oDocument->ts, true ),
				$this->oContext->getLanguage()->time( $oDocument->ts, true )
			);

			$aResultEntryDataSet = array(
				'searchicon' => $sLinkIcon,
				'searchlink' => $sSearchLink,
				'timestamp' => $sTimestamp,
				'catstr' => $catstr,
				'catno' => $iCats,
				'redirect' => $sRedirect,
				'highlightsnippets' => $aHighlightsnippets
			);

			$this->vSearchResult->addResultEntry( $aResultEntryDataSet );

		} // foreach $oDocuments as $oDocument

		// ----------end results ---------------

		return $this->vSearchResult;
	}

	/**
	 * Detects whether two keys of an array are the same.
	 * @param array $array1 First array
	 * @param array $array2 Second array
	 * @param string $key (optional) key to be compared.
	 * @return int Comparison: 0 if equal, else + or -1
	 */
	protected function compareEntries( $array1, $array2, $key = false ) {
		if ( $key ) {
			if ( !isset( $array1[$key] ) && !isset( $array2[$key] ) ) return 0;
			if ( isset( $array1[$key] ) && !isset( $array2[$key] ) ) return -1;
			if ( !isset( $array1[$key] ) && isset( $array2[$key] ) ) return 1;
			if ( is_int( $array1[$key] ) && is_int( $array2[$key] ) ) {
				return ( ( (int)$array2[$key] ) - ( (int)$array1[$key] ) );
			}
			return strcasecmp( (string)$array2[$key], (string)$array1[$key] );
		} else {
			foreach ( $this->sortorder as $sortkey => $asc ) {
				$res = $this->compareEntries( $array1, $array2, $sortkey );
				if ( $res !== 0 ) return $res * $asc;
			}
		}
		return 0;
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
		$res->setTemplate( '<div id="bs-es-searchterm-error">' . wfMessage( 'bs-extendedsearch-error' )->plain() . ': {message}</div>' );
		$res->addData( array( 'message' => wfMessage( $sMessage )->plain() ) );
		return $res;
	}

}