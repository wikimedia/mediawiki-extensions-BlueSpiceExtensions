<?php
/**
 * Describes number of pages diagram for Statistics for BlueSpice.
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
 * Describes number of pages for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 */
class BsDiagramNumberOfPages extends BsDiagram {

	/**
	 * Constructor of BsDiagramNumberOfPages class
	 */
	public function __construct() {
		parent::__construct();

		$this->sTitle = wfMessage( 'bs-statistics-diag-number-of-pages')->text();
		$this->sDescription = wfMessage( 'bs-statistics-diag-number-of-pages-desc')->text();
		$this->sTitlex = wfMessage( 'bs-statistics-label-time')->text();
		$this->sTitley = wfMessage( 'bs-statistics-label-count')->text();
		$this->sActualGrain = "m";
		$this->sModLabel = "M y";
		$this->iDataSource = BsDiagram::DATASOURCE_DATABASE;
		$this->bListable = true;
		$this->sSqlWhatForDiagram = "count(DISTINCT rev_page)";
		$this->sSqlWhatForList = "DISTINCT page_title, rev_user_text";
		$this->sSqlFromWhere = "FROM #__revision AS a
									JOIN #__page ON #__page.page_id = a.rev_page
									LEFT JOIN #__categorylinks AS c ON c.cl_from = a.rev_page
								WHERE rev_timestamp @period
								AND rev_id in (
									SELECT Min(rev_id)
									FROM #__revision
									WHERE rev_page=a.rev_page
								)
								AND @BsFilterNamespace
								AND @BsFilterCategory";
		$this->sListLabel = array( wfMessage( 'bs-statistics-label-page')->text(), wfMessage( 'bs-statistics-label-creator')->text() );
		$this->sMode = BsDiagram::MODE_AGGREGATED;

		$this->addFilter( new BsFilterNamespace( $this, array( 0 ) ) );
		$this->addFilter( new BsFilterCategory( $this ) );
	}
}