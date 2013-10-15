<?php
/**
 * Describes search query diagram for Statistics for BlueSpice.
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
 * Describes search query diagram for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class BsDiagramSearches extends BsDiagram {

	/**
	 * Constructor of BsDiagramSearches class
	 */
	public function __construct() {
		parent::__construct();

		$this->sTitle = wfMsg( 'bs-statistics-diag-search-queries');
		$this->sDescription = wfMsg( 'bs-statistics-diag-search-queries-desc');
		$this->sTitlex = wfMsg( 'bs-statistics-label-time');
		$this->sTitley = wfMsg( 'bs-statistics-label-count');
		$this->sActualGrain = "m";
		$this->sModLabel = "M";
		$this->iDataSource = BsDiagram::DATASOURCE_DATABASE;
		$this->bListable = true;
		$this->sSqlWhatForDiagram = "count(stats_term)";
		$this->sSqlWhatForList = "stats_term, count(stats_term) as x, max(stats_hits)";
		$this->sSqlFromWhere = "FROM #__bs_searchstats WHERE stats_ts @period AND @BsFilterSearchScope";
		$this->sSqlOptionsForList = "GROUP BY stats_term";
		$this->sListLabel = array( wfMsg( 'bs-statistics-label-searchterm' ), wfMsg( 'bs-statistics-label-count' ), wfMsg( 'bs-statistics-label-maxhits' ) );
		$this->sMode = BsDiagram::MODE_AGGREGATED;

		$this->addFilter( new BsFilterSearchScope( $this, array( "title" ) ) );
	}
}