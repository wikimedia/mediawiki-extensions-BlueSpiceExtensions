<?php
/**
 * Describes a multi select filter filter for Statistics for BlueSpice.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Describes a multi select filter filter for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
abstract class BsMultiSelectFilter extends BsSelectFilter {

	/**
	 * Constructor of BsFilterCategory class
	 * @param BsDiagram $oDiagram Instance of diagram the filter is used with.
	 * @param array $aDefaultValues List of strings
	 */
	public function __construct( $oDiagram, $aDefaultValues=null ) {
		parent::__construct( $oDiagram, $aDefaultValues );
		if ( !is_null( $aDefaultValues ) ) {
			$this->aDefaultValues = $aDefaultValues;
		}
	}

	/**
	 * Retrieves filter value from HTTP request
	 */
	public function getValueFromRequest() {
		global $wgRequest;
		$this->aActiveValues = $wgRequest->getArray( $this->getParamKey(), array() );
	}

	/*
	 * Returns description of active filter
	 * @return string
	 */
	public function getActiveFilterText() {
		$this->getActiveValues();
		$aI18NValues = array();
		foreach ( $this->aActiveValues as $sValue ) {
			$sValueText = preg_replace( "/^(<|&lt;)?(.*?)(>|&gt;)?$/", "$2", wfMessage( $sValue )->text() );
			$aI18NValues[] = $sValueText;
		}
		return implode( ", ", $aI18NValues );
	}
}