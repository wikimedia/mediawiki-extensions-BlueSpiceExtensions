<?php
/**
 * Describes user filter for Statistics for BlueSpice.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Describes user filter for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class BsFilterUsers extends BsMultiSelectFilter {

	/**
	 * Constructor of BsFilterCategory class
	 * @param BsDiagram $oDiagram Instance of diagram the filter is used with.
	 * @param array $aDefaultValues List of strings
	 */
	public function __construct( $oDiagram, $aDefaultValues = null ) {
		parent::__construct( $oDiagram, $aDefaultValues );

		$this->sLabel = wfMsg( 'bs-statistics-filter-user' );
		$this->aAvailableValues = $this->loadAvailableValues();
		$this->aDefaultValues = BsConfig::get( 'MW::Statistics::ExcludeUsers' );
	}

	/**
	 * Returns SQL statement for data retrieval
	 * @return string SQL statement
	 */
	public function getSql() {
		$this->getActiveValues();
		if ( !is_array( $this->aActiveValues ) || count( $this->aActiveValues ) <=0 ) {
			return "'USERFILTERTHISISNOUSER'";
		}

		$aInClause = array();

		foreach ( $this->aActiveValues as $sValue ) {
			$aInClause[] = "'".$sValue."'";
		}
		$sInClause = join( ', ', $aInClause );

		$sSql = $sInClause;
		return $sSql;
	}

	/**
	 * Dynamically retrieves a list of all users
	 * @return array List of strings
	 */
	public function loadAvailableValues() {
		$aUserNames = array();
		$oDbr = wfGetDB( DB_SLAVE );
		$rRes = $oDbr->select('user', 'distinct user_name', '', '', array('ORDER BY' => 'user_name ASC') );
		while ( $oRow = $rRes->fetchObject() ) {
			$aUserNames[$oRow->user_name] = $oRow->user_name;
		}
		return $aUserNames;
	}
}