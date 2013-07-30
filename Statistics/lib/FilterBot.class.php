<?php
/**
 * Describes bot filter for Statistics for BlueSpice.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: FilterBot.class.php 6444 2012-09-10 13:04:48Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// TODO MRG (22.12.10 01:48): Not used yet

/**
 * Describes bot filter for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class BsFilterBot extends BsSelectFilter {

	/**
	 * Constructor of BsFilterBot class
	 * @param BsDiagram $oDiagram Instance of diagram the filter is used with.
	 */
	public function __construct( $oDiagram, $aDefaultValues = null ) {
		parent::__construct( $oDiagram, $aDefaultValues );

		$this->sLabel = wfMsg( 'bs-statistics-filter-bot' );
		$this->aAvailableValues = array( 'yes', 'no' );
		$this->aDefaultValues = array( 'yes' );
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

		$sSql = '';
		switch ( $this->aActiveValues[0] ) {
			case 'yes' :
				$sSql = "user_id NOT IN (SELECT ug_user FROM #__user_groups WHERE ug_group = 'bot')";
				break;
		}

		return $sSql;
	}
}