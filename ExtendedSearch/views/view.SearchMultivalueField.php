<?php
/**
 * Renders a ExtendedSearch multivalue field.
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
 * This view renders a ExtendedSearch multivalue field.
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch 
 */
class ViewSearchMultivalueField extends ViewBaseElement {

	/**
	 * List of options that are to be displayed in options box.
	 * @var array List of strings. Key => value.
	 */
	protected $entries = array();

	/**
	 * Adds a select box option.
	 * @param string $key Key for option.
	 * @param array $valueArray 'value' => string, 'selected' => bool
	 */
	public function addEntry( $key, $valueArray ) {
		$this->entries[$key] = $valueArray;
	}

	/**
	 * Getter method for entries.
	 * @return array List of select box options.
	 */
	public function &getEntries() {
		return $this->entries;
	}

	/**
	 *
	 * @param string $html HTML to be rendered below the box.
	 */
	public function dirtyAppend( $html ) {
		$this->dirtyAppended = $html;
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $param = false ) {
		$sHeadline = wfMessage( $this->getOption( 'i18nKeyName' ) )->plain();
		$help = Xml::element(
			'span',
			array( 'style' => 'color: #777777; font-size: 10px;' ),
			wfMessage( 'bs-extendedsearch-search-help-multiselect' )->plain(),
			false
		);

		$sEntries = '';
		foreach ( $this->entries as $datasetArray ) {
			$bSelected = false;
			if ( isset( $datasetArray['selected'] ) && $datasetArray['selected'] === true ) $bSelected = true;
			$sEntries .= Xml::option( 
				htmlentities( $datasetArray['text'], ENT_QUOTES, 'UTF-8' ),
				$datasetArray['value'],
				$bSelected
			);
		}

		$sFormSelect = Xml::openElement(
			'select', 
			array(
				'name'     => $this->getOption( 'urlFieldName' ),
				'multiple' => 'true',
				'size'     => 10,
				'style'    => 'width: 200px'
			)
		);
		$sFormSelect .= $sEntries;
		$sFormSelect .= Xml::closeElement( 'select' );

		$divBody = $sHeadline.'<br />'.$help.'<br />'.$sFormSelect;

		if ( isset( $this->dirtyAppended ) )$divBody .= $this->dirtyAppended;
		$sOut = Xml::openElement( 'div', array( 'class' => 'bs-extendedsearch-multivaluefield' ) ).
				$divBody.
				Xml::closeElement( 'div' );

		return $sOut;
	}

}
