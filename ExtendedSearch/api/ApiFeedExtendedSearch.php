<?php

class ApiFeedExtendedSearch extends ApiBase {

	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
	}

	public function getCustomPrinter() {
		return new ApiFormatFeedWrapper( $this->getMain() );
	}

	public function execute() {
		$params = $this->extractRequestParams();

		//HINT: includes/api/ApiFeedContributions.php
		//HINT: includes/api/ApiFeedWatchlist.php
		global $wgSitename, $wgLanguageCode, $wgEnableOpenSearchSuggest,
				$wgSearchSuggestCacheExpiry, $wgFeed, $wgFeedClasses;

		if( !$wgFeed ) {
			$this->dieUsage( 'Syndication feeds are not available', 'feed-unavailable' );
		}

		if( !isset( $wgFeedClasses[ $params['feedformat'] ] ) ) {
			$this->dieUsage( 'Invalid subscription feed type', 'feed-invalid' );
		}

		$msg = wfMessage( 'specialextendedsearch' )->inContentLanguage()->text();
		$feedTitle = $wgSitename. ' - ' . $msg . ' [' .$wgLanguageCode. ']';
		$feedUrl = SpecialPage::getTitleFor( 'SpecialExtendedSearch' )->getFullURL();
		$feed = new $wgFeedClasses[$params['feedformat']]( $feedTitle, htmlspecialchars( $msg ), $feedUrl );

		$feedItems = array();
		try {

			$oSearchService = SearchService::getInstance();
			$oSearchRequest = new SearchRequest();
			$oSearchRequest->init();
			$oSearchOptions = new SearchOptions( $oSearchRequest, RequestContext::getMain() );
			$oSearchOptions->readInSearchRequest();

			// Prepare search input
			$sSearchString = $params['q'];
			$sSearchString = urldecode( $sSearchString );
			$sSearchString = ExtendedSearchBase::preprocessSearchInput( $sSearchString );
			$sSearchString = ExtendedSearchBase::sanitzeSearchString( $sSearchString );

			$oSearchOptions->setOption('searchString', $oSearchOptions);

			// Make solr query suitable for autocomplete
			$aSolrQuery = $oSearchOptions->getSolrQuery();

			$aSearchOptions = $aSolrQuery['searchOptions'];
			$aSearchOptions['facet']       = 'off';
			$aSearchOptions['hl']          = 'on';
			$aSearchOptions['hl.fl']       = 'textWord, textReverse';
			$aSearchOptions['hl.snippets'] = 3;

			// params are query, offset, limit, params
			$aHits = $oSearchService->search( $sSearchString, 0, 25, $aSearchOptions );

			foreach( $aHits->response->docs as $doc ) {
				if ( $doc->namespace != '999' ) {
					$oTitle = Title::makeTitle( $doc->namespace, $doc->title );
				} else {
					continue;
				}

				if ( !$oTitle->userCan( 'read' ) ) continue;

				$oHighlightData = $aHits->highlighting->{$doc->uid};
				if ( isset( $oHighlightData->textWord ) ) {
					$aHighlightsnippets = $oHighlightData->textWord;
				} else if ( isset( $oHighlightData->textReverse ) ) {
					$aHighlightsnippets = $oHighlightData->textReverse;
				}

				$sTextFragment = '';
				foreach ( $aHighlightsnippets as $sFrag ) {
					$sFrag = strip_tags( $sFrag, '<em>' );
					if ( empty( $sFrag ) ) continue;
					$sTextFragment .= "{$sFrag}<br />";
				}

				$feedItems[] = new FeedItem(
					$doc->title,
					$sTextFragment,
					$oTitle->getFullURL()
				);
			}
		}
		catch ( Exception $e ) {
			$this->dieUsage( $e->getMessage(), 'feed-invalid' );
		}

		ApiFormatFeedWrapper::setResult( $this->getResult(), $feed, $feedItems );
	}

	public function getAllowedParams() {
		global $wgFeedClasses;
		$feedFormatNames = array_keys( $wgFeedClasses );
		return array (
			'q' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'feedformat' => array(
				ApiBase::PARAM_DFLT => 'rss',
				ApiBase::PARAM_TYPE => $feedFormatNames
			),
			'user' => array(
				ApiBase::PARAM_TYPE => 'user',
			),
			'namespace' => array(
				ApiBase::PARAM_TYPE => 'namespace',
				ApiBase::PARAM_ISMULTI => true
			),
			'category' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_ISMULTI => true
			),
			'files' => array(
				ApiBase::PARAM_TYPE => 'boolean',
			),
		);
	}

	/**
	* @deprecated since MediaWiki core 1.25
	*/
	public function getExamples() {
		return array(
			'api.php?action=feedextendedsearch&q=Test&namespace=0|2'
		);
	}

	/**
	* @see ApiBase::getExamplesMessages()
	*/
	protected function getExamplesMessages() {
		return array(
			'api.php?action=feedextendedsearch&q=Test&namespace=0|2'
				=> 'apihelp-feedextendedsearch-example'
		);
	}

	public function getHelpUrls() {
		return 'http://help.blue-spice.org/index.php/ExtendedSearch';
	}
}
