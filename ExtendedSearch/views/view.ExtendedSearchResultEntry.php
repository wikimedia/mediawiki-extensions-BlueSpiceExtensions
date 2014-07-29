<?php
/**
 * Renders a single ExtendedSearch search result.
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
 * This view renders a single ExtendedSearch search result.
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ViewExtendedSearchResultEntry extends ViewBaseElement {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Preprocesses highlight snippets as they come from Solr.
	 * @param array $aSnippets List of snippets with search text occurrences.
	 * @return string Modified snipped.
	 */
	protected function processSnippets( array $aSnippets ) {
		$sOut = '';
		foreach ( $aSnippets as $sFrag ) {
			if ( empty( $sFrag ) ) continue;
			$sFrag = htmlspecialchars( $sFrag, ENT_QUOTES, 'UTF-8' );
			$sFrag = str_replace( array( '&lt;em&gt;', '&lt;/em&gt;' ), array( '<em>', '</em>' ), $sFrag );
			$sOut .= $sFrag . '<br />';

		}
		return $sOut;
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $aParam = false ) {
		$aTemplate = array();

		$sHighlightSnippets = $this->getOption( 'highlightsnippets' );
		if ( !empty( $sHighlightSnippets ) ) {
			$sHighlightSnippets = $this->processSnippets( $sHighlightSnippets );
		}

		$aResultInfo = array();
		$aResultInfo[] = $this->getOption( 'timestamp' );
		if ( $this->getOption( 'redirect' ) ) {
			$aResultInfo[] = $this->getOption( 'redirect' );
		}

		$aTemplate[] = '<div class="search-wrapper">';
		$aTemplate[] = '<div class="bs-extendedsearch-result-head">';
		$aTemplate[] = '<table><tr>';
		$aTemplate[] = '<td><span class="bs-extendedsearch-result-icon">' . $this->getOption( 'searchicon' ) . '</span></td>';
		$aTemplate[] = '<td><span class="bs-extendedsearch-result-title"><h3>' . $this->getOption( 'searchlink' ) . '</h3></span></td>';
		$aTemplate[] = '</tr></table>';
		$aTemplate[] = '</div>';
		$aTemplate[] = '<div class="bs-search-result-info">';

		$aTemplate[] = implode( ' | ', $aResultInfo );

		$aTemplate[] = '</div>';

		if ( $this->getOption( 'highlightsnippets' ) ) {
			$aTemplate[] = '<div class="bs-search-hit-text">' . $sHighlightSnippets . '</div>';
		}

		$sCategories = trim( $this->getOption( 'catstr' ) );
		if ( !empty( $sCategories ) ) {
			$aTemplate[] = '<div class="bs-extendedsearch-cat search-result-entry-info">'.
				wfMessage( 'bs-extendedsearch-category-filter', $this->getOption( 'catno' ), $sCategories )->plain().'</div>';
		}

		$aTemplate[] = '</div>';

		return implode( "\n", $aTemplate );
	}

}