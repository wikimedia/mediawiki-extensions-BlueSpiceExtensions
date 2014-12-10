<?php
/**
 * Buildes URIs for ExtendedSearch for MediaWiki
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

/**
 * BsSearchResult class
 */
class BsSearchResult {

	/**
	 * Instance of apache solr response
	 * @var object of apache solr response
	 */
	protected $oResponse = null;
	/**
	 * Additional data
	 * @var array of additional data
	 */
	protected $aData = array();
	/**
	 * RequestContext
	 * @var RequestContext object of RequestContext
	 */
	protected $oContext;
	/**
	 * View that renders search results
	 * @var ViewSearchResult ViewSearchResult object
	 */
	protected $vSearchResult = null;
	/**
	 * Instance of SearchOptions
	 * @var SearchOptions object of SearchOptions
	 */
	protected $oSearchOptions = null;
	/**
	 * Instance of SearchUriBuilder
	 * @var object SearchUriBuilder object
	 */
	protected $oSearchUriBuilder = null;
	/**
	 * Maximum character length of facets
	 * @var int Numberof characters
	 */
	protected $iMaxFacetLength = 20;

	/**
	 * Constructor for SearchResult class
	 * @param object $oContext RequestContext
	 * @param object $oSearchOptions SearchOptions
	 * @param object $oSearchUriBuilder SearchUriBuilder
	 * @param object $oResponse solr result set
	 */
	public function __construct( $oContext, $oSearchOptions, $oSearchUriBuilder, $oResponse ) {
		$this->oContext = $oContext;
		$this->oSearchOptions = $oSearchOptions;
		$this->oSearchUriBuilder = $oSearchUriBuilder;
		$this->oResponse = $oResponse;
	}

	/**
	 * Setter for result specific data
	 */
	public function setData( $sKey, $vValue ) {
		$this->aData[$sKey] = $vValue;
	}

	/**
	 * Getter for result specific data
	 * @return mixed Specific data for a given key
	 */
	public function getData( $sKey ) {
		return $this->aData[$sKey];
	}

	/**
	 * Creates a search result view
	 * @param array $aMonitor reference of Monitor array from special page
	 * @param int $iResults number of results
	 * @param bool $bFuzzy whether it is a fuzzy query or not
	 * @param bool $bFacet whether facets should be added or not
	 * @return object search result view
	 */
	public function createSearchResult( &$aMonitor, $iResults, $bFuzzy, $bFacet ) {
		$this->vSearchResult = new ViewSearchResult();

		if ( $bFuzzy && isset( $this->aData['spell'] ) ) {
			$this->vSearchResult->addSpell( $this->aData['spell'] );
		}

		if ( BsConfig::get( 'MW::ExtendedSearch::ShowCreateSugg' ) ) {
			if ( $iResults == 0 || $bFuzzy || !$this->oSearchOptions->getOption( 'titleExists' ) ) {
				$this->vSearchResult->addSuggest(
					array( 'search' => $this->oSearchOptions->getOption( 'searchStringRaw' ) )
				);
			}
		}

		$aMonitor['NoOfResultsFound'] = $iResults;
		$aMonitor['SearchTerm'] = $this->oSearchOptions->getOption( 'searchStringRaw' );
		$aMonitor['EscalatedToFuzzy'] = $bFuzzy;

		if ( $iResults == 0 ) return $this->vSearchResult;

		$this->createNavigation( $iResults );

		if ( $bFacet ) {
			$this->vSearchResult->setOption( 'showfacets' , $bFacet );
			$this->createFacets();
		}

		$this->createResults();

		return $this->vSearchResult;
	}

	/**
	 * Creates the navigation
	 * @param int $iResults number of results found
	 */
	private function createNavigation( $iResults ) {
		$aPaging = $this->getPaging( $iResults );
		$aSorting = $this->getSorting();

		$this->vSearchResult->setOption( 'pages', $aPaging );
		$this->vSearchResult->setOption( 'numFound', $iResults );
		$this->vSearchResult->setOption( 'sorting', $aSorting );
	}

	/**
	 * Creates the facets
	 */
	private function createFacets() {
		$sSiteUri = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::MLT );

		// possible orders: count, name, checked
		// possible directions: 1 = desc, -1 = asc
		$aOrder = ( BsConfig::get( 'MW::SortAlph' ) )
			? array( 'name' => -1 )
			: array( 'count' => 1 );
		$this->setData( 'sortorder' , $aOrder );

		$aBaseFacets = array(
			'namespace' => array(
				'param' => 'na',
				'option' => 'namespaces',
				'i18n' => 'bs-extendedsearch-facet-namespace',
				'url' => $this->oSearchUriBuilder->buildUri(
					SearchUriBuilder::ALL,
					SearchUriBuilder::NAMESPACES|SearchUriBuilder::FILES
				)
			),
			'cat' => array(
				'param' => 'ca',
				'option' => 'cats',
				'i18n' => 'bs-extendedsearch-facet-category',
				'url' => $this->oSearchUriBuilder->buildUri(
					SearchUriBuilder::ALL,
					SearchUriBuilder::CATS
				)
			),
			'editor' => array(
				'param' => 'ed',
				'option' => 'editor',
				'i18n' => 'bs-extendedsearch-facet-editors',
				'url' => $this->oSearchUriBuilder->buildUri(
					SearchUriBuilder::ALL,
					SearchUriBuilder::NAMESPACES|SearchUriBuilder::EDITOR
				)
			),
			'type' => array(
				'param' => 'ty',
				'option' => 'type',
				'i18n' => 'bs-extendedsearch-facet-type',
				'url' => $this->oSearchUriBuilder->buildUri(
					SearchUriBuilder::ALL,
					SearchUriBuilder::NAMESPACES|SearchUriBuilder::TYPE
				)
			)
		);

		wfRunHooks( 'BSExtendedSearchBeforeCreateFacets', array( &$aBaseFacets ) );

		foreach ( $aBaseFacets as $sFacet => $aConfig ) {
			$oFacet = new ViewSearchFacet();

			if ( !is_null( $this->oResponse->facet_counts->facet_fields->{$sFacet} ) ) {
				$oFacet->setOption( 'title', $aConfig['i18n'] );

				/* alters to:
				 * array(
				 *     0 => array( 'checked' => true ),
				 *     1 => array( 'count' => 15 ),
				 *     999 => array( 'checked' => true, 'count' => 2 )
				 * )*/
				$aFacets = array();
				$aData = $this->oSearchOptions->getOption( $aConfig['option'] );

				if ( !empty( $aData ) ) {
					foreach ( $aData as $key => $value ) {
						$aFacets[$value]['checked'] = true;
					}
					unset( $aData );
				}

				// Get all available facets
				$aFacetsInRespsonse = $this->oResponse->facet_counts->facet_fields->{$sFacet};
				foreach ( $aFacetsInRespsonse as $key => $count ) {
					if ( $key == '_empty_' ) continue;
					if ( $sFacet === 'namespace' ) {
						if ( BsNamespaceHelper::checkNamespacePermission( $key, 'read' ) === false || $count == '0' ) {
							unset( $aFacets[$key] );
							continue;
						}
					} elseif ( $sFacet === 'type' ) {
						if ( $key != 'wiki'
							&& !$this->oContext->getUser()->isAllowed( 'searchfiles' ) ) {
							continue;
						}
					}
					$aFacets[$key]['count'] = $count;
				}

				// Prepare available facets. Add some information for each facet
				foreach ( $aFacets as $key => $attributes ) {
					if ( !isset( $aFacets[$key]['count'] ) ) {
						unset( $aFacets[$key] );
						continue;
					}

					if ( $sFacet === 'namespace' ) {
						if ( $key == '999' ) {
							$sTitle = wfMessage( 'bs-extendedsearch-facet-namespace-files' )->plain();
						} elseif ( $key == '998' ) {
							$sTitle = wfMessage( 'bs-extendedsearch-facet-namespace-extfiles' )->plain();
						} elseif ( $key == '0' ) {
							$sTitle = wfMessage( 'bs-ns_main' )->plain();
						} else {
							$sTitle = BsNamespaceHelper::getNamespaceName( $key, false );

							if ( empty( $sTitle ) ) {
								unset( $aFacets[$key] );
								continue;
							}
						}
					} elseif ( $sFacet === 'cat' ) {
						$sTitle = ( $key == 'notcategorized' )
							? wfMessage( 'bs-extendedsearch-facet-uncategorized' )->plain()
							: $key;
					} elseif ( $sFacet === 'editor' ) {
						$sTitle = ( $key === 'unknown' )
							? wfMessage( 'bs-extendedsearch-unknown' )->plain()
							: $key;
					} elseif (  $sFacet === 'type' ) {
						$sTitle = $key;
					}

					$aFacets[$key]['title'] = $this->getFacetTitle( $sTitle );
					$aFacets[$key]['name'] = $this->reduceMaxFacetLength( $sTitle );
				}

				uasort( $aFacets, array( $this, 'compareEntries' ) );

				$aFacetAll = array();
				foreach ( $aFacets as $key => $attributes ) {
					$aDataSet = array();
					if ( isset( $attributes['checked'] ) ) $aDataSet['checked'] = true;

					if ( $sFacet === 'namespace' ) {
						if ( $key == '999' ) {
							$uri = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::NAMESPACES | SearchUriBuilder::FILES );
							$uri .= '&search_files=' . ( isset( $attributes['checked'] ) ) ? '0' : '1';
						} else {
							$uri = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::NAMESPACES );
						}
					} elseif ( $sFacet === 'cat' ) {
						$uri = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::CATS );
					} elseif ( $sFacet === 'editor' ) {
						$uri = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::EDITOR );
					} elseif ( $sFacet === 'type' ) {
						$uri = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::TYPE );
					}

					foreach ( $aFacets as $namespaceUrl => $attributesUrl ) {
						$bOwnUrlAndNotAlreadyChecked = ( ( $key == $namespaceUrl ) && !isset( $attributesUrl['checked'] ) );
						$bOtherUrlAndAlreadyChecked = ( ( $key != $namespaceUrl ) && isset( $attributesUrl['checked'] ) );

						if ( $bOwnUrlAndNotAlreadyChecked || $bOtherUrlAndAlreadyChecked ) {
							$uri .= "&{$aConfig['param']}[]=".$namespaceUrl;
						}
					}

					$aDataSet['uri'] = $uri;
					$aDataSet['diff'] = "{$aConfig['param']}[]=".$key;
					$aDataSet['name'] = $attributes['name'];
					$aDataSet['title'] = $attributes['title'];
					$aDataSet['count'] = (int)$attributes['count'];

					$oFacet->setData( $aDataSet );
					$aFacetAll[] = "{$aConfig['param']}[]=".$key;
				}

				if ( $sFacet === 'namespace' ) {
					$aReqNs = $this->oSearchOptions->getOption( 'namespaces' );
					foreach ( $aReqNs as $ikey => $value ) {
						if ( !array_key_exists( $value, $aFacets ) ){
							$aFacetAll[] = "{$aConfig['param']}[]=".$value;
							$sSiteUri = str_replace( "&{$aConfig['param']}[]=$value", '', $sSiteUri );
						}
					}
				}

				$sFacetAll = implode( '&', $aFacetAll );
				$oFacet->setOption( 'uri-facet-all-diff', $sFacetAll );
			}

			$this->vSearchResult->setFacet( $oFacet );
		}

		$this->vSearchResult->setOption( 'siteUri', $sSiteUri );
	}

	/**
	 * Creates the results
	 */
	private function createResults() {
		$oParser = new Parser();

		foreach ( $this->oResponse->response->docs as $oDocument ) {
			$iNamespace = ( $oDocument->namespace == '999' )
				? NS_FILE
				: $oDocument->namespace;

			$oTitle = Title::makeTitle( $iNamespace, $oDocument->title );

			// external files will never exist for mediawiki
			if ( $oDocument->namespace != '998' ) {
				if ( !$oTitle->exists() ) continue;
			}

			$oSkin = $this->oContext->getSkin();
			$sSearchLink = '';
			$sIconPath = '';
			wfRunHooks( 'BSExtendedSearchFormatLink', array( &$sSearchLink, $oDocument, $oSkin, &$sIconPath ) );

			if ( empty( $sSearchLink ) ) {
				if ( $oDocument->type == 'wiki' ) {
					if ( !$oTitle->userCan( 'read' ) ) continue;
					$sSearchLink = $this->getWikiLink( $oDocument, $oTitle, $oParser );
					$sIcon = 'default';

				} elseif ( $this->oContext->getUser()->isAllowed( 'searchfiles' ) ) {
					$sSearchLink = $this->getFileLink( $oDocument, $oTitle );
					$sIcon = $oDocument->type;
				} else {
					continue;
				}
			}

			$iCats = 0;
			$sCats = '';
			if ( isset( $oDocument->cat ) ) {
				$sCats = $this->getCategories( $oDocument, $oTitle, $iCats );
			}

			// If text is empty no Notice will be thrown
			$aHighlightSnippets = '';
			if ( $this->oSearchOptions->getOption( 'scope' ) != 'title' ) {
				$aHighlightSnippets = $this->getHighlightSnippets( $oDocument );
			}

			$sRedirect = '';
			if ( isset( $oDocument->redirects ) ) {
				$sRedirect = $this->getRedirects( $oDocument );
			}

			$sTimestamp = sprintf(
				'%s - %s',
				$this->oContext->getLanguage()->date( $oDocument->ts, true ),
				$this->oContext->getLanguage()->time( $oDocument->ts, true )
			);

			$aResultEntryDataSet = array(
				'iconpath' => $sIconPath,
				'searchicon' => $sIcon,
				'searchlink' => $sSearchLink,
				'timestamp' => $sTimestamp,
				'catstr' => $sCats,
				'catno' => $iCats,
				'redirect' => $sRedirect,
				'highlightsnippets' => $aHighlightSnippets
			);

			$this->vSearchResult->setResultEntry( $aResultEntryDataSet );
		}
	}

	/**
	 * Generates paging links
	 * @param integer $iResults Number of Results
	 * @return array Paging links
	 */
	private function getPaging( $iResults ) {
		$aPaging = array();
		$sSearchLimit = $this->oSearchOptions->getOption( 'searchLimit' );

		$loopsCalculated = ( $iResults / $sSearchLimit ) + 1;
		$this->vSearchResult->setOption( 'activePage', 1 );
		$sUrl = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL, SearchUriBuilder::OFFSET );

		for ( $i = 1; $i < $loopsCalculated; $i++ ) {
			$sOffset = ( ( $i - 1 ) * $sSearchLimit );

			if ( $sOffset == $this->oSearchOptions->getOption( 'offset' ) ) {
				$this->vSearchResult->setOption( 'activePage', $i );
			}

			$aPaging[$i] = "{$sUrl}&search_offset={$sOffset}";
		}
		return $aPaging;
	}

	/**
	 * Generates sorting data
	 * @return array Sorting data
	 */
	private function getSorting() {
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
			'sorturl' => $this->oSearchUriBuilder->buildUri(
					SearchUriBuilder::ALL,
					SearchUriBuilder::MLT|SearchUriBuilder::ORDER_ASC_OFFSET
				)
			);
		return $aSorting;
	}

	/**
	 * Generate a link to a wiki page for a given result
	 * @param object $oDocument Apache_Solr_Document
	 * @param object $oTitle Title of wiki page
	 * @param object $oParser Parser
	 * @return string Anchor link to the wiki page
	 */
	private function getWikiLink( $oDocument, $oTitle, $oParser ) {
		$sHtml = null;
		if ( isset( $this->oResponse->highlighting->{$oDocument->uid}->titleWord ) ) {
			$sHtml = $this->oResponse->highlighting->{$oDocument->uid}->titleWord[0];
		} elseif( isset( $this->oResponse->highlighting->{$oDocument->uid}->titleReverse ) ) {
			$sHtml = $this->oResponse->highlighting->{$oDocument->uid}->titleReverse[0];
		}

		if ( !is_null( $sHtml ) ) {
			if ( $oDocument->namespace != '0' && $oDocument->namespace != '998' && $oDocument->namespace != '999' ) {
				$sHtml = BsNamespaceHelper::getNamespaceName( $oDocument->namespace ). ':' . $sHtml;
			}
			$sHtml = str_replace ( '_', ' ', $sHtml );
		}

		$sSearchLink = Linker::link(
			$oTitle,
			$sHtml,
			$aCustomAttribs = array(
				'class' => 'bs-extendedsearch-result-headline'
			),
			array(),
			array( 'known' )
		);

		if ( isset( $this->oResponse->highlighting->{$oDocument->uid}->sections ) ) {
			$sSection = strip_tags( $this->oResponse->highlighting->{$oDocument->uid}->sections[0], '<em>' );
			$sSectionAnchor = $oParser->guessSectionNameFromWikiText( $sSection );
			$sSectionLink = BsLinkProvider::makeLink( $oTitle, $sSection, array(), array(), array( 'known' ) );

			$aMatches = array();
			preg_match( '#.*?href="(.*?)".*?#', $sSectionLink, $aMatches );

			if ( isset( $aMatches[1] ) ) {
				$sAnchor = $aMatches[1] . $sSectionAnchor;
				$sSectionLink = str_replace( $aMatches[1], $sAnchor, $sSectionLink );
			}

			$sSearchLink .= ' <span class="bs-extendedsearch-sectionresult">('.
				wfMessage( 'bs-extendedsearch-section' )->plain() . ' ' . $sSectionLink . ')</span>';
		}
		return $sSearchLink;
	}

	/**
	 * Generates a media, external or file link for a given result
	 * @param object $oDocument Apache_Solr_Document
	 * @param object $oTitle Title of wiki page
	 * @return string Anchor media, external or file link
	 */
	private function getFileLink( $oDocument, $oTitle ) {
		if ( $oDocument->overall_type == 'repo' ) {
			$sSearchLink = Linker::makeMediaLinkObj( $oTitle );
		} elseif ( $oDocument->overall_type == 'special-linked' ) {
			$sTitle = $oDocument->title;
			$sLink = $oDocument->path;

			$sSearchLink = Linker::makeExternalLink( $sLink, $sTitle, '' );
			$sSearchLink = str_replace( '<a', '<a target="_blank"', $sSearchLink );
		} else {
			$sTitle = $oDocument->title;
			$sLink = $oDocument->path;

			$sSearchLink = '<a target="_blank" href="file:///' . $sLink . '">' . $sTitle . '</a>';
		}
		return $sSearchLink;
	}

	/**
	 * Generates categories for a given result
	 * @param object $oDocument Apache_Solr_Document
	 * @param object $oTitle Title of wiki page
	 * @return string Anchor media, external or file link
	 */
	private function getCategories( $oDocument, $oTitle, &$iCats ) {
		$sCategories = '';
		if ( is_array( $oDocument->cat ) ) {
			$aCatLinks = array();
			$iItems = 0;

			foreach ( $oDocument->cat as $c ) {
				if ( $c == 'notcategorized' ) continue;
				$oCatTitle = Title::makeTitle( NS_CATEGORY, $c );
				$sCategories = Linker::link( $oCatTitle, $oCatTitle->getText() );

				if ( $iItems === 3 ) {
					$aCatLinks[] = Linker::link( $oTitle, '...' );
					break;
				} else {
					$aCatLinks[] = $sCategories;
				}

				$iItems++;
			}

			$sCategories = implode( ', ', $aCatLinks );
			$iCats = count( $aCatLinks );
		} else {
			if ( $oDocument->cat != 'notcategorized' ) {
				$oCatTitle = Title::makeTitle( NS_CATEGORY, $oDocument->cat );
				$sCategories = Linker::link( $oCatTitle, $oCatTitle->getText() );
			}
			$iCats = 1;
		}
		return $sCategories;
	}

	/**
	 * Generates highlight snippets for a given result
	 * @param object $oDocument Apache_Solr_Document
	 * @return array|string Highlight snippets array or empty string
	 */
	private function getHighlightSnippets( $oDocument ) {
		$oHighlightData = $this->oResponse->highlighting->{$oDocument->uid};
		$aHighlightSnippets = '';
		if ( isset( $oHighlightData->textWord ) ) {
			$aHighlightSnippets = $oHighlightData->textWord;
		} elseif ( isset( $oHighlightData->textReverse ) ) {
			$aHighlightSnippets = $oHighlightData->textReverse;
		}
		return $aHighlightSnippets;
	}

	/**
	 * Generates redirect links for a given result
	 * @param object $oDocument Apache_Solr_Document
	 * @return string Redirect links
	 */
	private function getRedirects( $oDocument ) {
		if ( is_array( $oDocument->redirects ) ) {
			$aRedirects = array();
			foreach ( $oDocument->redirects as $sRedirect ) {
				$oTitle = Title::newFromText( $sRedirect );
				$aRedirects[] = Linker::link( $oTitle );
			}
			$sRedirect = wfMessage( 'bs-extendedsearch-redirect', implode( ', ', $aRedirects ) )->plain();
		} else {
			$oTitle = Title::newFromText( $oDocument->redirects );
			$sRedirect = wfMessage( 'bs-extendedsearch-redirect', Linker::link( $oTitle ) )->plain();
		}
		return $sRedirect;
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
			$aOrder = $this->getData( 'sortorder' );
			foreach ( $aOrder as $sortkey => $asc ) {
				$res = $this->compareEntries( $array1, $array2, $sortkey );
				if ( $res !== 0 ) return $res * $asc;
			}
		}
		return 0;
	}

	/**
	 * Shorten facet string.
	 * @param string $sFacet Name of facet
	 * @return string Shortened name of facet
	 */
	private function reduceMaxFacetLength( $sFacet ) {
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
	private function getFacetTitle( $sFacet ) {
		$sFacet = str_replace( '_', ' ', $sFacet );
		$sTitle = '';
		if ( mb_strlen( $sFacet ) >= $this->iMaxFacetLength ) {
			$sTitle = $sFacet;
		}

		return $sTitle;
	}

}