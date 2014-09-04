<?php
/**
 * Renders a single ExtendedSearch facet.
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
 * This view renders a single ExtendedSearch facet.
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ViewSearchFacet extends ViewBaseElement {

	/**
	 * Number of checked facets. Used to determine whether overall checkbox should be checked as well
	 * @var int Number of checked facets
	 */
	protected $iEntriesChecked = 0;
	/**
	 * List of checked facets.
	 * @var array List of checked facets.
	 */
	protected $aEntriesChecked = array();
	/**
	 * List of unchecked facets.
	 * @var array List of checked facets.
	 */
	protected $aEntriesUnChecked = array();

	/**
	 * Add facet either to checked or unchecked set.
	 * @param array $dataSet Set of facets.
	 */
	public function setData( array $dataSet ) {
		if ( isset( $dataSet['diff'] ) ) {
			$dataSet['diff'] = ' urldiff="'.htmlspecialchars( $dataSet['diff'], ENT_QUOTES, 'UTF-8' ).'"';
		}
		if ( isset( $dataSet['uri'] ) ) {
			$dataSet['uri'] = htmlspecialchars( $dataSet['uri'], ENT_QUOTES, 'UTF-8' );
		}

		$dataSet['title'] = "{$dataSet['title']}";
		$dataSet['name-and-count'] = "&nbsp;{$dataSet['name']}&nbsp;({$dataSet['count']})";

		if ( isset( $dataSet['checked'] ) && ( $dataSet['checked'] === true ) ) {
			$this->iEntriesChecked++;
			$dataSet['checked'] = ' checked="checked"';
			$this->aEntriesChecked[] = $dataSet;

		}
		else {
			$dataSet['checked'] = '';
			$this->aEntriesUnChecked[] = $dataSet;
		}
	}

	/**
	 * Template for single facet item.
	 */
	public function setTemplate( $template ) {
		$out = '<div class="facetBarEntry" title="{title}">';
		$out .= '<input type="checkbox"{checked} {diff} class="searchcheckbox" />';
		$out .= '<label>{name-and-count}</label>';
		$out .= '</div>';
		parent::setTemplate( $out );
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$this->setTemplate( '' );

		$bChecked = ( $this->iEntriesChecked > 0 )
			? true
			: false;

		$sFacetHead = '<div class="bs-facet-title bs-extendedsearch-default-textspacing">';
		$sFacetHead .= Xml::check( '', $bChecked, array( 'urldiff' => $this->getOption( 'uri-facet-all-diff' ) ) );
		$sFacetHead .= '<label>' . wfMessage( $this->getOption( 'title' ) )->plain(). '</label>';
		$sFacetHead .= '</div>';

		$this->addCompleteDataset( $this->aEntriesChecked );
		$body = parent::execute();

		if ( !empty( $this->aEntriesChecked ) && !empty( $this->aEntriesUnChecked ) ) {
			$body .= Xml::element( 'span', array( 'class' => 'bs-extendedsearch-facet-separator' ), '', false );
		}

		$this->addCompleteDataset( $this->aEntriesUnChecked );
		$body .= parent::execute();

		if ( empty( $body ) ) return '';

		$body .= '<div class="bs-extendedsearch-facetend"></div><div class="bs-extendedsearch-facetbox-more"></div>';

		$body = Xml::openElement( 'div', array( 'class' => 'bs-extendedsearch-facetbox-container' ) ) .
				$body .
				Xml::closeElement( 'div' );
		$body = Xml::openElement( 'div', array( 'class' => 'bs-extendedsearch-facetbox' ) ) .
				$body .
				Xml::closeElement( 'div' );

		return $sFacetHead.$body;
	}

}