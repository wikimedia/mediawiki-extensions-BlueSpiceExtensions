<?php
/**
 * Describes number of users diagram for Statistics for BlueSpice.
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
		$this->sTitle = wfMessage( 'bs-statistics-diag-number-of-users')->text();
		$this->sDescription = wfMessage( 'bs-statistics-diag-number-of-users-desc')->text();
		$this->sTitlex = wfMessage( 'bs-statistics-label-time')->text();
		$this->sTitley = wfMessage( 'bs-statistics-label-count')->text();
		$this->sActualGrain = "m";
		$this->sModLabel = "M";
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
								AND NOT user_name IN (@BsFilterUsers)";
		$this->sListLabel = array( wfMessage( 'bs-statistics-label-name')->text(), wfMessage( 'bs-statistics-label-registration')->text() );
		$this->sMode = BsDiagram::MODE_AGGREGATED;

		$this->addFilter( new BsFilterUsers( $this ) );
	}
}