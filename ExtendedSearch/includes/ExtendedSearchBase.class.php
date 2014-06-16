<?php
/**
 * Base class for ExtendedSearch for MediaWiki
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
	 * Given context.
	 * @var $oContext
	 */
	protected $oContext = null;
	/**
	 * Instance of current search service.
	 * @var $oSearchService
	 */
	protected $oSearchService = null;
	/**
	 * Reference to instance of SearchRequest.
	 * @var Object SearchRequest class.
	 */
	protected $oSearchRequest = null;
	/**
	 * Instance of current search options.
	 * @var $oSearchService
	 */
	protected $oSearchOptions = null;
	/**
	 * Reference to instance of SearchUriBuilder.
	 * @var Object SearchUriBuilder class.
	 */
	protected $oSearchUriBuilder = null;
	/**
	 * Reference to instance of SearchUriBuilder.
	 * @var Object SearchUriBuilder class.
	 */
	protected $oSearchIndex = null;
	/**
	 * Instance of ExtendedSearchBase
	 * @var Object
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
		$this->oSearchIndex = new SearchIndex( $this->oSearchRequest, $this->oSearchOptions, $this->oSearchUriBuilder, $this->oContext );
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
		// Form and results views are added via addItem to a ViewBaseElement
		$oSearchformView = new ViewBaseElement();
		$oSearchform = new ViewExtendedSearchFormPage();
		$aMonitor = array();

		if ( $this->oSearchOptions->getOptionBool( 'bExtendedForm' ) ) {
			$aMonitor['linkToExtendedPageMessageKey'] = 'bs-extendedsearch-specialpage-form-return-to-simple';
			$aMonitor['linkToExtendedPageUri'] = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL );

			$oSearchformView->addItem( $this->renderExtendedForm( $oSearchform ) );
			$bShowExtendedForm = true;
		} else {
			$aMonitor['linkToExtendedPageMessageKey'] = 'bs-extendedsearch-specialpage-form-expand-to-extended';
			$aMonitor['linkToExtendedPageUri'] = $this->oSearchUriBuilder->buildUri( SearchUriBuilder::ALL | SearchUriBuilder::EXTENDED );
		}

		$oSearchform->setOptions( $aMonitor );
		$oSearchformView->addItem( $oSearchform );

		$oView = new ViewBaseElement();
		$this->oContext->getOutPut()->addHTML( $oSearchformView->execute() );

		if ( isset( $bShowExtendedForm ) && $bShowExtendedForm == 'true' ) return;

		return $this->getResults();
	}

	public function getResults() {
		try {
			$oView = new ViewBaseElement();
			$aResponeMonitor = array();

			$oResultView = $this->search( $aResponeMonitor );

			$vNoOfResultsFound = new ViewNoOfResultsFound();
			$vNoOfResultsFound->setOptions( $aResponeMonitor );
			$oView->addItem( $vNoOfResultsFound );

			$oView->addItem( $oResultView );
		} catch ( BsException $e ) {
			if ( $e->getMessage() == 'redirect' ) return;
			throw $e;
		}
		return $oView;
	}

	/**
	 * Renders extended search options form.
	 * @param array $aMonitor List that contains form view.
	 * @return ViewBaseElement View that describes search options.
	 */
	public function renderExtendedForm( &$aMonitor ) {
		global $wgContLang;

		$aHiddenFieldsInForm = array();
		$aHiddenFieldsInForm['search_asc'] = $this->oSearchOptions->getOption( 'asc' );
		$aHiddenFieldsInForm['search_order'] = $this->oSearchOptions->getOption( 'order' );// score|titleSort|type|ts
		$aHiddenFieldsInForm['search_submit'] = '1';

		$aMonitor->setOptions(
			array(
				'hiddenFields' => $aHiddenFieldsInForm,
				'files' => $this->oSearchOptions->getOption( 'files' ),
				'method' => BsConfig::get( 'MW::ExtendedSearch::FormMethod' ),
				'scope' => $this->oSearchOptions->getOption( 'scope' )
			)
		);

		$vOptionsFormWiki = $aMonitor->getOptionsForm( 'wiki', wfMessage( 'bs-extendedsearch-search-wiki' )->plain() );
		$vNamespaceBox = $vOptionsFormWiki->getBox( 'NAMESPACE-FIELD', 'bs-extendedsearch-search-namespace', 'na[]' );
		$aMwNamespaces = $wgContLang->getNamespaces();
		$aSelectedNamespaces = $this->oSearchOptions->getOption( 'namespaces' );

		if ( BsConfig::get( 'MW::SortAlph' ) ) asort( $aMwNamespaces );

		foreach ( $aMwNamespaces as $namespace ) {
			$iNsIndex = $wgContLang->getNsIndex( $namespace );
			if ( $iNsIndex < 0 ) continue;
			if ( $iNsIndex == 0 ) $namespace = wfMessage( 'bs-extendedsearch-articles' )->plain();
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

		$dbr = wfGetDB( DB_SLAVE ); // needed for categories and editors

		$catRes = $dbr->select(
				array( 'category' ),
				array( 'cat_id', 'cat_title' ),
				'',
				null,
				array( 'ORDER BY' => 'cat_title asc' )
		);
		if ( $dbr->numRows( $catRes ) != 0 ) {
			$vCategoryBox = $vOptionsFormWiki->getBox( 'CATEGORY-FIELD', 'bs-extendedsearch-search-category', 'ca[]' );
			$aSelectedCategories = $this->oSearchOptions->getOption( 'cats' );
			while ( $catRow = $dbr->fetchObject( $catRes ) ) {
				$vCategoryBox->addEntry(
					$catRow->cat_title,
					array(
						'value' => $catRow->cat_title,
						'text' => $catRow->cat_title,
						'selected' => in_array( $catRow->cat_title, $aSelectedCategories )
					)
				);
			}
		}

		$dbr->freeResult( $catRes );

		$vEditorsBox = $vOptionsFormWiki->getBox( 'EDITORS-FIELD', 'bs-extendedsearch-search-editors', 'ed[]' );
		$edRes = $dbr->select(
			array( 'revision' ),
			array( 'DISTINCT rev_user_text' ),
			'',
			null,
			array( 'ORDER BY' => 'rev_user_text' )
		);
		$aSelectedEditors = $this->oSearchOptions->getOption( 'editor' );
		while ( $edRow = $dbr->fetchObject( $edRes ) ) {
			$oUser = User::newFromName( $edRow->rev_user_text );
			if ( !is_object( $oUser ) ) continue;

			$vEditorsBox->addEntry(
				$oUser->getName(),
				array(
					'value' => $oUser->getName(),
					'text' => $oUser->getName(),
					'selected' => in_array( $oUser->getName(), $aSelectedEditors )
				)
			);
		}

		$dbr->freeResult( $edRes );

		$vbe = new ViewBaseElement();
		$vbe->setAutoElement( false );
		return $vbe;
	}

	/**
	 * Starts a search for a given search request.
	 * @param array $aMonitor Set of options.
	 * @return ViewBaseElement View for search results.
	 */
	public function search( &$aMonitor ) {
		try {
			$vItem = $this->oSearchIndex->search( $this->oSearchService, $aMonitor );
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
		$sSearchString = str_ireplace( array( ' and ', ' or ', ' not ' ), array( ' AND ', ' OR ', ' NOT ' ), ' '.$sSearchString.' ' );

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
	 * Starts a search for Autocomplete
	 * @param String $sSearchString The string to be searched for.
	 * @return String JSON of search results.
	 */
	public static function getAutocompleteData( $sSearchString ) {
		if ( self::isCurlActivated() === false ) return '';

		$oSerachService = SearchService::getInstance();
		$oSearchRequest = new SearchRequest();
		$oSearchOptions = new SearchOptions( $oSearchRequest, RequestContext::getMain() );

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

		$bEscalateToFuzzy = ( $oHits->response->numFound == 0 ); // boolean!
		// Escalate to fuzzy
		if ( $bEscalateToFuzzy ) {
			$this->oSearchOptions->setOption( 'scope', 'title' );

			$aFuzzyQuery = $this->oSearchOptions->getSolrFuzzyQuery( $sSolrSearchString );
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

		$aResults = array();
		$iID = 0;

		if ( !empty( $oDocuments ) ) {
			$oTitle = null;
			$sLabelText = '';

			foreach ( $oDocuments as $oDoc ) {
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
				if ( $aQuery['namespace'] !== false ) {
					$sNamespace = BsNamespaceHelper::getNamespaceName( $aQuery['namespace'] );
					$sLabelText = str_replace( $sNamespace.':', '', $sLabelText );
				}

				$oItem = new stdClass();
				$oItem->id = ++$iID;
				$oItem->value = $oTitle->getPrefixedText();
				$oItem->label = $sLabelText;
				$oItem->type = $oDoc->type;
				$oItem->link = $oTitle->getFullURL();
				$oItem->attr = '';

				$aResults[] = $oItem;
			}
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

		$bTitleExists = $oSearchOptions->titleExists( $sSearchString );

		$sEcpSearchString = self::sanitzeSearchString( $sSearchString );

		wfRunHooks( 'BSExtendedSearchAutocomplete', array( &$aResults, $sSearchString, &$iID, $bTitleExists, $sEcpSearchString ) );

		$aLinkParams = array(
			'search_origin' => 'titlebar',
			'search_scope' => 'text',
			'search_input' => $sEcpSearchString,
			'search_files' => $iSearchfiles,
			'autocomplete' => true
		);

		$oItem = new stdClass();
		$oItem->id = ++$iID;
		$oItem->value = $sEcpSearchString;
		$oItem->label = $sLabel;
		$oItem->type = '';
		$oItem->link = SpecialPage::getTitleFor( 'SpecialExtendedSearch' )->getFullUrl( $aLinkParams );
		$oItem->attr = 'bs-extendedsearch-ac-noresults';

		$aResults[] = $oItem;

		return json_encode( $aResults );
	}

	/**
	 * Highlights title for a given search string
	 * @param object $oTitle Title which should be highlighted
	 * @param string $sSearchString search string
	 * @return string highlighted title
	 */
	public static function highlightTitle( $oTitle, $sSearchString ) {
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

		$aMltQuery = $this->oSearchOptions->getSolrMltQuery( $oTitle );
		try {
			$oResults = $this->oSearchService->mlt(
				$aMltQuery['searchString'],
				$aMltQuery['offset'],
				$aMltQuery['searchLimit'],
				$aMltQuery['searchOptions']
			);
		} catch ( Exception $e ) {
			return $oViewMlt;
		}

		$aMlt = array();
		//$aMlt[] = implode( ', ', $oResults->interestingTerms );
		if ( !empty( $oResults->response->docs ) ) {
			foreach ( $oResults->response->docs as $oRes ) {
				if ( count( $aMlt )  === 5 ) break;

				if ( $oRes->namespace != 999 ) {
					$oMltTitle = Title::makeTitle( $oRes->namespace, $oRes->title );
				} else {
					$oMltTitle = Title::makeTitle( NS_FILE, $oRes->title );
				}

				if ( !$oMltTitle->userCan( 'read' ) ) continue;
				if ( $oMltTitle->getArticleID() === $oTitle->getArticleID() ) continue;
				if ( $oMltTitle->isRedirect() ) continue;

				$sHtml = $oMltTitle->getPrefixedText();
				$aMlt[] = BsLinkProvider::makeLink( $oMltTitle, $sHtml );
			}
		}

		if ( empty( $aMlt ) ) {
			$aMlt[] = wfMessage( 'bs-extendedsearch-no-mlt-found' )->plain();
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

}