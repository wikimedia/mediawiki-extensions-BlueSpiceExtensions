<?php
/**
 * Renders the ExtendedSearch results page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/**
 * <div style:"display: none"> mit Hilfen
 */
/**
 * This view renders the ExtendedSearch results page.
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ViewSearchResult extends ViewBaseElement {

	/**
	 * Contains the page output.
	 * @var string HTML output.
	 */
	protected $sOut = '';
	/**
	 * Contain view for search entry.
	 * @var ViewExtendedSearchResultEntry View for search entry.
	 */
	protected $aResultEntryView = array();
	/**
	 * List of facet boxes.
	 * @var array List of ViewExtendedSearchFacetBox.
	 */
	protected $aFacetBoxes = array();

	/**
	 * Adds data to the current result entry view.
	 * @param array $aDataSet List of result items.
	 */
	public function setResultEntry( $aDataSet ) {
		$vResultEntry = new ViewExtendedSearchResultEntry();
		$vResultEntry->setOptions( $aDataSet );
		$this->aResultEntryView[] = $vResultEntry->execute();
	}

	/**
	 * Adds a ceate/suggest section.
	 * @param array $aOptions Parameters for this section.
	 */
	public function addSuggest( $aOptions ) {
		$vSuggest = new ViewSearchSuggest();
		$vSuggest->setOptions( $aOptions );
		$this->addItem( $vSuggest );
	}

	/**
	 * Adds a ceate/suggest section.
	 * @param array $aOptions Parameters for this section.
	 */
	public function addSpell( $aOptions ) {
		$vSpell = new ViewSpell();
		$vSpell->setOptions( $aOptions );
		$this->addItem( $vSpell );
	}

	/**
	 * Adds additional output to page.
	 * @param string $aOutputToAdd HTML that shall be displayed.
	 */
	public function addOutput( $aOutputToAdd ) {
		$this->out .= $aOutputToAdd;
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $param = false ) {
		$aOut = array();
		$aOut[] = parent::execute();

		$aOut[] = $this->sOut;
		if ( !empty( $this->aResultEntryView ) ) {
			$sResults = implode( "\n", $this->aResultEntryView );
			$aOut[] = '<div id="bs-extendedsearch-spinner"></div>';
			$aOut[] = '<hr /><br />';
			if ( $this->getOption( 'siteUri' ) ) {
				$aOut[] = Xml::element(
					'div',
					array(
						'id' => 'bs-extendedsearch-siteuri',
						'siteuri' => $this->getOption( 'siteUri' )
					),
					'',
					false
				);
			}
			$aOut[] = '<div id="bs-extendedsearch-filters-results-paging">';
			if ( $this->getOption( 'showfacets' ) ) {

				$sFilterBoxes = '';
				foreach ( $this->aFacetBoxes as $box ) {
					$sFilterBoxes .= $box->execute();
				}
				$sFilterBoxes = Xml::openElement( 'div', array( 'id' => 'bs-extendedsearch-all-filter-boxes' ) ).
								$sFilterBoxes.
								Xml::closeElement( 'div' );
				$aOut[] = Xml::openElement( 'div', array( 'id' => 'bs-extendedsearch-filters' ) ).
						$sFilterBoxes.
						Xml::closeElement( 'div' );
			}

			$cachePaging = $this->getPaging();
			$upperPaging = Xml::openElement(
					'div',
					array(
						'class' => 'bs-extendedsearch-default-line bs-extendedsearch-paging',
						'id' => 'bs-extendedsearch-paging-upper'
					)
			);
			$upperPaging .= $cachePaging;
			$upperPaging .= Xml::closeElement( 'div' );

			$lowerPaging = Xml::openElement(
					'div',
					array(
						'class' => 'bs-extendedsearch-default-line bs-extendedsearch-paging',
						'id' => 'bs-extendedsearch-paging-lower'
					)
			);
			$lowerPaging .= $cachePaging;
			$lowerPaging .= Xml::closeElement( 'div' );

			$sortingBar = Xml::openElement(
					'div',
					array( 'class' => 'bs-extendedsearch-sorting-bar bs-extendedsearch-default-textspacing' )
			);
			$sortingBar .= $this->getSortingBar();
			$sortingBar .= Xml::closeElement( 'div' );

			$results = Xml::openElement(
					'div',
					array(
						'id'=>'bs-extendedsearch-results'
					)
				);
			$results .= $sResults;
			$results .= Xml::closeElement( 'div' );

			$aOut[] = ( $this->getOption( 'showfacets' ) )
					? '<div id="bs-extendedsearch-results-paging" class="bs-extendedsearch-results-with-facets">'
					: '<div id="bs-extendedsearch-results-paging">';
			$aOut[] = $upperPaging.$sortingBar.$results.$lowerPaging;
			$aOut[] = '  </div>'; // bs-extendedsearch-results-paging
			$aOut[] = '</div>'; // bs-extendedsearch-filters-results-paging
			$aOut[] = '<div id="bs-extendedsearch-results-finalizer"></div>';
			$aOut[] = '<br /><br />';
		}
		// Placeholder for SearchUsers, SearchProfiles, SearchHelpdesk etc
		$sOut = implode( "\n", $aOut );

		return $sOut;
	}

	/**
	 * Displays a single paging box, either with page number or with prev/next arrows.
	 * @param string $pageNo Label of the page. Number or prev/next arrows.
	 * @param string $url Link to be followed when clicked.
	 * @param bool $bActive Is this box currently selected?
	 * @param bool $arrows Is it an arrow box?
	 * @return string HTML of paging box.
	 */
	protected function makePagingDiv( $pageNo, $url = '', $bActive = false, $arrows = false ) {
		$aStyleClasses = array(
			'bs-extendedsearch-paging-no'
		);
		if ( $arrows ) $aStyleClasses[] = 'bs-extendedsearch-paging-arrows';
		if ( $bActive ) $aStyleClasses[] = 'bs-extendedsearch-paging-no-active';

		$aOut = $pageNo;
		if ( $bActive ) $aOut = "<b>{$aOut}</b>";

		$aOut = '<div class="'.implode( ' ', $aStyleClasses ).'">'.$aOut.'</div>';

		if ( !$bActive && !empty( $url ) ) {
			$aOut = Html::openElement( 'a', array( 'href' => $url ) ) . $aOut . Html::closeElement( 'a' );
		}

		return $aOut;
	}

	/**
	 * Renders the paging bar.
	 * @return string HTML of paging bar.
	 */
	protected function getPaging() {
		$aOut = array();
		$aPaging = $this->getOption( 'pages' );
		$pageActive = (int)$this->getOption( 'activePage' );
		$firstPageToDisplay = ( $pageActive > 5 ) ? $pageActive - 5 : 1;
		end( $aPaging );
		$lastPage = key( $aPaging );
		$lastPageToDisplay = ( $lastPage - $pageActive > 5 ) ? $pageActive + 5 : $lastPage;

		$aOut[] = ( $pageActive > 1 )
			? $this->makePagingDiv( htmlspecialchars( '<' ), $aPaging[$pageActive - 1], false, true )
			: $this->makePagingDiv( htmlspecialchars( '<' ), '', false, true );

		if ( $firstPageToDisplay > 1 )
				$aOut[] = $this->makePagingDiv( 1, $aPaging[1] );

		if ( $firstPageToDisplay > 2 )
				$aOut[] = '<div class="bs-extendedsearch-paging-dots bs-extendedsearch-default-textspacing">...</div>';

		foreach ( $aPaging as $page => $url ) {
			if ( $firstPageToDisplay > $page ) continue;
			if ( $lastPageToDisplay < $page ) break;
			$aOut[] = $this->makePagingDiv( $page, $url, ( $page == $pageActive ) ); // (($page == $lastKeyInAPaging) || $page == 1)
		}

		if ( $lastPageToDisplay + 1 < $lastPage )
				$aOut[] = '<div class="bs-extendedsearch-paging-dots bs-extendedsearch-default-textspacing">...</div>';

		if ( $lastPageToDisplay < $lastPage )
				$aOut[] = $this->makePagingDiv( $lastPage, $aPaging[$lastPage] );

		$aOut[] = ( $pageActive < $lastPage )
				? $this->makePagingDiv( htmlspecialchars( '>' ), $aPaging[$pageActive + 1], false, true )
				: $this->makePagingDiv( htmlspecialchars( '>' ), '', false, true );

		$aOut = implode( '<div class="bs-extendedsearch-paging-spacer"></div>', $aOut );

		return $aOut;
	}

	/**
	 * Renders the sorting bar.
	 * @return string HTML of sorting bar.
	 */
	protected function getSortingBar() {
		$aSorting = $this->getOption( 'sorting' );
		$aOut = array();
		$aOut[] = '<span class="bs-extendedsearch-sorting-results">';
		$aOut[] = $this->getNumberOfPageItems();
		$aOut[] = '</span>';

		$aOut[] = '<span class="bs-extendedsearch-sorting-sortby">';
		$aOut[] = wfMessage( 'bs-extendedsearch-sort-by' )->plain();

		// 'titleSort', 'score', 'type', 'ts'
		$iItems = count( $aSorting['sorttypes'] );
		$iNum = 1;

		foreach ( $aSorting['sorttypes'] as $sort => $sMessage ) {
			$bActive = ( $sort == $aSorting['sortactive'] );
			if ( $bActive ) {
				$sDirection = ( $aSorting['sortdirection'] == 'asc' ) ? 'desc' : 'asc';
				$sDirectionMessage = ( $aSorting['sortdirection'] == 'asc' )
					? wfMessage( 'bs-extendedsearch-ascending' )->plain()
					: wfMessage( 'bs-extendedsearch-descending' )->plain();

				global $wgScriptPath;
				$sIcon .= '" title="' . $sDirectionMessage . '" alt="' . $sDirectionMessage . '" />';
				$sImg = ( $aSorting['sortdirection'] == 'asc' ) ? 'arrow_up.png' : 'arrow_down.png';
				$sImgPath = $wgScriptPath . '/extensions/BlueSpiceExtensions/ExtendedSearch/resources/images/';
				$sIcon = Html::element(
					'img',
					array(
						'src' => $sImgPath . $sImg,
						'title' => $sDirectionMessage,
						'alt' => $sDirectionMessage
					)
				);
			} else {
				// $direction = $sortDirection todo: think it over: if sort order is changed from score to time, the order should be reset!
				$sDirection = ( in_array( $sort, array( 'titleSort', 'type' ) ) ) ? 'asc' : 'desc';
				$sIcon = '';
			}

			if ( $bActive ) $aOut[] = '<b>';

			$aOut[] = Html::element(
				'a',
				array( 'href' => $aSorting['sorturl'].'&search_asc='.$sDirection.'&search_order='.$sort ),
				wfMessage( $sMessage )->plain()
			);

			$aOut[] = $sIcon;

			if ( $bActive ) $aOut[] = '</b>';
			if ( $iNum < $iItems ) {
				$aOut[] = '|';
			}
			$iNum++;
		}

		$aOut[] = '</span>';

		return implode( "\n" , $aOut );
	}

	public function setFacet( $oFacet ) {
		$this->aFacetBoxes[] = $oFacet;
	}

	public function getFacets() {
		return $this->aFacetBoxes;
	}
	/**
	 * Returns the range of numbers which articles are displayed
	 * @return string range.
	 */
	public function getNumberOfPageItems() {
		$iNumOfResults = BsConfig::get( 'MW::ExtendedSearch::LimitResults' );
		$iBegin = ( ( $this->getOption( 'activePage' ) - 1 ) * $iNumOfResults ) + 1;
		$iEnd = $this->getOption( 'activePage' ) * $iNumOfResults;
		$iNumFound = $this->getOption( 'numFound' );

		if ( $iNumFound < $iEnd ) {
			$iEnd = $iNumFound;
		}

		return wfMessage( 'bs-extendedsearch-result-caption', $iBegin, $iEnd, $iNumFound )->text();
	}

}