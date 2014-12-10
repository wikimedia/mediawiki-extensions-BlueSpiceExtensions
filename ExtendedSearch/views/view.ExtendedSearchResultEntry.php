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
		global $wgScriptPath;
		$sImgPath = $wgScriptPath . '/extensions/BlueSpiceExtensions/ExtendedSearch/resources/images';

		$aImageLinks = array(
			'doc' => '<img src="' . $sImgPath . '/word.gif" alt="doc" /> ',
			'ppt' => '<img src="' . $sImgPath . '/ppt.gif" alt="ppt" /> ',
			'xls' => '<img src="' . $sImgPath . '/xls.gif" alt="xls" /> ',
			'pdf' => '<img src="' . $sImgPath . '/pdf.gif" alt="pdf" /> ',
			'txt' => '<img src="' . $sImgPath . '/txt.gif" alt="txt" /> ',
			'default' => '<img src="' . $sImgPath . '/page.gif" alt="page" /> '
		);

		$sHighlightSnippets = $this->getOption( 'highlightsnippets' );
		if ( !empty( $sHighlightSnippets ) ) {
			$sHighlightSnippets = $this->processSnippets( $sHighlightSnippets );
		}

		$aResultInfo = array();
		$aResultInfo[] = $this->getOption( 'timestamp' );
		if ( $this->getOption( 'redirect' ) ) {
			$aResultInfo[] = $this->getOption( 'redirect' );
		}

		$sIconPath = $this->getOption( 'iconpath' );
		$sIcon = ( empty( $sIconPath ) )
			? $aImageLinks[$this->getOption( 'searchicon' )]
			: $sIconPath;

		$aTemplate = array();
		$aTemplate[] = '<div class="search-wrapper">';
		$aTemplate[] = '<div class="bs-extendedsearch-result-head">';
		$aTemplate[] = '<table><tr>';
		$aTemplate[] = '<td><span class="bs-extendedsearch-result-icon">' . $sIcon . '</span></td>';
		$aTemplate[] = '<td><span class="bs-extendedsearch-result-title"><h3>' . $this->getOption( 'searchlink' ) . '</h3></span></td>';
		$aTemplate[] = '</tr></table>';
		$aTemplate[] = '</div>';
		$aTemplate[] = '<div class="bs-search-result-info">';

		$aTemplate[] = implode( ' | ', $aResultInfo );

		$aTemplate[] = '</div>';

		if ( !empty( $sHighlightSnippets ) ) {
			$aTemplate[] = '<div class="bs-search-hit-text">' . $sHighlightSnippets . '</div>';
		}

		$sCategories = trim( $this->getOption( 'catstr' ) );
		if ( !empty( $sCategories ) ) {
			$aTemplate[] = '<div class="bs-extendedsearch-cat search-result-entry-info">'.
				wfMessage( 'bs-extendedsearch-category-filter', $this->getOption( 'catno' ), $sCategories )->text().'</div>';
		}

		$aTemplate[] = '</div>';

		return implode( "\n", $aTemplate );
	}

}