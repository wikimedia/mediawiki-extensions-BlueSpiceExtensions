<?php
/**
 * Describes search scope filter for Statistics for BlueSpice.
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
 * Describes search scope filter for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class BsFilterSearchScope extends BsSelectFilter {

	/**
	 * Constructor of BsFilterCategory class
	 * @param BsDiagram $oDiagram Instance of diagram the filter is used with.
	 * @param array $aDefaultValues List of strings
	 */
	public function __construct( $oDiagram, $aDefaultValues ) {
		parent::__construct( $oDiagram, $aDefaultValues );

		$this->sLabel = wfMessage( 'bs-statistics-filter-searchscope' )->text();
		$this->aAvailableValues = array( 'title', 'text', 'files', 'all' );
		$this->aDefaultValues = array( 'all' );
	}

	/**
	 * Returns SQL statement for data retrieval
	 * @return string SQL statement
	 */
	public function getSql() {
		$this->getActiveValues();
		if ( !is_array( $this->aActiveValues ) || count( $this->aActiveValues ) <=0 ) {
			return '';
		}
		switch ( $this->aActiveValues[0] ) {
			case 'title' :
				$sSql = "stats_scope LIKE ( '%title%' )";
				break;
			case 'text' :
				$sSql = "stats_scope LIKE ( '%text%' )";
				break;
			case 'files' :
				$sSql = "stats_scope LIKE ( '%files%' )";
				break;
			case 'all' :
				$sSql = "(1=1)";
				break;
			default :
				$sSql = '';
		}
		return $sSql;
	}
}