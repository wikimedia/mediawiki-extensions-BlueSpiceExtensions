<?php
/**
 * Describes number of users diagram for Statistics for BlueSpice.
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
 * Describes number of users diagram for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 */
class BsDiagramNumberOfUsers extends BsDiagram {

	/**
	 * Constructor of BsDiagramNumberOfUsers class
	 */
	public function __construct() {
		parent::__construct();

		BsConfig::get( 'MW::Statistics::ExcludeUsers' );
		$this->sTitle = wfMessage( 'bs-statistics-diag-number-of-users' )->plain();
		$this->sDescription = wfMessage( 'bs-statistics-diag-number-of-users-desc' )->plain();
		$this->sTitlex = wfMessage( 'bs-statistics-label-time' )->plain();
		$this->sTitley = wfMessage( 'bs-statistics-label-count' )->plain();
		$this->sActualGrain = "m";
		$this->sModLabel = "M y";
		$this->iDataSource = BsDiagram::DATASOURCE_DATABASE;
		$this->bListable = true;
		$this->sSqlWhatForDiagram = "count(user_id)";
		$this->sSqlWhatForList = "user_name, user_registration";
		$this->sSqlFromWhere = "FROM #__user
								WHERE user_registration @period
								AND user_id NOT IN (
									SELECT ug_user
									FROM #__user_groups
									WHERE ug_group = 'bot'
								)
								AND user_id NOT IN (
									SELECT ipb_user FROM ipblocks
								)
								AND user_name NOT IN (@BsFilterUsers)";
		$this->sListLabel = array( wfMessage( 'bs-statistics-label-name' )->plain(), wfMessage( 'bs-statistics-label-registration' )->plain() );
		$this->sMode = BsDiagram::MODE_AGGREGATED;

		$this->addFilter( new BsFilterUsers( $this ) );
	}
}