<?php
/**
 * Describes namespace filter for Statistics for BlueSpice.
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
 * Describes namesoace filter for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class BsFilterNamespace extends BsMultiSelectFilter {

	/**
	 * Constructor of BsStatisticsFilter class
	 * @param BsDiagram $oDiagram Instance of diagram the filter is used with.
	 */
	public function __construct( $oDiagram, $aDefaultValues ) {
		parent::__construct( $oDiagram, $aDefaultValues );

		$this->sLabel = wfMessage( 'bs-ns' )->text();
		$this->aAvailableValues = $this->loadAvailableValues();
		$this->aDefaultValues = array( 0 );
	}

	/**
	 * Returns SQL statement for data retrieval
	 * @return string SQL statement
	 */
	public function getSql() {
		$this->getActiveValues();
		if ( !is_array( $this->aActiveValues ) || count( $this->aActiveValues ) <=0 ) {
			return '1=1';
		}

		$aInClause = array();

		foreach ( $this->aActiveValues as $sValue ) {
			$aInClause[] = "'".$sValue."'";
		}

		$sInClause = implode( ',', $aInClause );

		$sSql = $sInClause;
		$sSql = 'page_namespace IN ('.$sInClause.')';
		return $sSql;
	}

	/**
	 * Dynamically retrieves a list of all categories
	 * @return array List of strings
	 */
	public function loadAvailableValues() {
		return BsNamespaceHelper::getNamespacesForSelectOptions( array( -2,-1 ) );
	}

	// TODO MRG (22.12.10 00:56): Diese funktion überschreibt parent. das sollte man besser lösen
	/**
	 * Returns an internationalized list of available values
	 * @return array List of key value pairs
	 */
	public function getLabelledAvailableValues() {
		$this->aLabelledAvailableValues = $this->aAvailableValues;
		return $this->aLabelledAvailableValues;
	}

	/*
	 * Returns description of active filter
	 * @return string
	 */
	public function getActiveFilterText() {
		$this->getActiveValues();
		$aI18NValues = array();
		foreach ( $this->aActiveValues as $sValue ) {
			$aI18NValues[] = BsNamespaceHelper::getNamespaceName( $sValue );
		}
		return implode( ", ", $aI18NValues );
	}
}