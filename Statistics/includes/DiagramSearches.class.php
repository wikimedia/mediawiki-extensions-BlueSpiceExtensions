<?php
/**
 * Describes search query diagram for Statistics for BlueSpice.
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

		$this->sTitle = wfMessage( 'bs-statistics-diag-search-queries')->text();
		$this->sDescription = wfMessage( 'bs-statistics-diag-search-queries-desc')->text();
		$this->sTitlex = wfMessage( 'bs-statistics-label-time')->text();
		$this->sTitley = wfMessage( 'bs-statistics-label-count')->text();
		$this->sActualGrain = "m";
		$this->sModLabel = "M y";
		$this->iDataSource = BsDiagram::DATASOURCE_DATABASE;
		$this->bListable = true;
		$this->sSqlWhatForDiagram = "count(stats_term)";
		$this->sSqlWhatForList = "stats_term, count(stats_term) as x, max(stats_hits)";
		$this->sSqlFromWhere = "FROM #__bs_searchstats WHERE stats_ts @period AND @BsFilterSearchScope";
		$this->sSqlOptionsForList = "GROUP BY stats_term";
		$this->sListLabel = array( wfMessage( 'bs-statistics-label-searchterm' )->text(), wfMessage( 'bs-statistics-label-count' )->text(), wfMessage( 'bs-statistics-label-maxhits' )->text() );
		$this->sMode = BsDiagram::MODE_AGGREGATED;

		$this->addFilter( new BsFilterSearchScope( $this, array( "title" ) ) );
	}
}