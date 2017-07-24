<?php
/**
 * Renders a single ExtendedSearch search result.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.com>
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
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
		$sHighlightSnippets = $this->getOption( 'highlightsnippets' );
		if ( !empty( $sHighlightSnippets ) ) {
			$sHighlightSnippets = $this->processSnippets( $sHighlightSnippets );
		}

		$aResultInfo = array();
		$aResultInfo[] = $this->getOption( 'timestamp' );
		if ( $this->getOption( 'redirect' ) ) {
			$aResultInfo[] = $this->getOption( 'redirect' );
		}

		$aTemplate = array();
		$aTemplate[] = '<div class="search-wrapper">';
		$aTemplate[] = '<div class="bs-extendedsearch-result-head">';
		$aTemplate[] = '<table><tr>';
		$aTemplate[] = '<td><span class="bs-extendedsearch-result-icon">' . $this->getIcon() . '</span></td>';
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

	protected function getIcon() {
		global $wgScriptPath;
		$sIconPath = $this->getOption( 'iconpath' );
		$sSearchIcon = $this->getOption( 'searchicon' );
		if( !empty( $sIconPath ) ) {
			return '<img src="' . $sIconPath . '" alt="'.$sSearchIcon.'" />';
		}

		$sImgPath = $wgScriptPath . '/extensions/BlueSpiceExtensions/ExtendedSearch/resources/images';
		$aImageLinks = array(
			'doc' => '<img src="' . $sImgPath . '/word.gif" alt="doc" /> ',
			'odt' => '<img src="' . $sImgPath . '/odt.png" alt="odt" /> ',
			'ppt' => '<img src="' . $sImgPath . '/ppt.gif" alt="ppt" /> ',
			'odp' => '<img src="' . $sImgPath . '/odp.png" alt="odp" /> ',
			'xls' => '<img src="' . $sImgPath . '/xls.gif" alt="xls" /> ',
			'ods' => '<img src="' . $sImgPath . '/ods.png" alt="ods" /> ',
			'pdf' => '<img src="' . $sImgPath . '/pdf.gif" alt="pdf" /> ',
			'txt' => '<img src="' . $sImgPath . '/txt.gif" alt="txt" /> ',
			'default' => '<img src="' . $sImgPath . '/page.gif" alt="page" /> '
		);
		if( !isset( $aImageLinks[$sSearchIcon] ) ) {
			$sSearchIcon = 'default';
		}

		return $aImageLinks[$sSearchIcon];
	}
}