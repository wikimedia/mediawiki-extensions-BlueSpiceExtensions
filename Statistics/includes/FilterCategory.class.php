<?php
/**
 * Describes category filter for Statistics for BlueSpice.
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
 * Describes category filter for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class BsFilterCategory extends BsMultiSelectFilter {

	/**
	 * Constructor of BsFilterCategory class
	 * @param BsDiagram $oDiagram Instance of diagram the filter is used with.
	 * @param array $aDefaultValues List of strings
	 */
	public function __construct( $oDiagram, $aDefaultValues = null ) {
		parent::__construct( $oDiagram, $aDefaultValues );

		$this->sLabel = wfMessage( 'bs-statistics-filter-category' )->text();
		$this->aAvailableValues = $this->loadAvailableValues();
		$this->aDefaultValues = array();
	}

	/**
	 * Returns SQL statement for data retrieval
	 * @return string SQL statement
	 */
	public function getSql() {
		$this->getActiveValues();
		// Beware: here, .* is returned instead of ''
		if ( !is_array( $this->aActiveValues ) || count( $this->aActiveValues ) <=0 ) {
			return '1=1';
		}

		$aInClause = array();

		foreach ( $this->aActiveValues as $sValue ) {
			if ($sValue == '(all)') {
				return '1=1';
			} else {
				$aInClause[] = "'".$sValue."'";
			}
		}

		$sInClause = join( ',', $aInClause );
		$sSql = $sInClause;
		$sSql = 'cl_to IN ('.$sInClause.')';
		return $sSql;
	}

	/**
	 * Dynamically retrieves a list of all categories
	 * @return array List of strings 
	 */
	public function loadAvailableValues() {
		$aCategories = array();
		// TODO MRG (20.02.11 23:51): i18n geht noch nicht so recht
		$aCategories[wfMessage( 'bs-ns_all' )->text()] = '(all)';
		// TODO MRG (22.12.10 01:19): Greift auf MW zu
		$oDbr = wfGetDB( DB_REPLICA );
		$rRes = $oDbr->select('categorylinks', 'distinct cl_to', '', '', array('ORDER BY' => 'cl_to ASC') );
		while ( $oRow = $rRes->fetchObject() ) {
			$aCategories[$oRow->cl_to] = $oRow->cl_to;
		}
		return $aCategories;
	}
}