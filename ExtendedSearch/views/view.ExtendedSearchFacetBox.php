<?php
/**
 * Renders a single ExtendedSearch facet.
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
 * This view renders a single ExtendedSearch facet.
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ViewSearchFacet extends ViewBaseElement {

	/**
	 * Basic configuration
	 * @var array
	 */
	protected $aConfig = array(

	);

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

	public function __construct( $aConfig ) {
		$this->aConfig = $aConfig;
		$this->setOption( 'title', $aConfig['i18n'] );
		$this->setOption( 'fset', array() );
		if( isset( $aConfig['settings'] ) ) {
			$this->setOption( 'fset', $aConfig['settings'] );
		}

		parent::__construct();
	}

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
		if( !isset( $dataSet['id'] ) ) {
			$dataSet['id'] = Sanitizer::escapeId( $dataSet['diff'] );
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
		$out .= '<input id="{id}" type="checkbox"{checked} {diff} class="searchcheckbox" />';
		$out .= '<label for="{id}">{name-and-count}</label>';
		$out .= '</div>';
		parent::setTemplate( $out );
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$this->setTemplate( '' );

		$this->addCompleteDataset( $this->aEntriesChecked );
		$body = parent::execute();

		if ( !empty( $this->aEntriesChecked ) && !empty( $this->aEntriesUnChecked ) ) {
			$body .= Xml::element( 'span', array( 'class' => 'bs-extendedsearch-facet-separator' ), '', false );
		}

		$this->addCompleteDataset( $this->aEntriesUnChecked );
		$body .= parent::execute();

		if ( empty( $body ) ) {
			return '';
		}

		$sFacetHead = $this->makeFacetHead( $this->iEntriesChecked > 0 );
		$body .= '<div class="bs-extendedsearch-facetend"></div><div class="bs-extendedsearch-facetbox-more"></div>';
		$body = Xml::openElement( 'div', array( 'class' => 'bs-extendedsearch-facetbox-container' ) ) .
				$body .
				Xml::closeElement( 'div' );
		$body = Xml::openElement( 'div', array( 'class' => 'bs-extendedsearch-facetbox' ) ) .
				$body .
				Xml::closeElement( 'div' );

		return $sFacetHead.$body;
	}

	public function makeFacetHead( $bChecked ) {
		$sId = Sanitizer::escapeId( $this->getOption( 'uri-facet-all-diff' ) );
		$sFacetHead = '<div class="bs-facet-title bs-extendedsearch-default-textspacing">';
		$sFacetHead .= Xml::check( '', $bChecked, array( 'id' => $sId, 'urldiff' => $this->getOption( 'uri-facet-all-diff' ) ) );
		$sFacetHead .= '<label for="'.$sId.'">' . wfMessage( $this->getOption( 'title' ) )->plain(). '</label>';
		if( isset( $this->aConfig['settings'] ) ) {
			$sFacetOperator = $this->getOption( 'fset' );
			$sFacetOperatorBox = '';
			$sFacetOperatorBox .= Html::element(
				'span',
				array(
					'class' => 'bs-extendedsearch-facet-filter ' .
						( $sFacetOperator['op'] == 'AND'
						? 'bs-extendedsearch-facet-filter-active'
						: '' ),
				),
				wfMessage( 'bs-extendedsearch-facetsetting-op-and' )->plain()
			);
			$sFacetOperatorBox .= Html::element(
				'span',
				array(
					'class' => 'bs-extendedsearch-facet-filter ' .
						( $sFacetOperator['op'] == 'OR'
						? 'bs-extendedsearch-facet-filter-active'
						: '' ),
				),
				wfMessage( 'bs-extendedsearch-facetsetting-op-or' )->plain()
			);
			$sFacetHead .= Html::rawElement(
				'a',
				array(
					'href' => '#',
					'class' => 'bs-es-facetsettings',
					'data-fset-param' => $this->aConfig['param'],
					'data-fset' => FormatJson::encode( $this->getOption( 'fset' ) )
				),
				$sFacetOperatorBox
			);
		}
		$sFacetHead .= '</div>';
		return $sFacetHead;
	}

}
