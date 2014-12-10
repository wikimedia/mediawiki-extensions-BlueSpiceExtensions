<?php
/**
 * Describes number of articles diagram for Statistics for BlueSpice.
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
 * Describes number of articles for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class BsDiagramNumberOfArticles extends BsDiagram {

	/**
	 * Constructor of BsDiagramNumberOfArticles class
	 */
	public function __construct() {
		parent::__construct();

		$this->sTitle = wfMsg( 'bs-statistics-diag-number-of-pages-mw');
		$this->sDescription = wfMsg( 'bs-statistics-diag-number-of-pages-mw-desc');
		$this->sTitlex = wfMsg( 'bs-statistics-label-time');
		$this->sTitley = wfMsg( 'bs-statistics-label-count');
		$this->sActualGrain = "m";
		$this->sModLabel = "M";
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
								AND NOT page_is_redirect = 1
								AND (
									page_id IN (
										SELECT DISTINCT(pl_from)
										FROM #__pagelinks
									)
									OR page_id IN (
										SELECT DISTINCT(cl_from)
										FROM #__categorylinks
									)
									OR page_id IN (
										SELECT DISTINCT(tl_from)
										FROM #__templatelinks
									)
									OR page_id IN (
										SELECT DISTINCT(il_from)
										FROM #__imagelinks
									)
									OR page_id IN (
										SELECT DISTINCT(el_from)
										FROM #__externallinks
									)
									OR page_id IN (
										SELECT DISTINCT(ll_from)
										FROM #__langlinks
									)
								)
								AND @BsFilterCategory";
		$this->sListLabel = array( wfMsg( 'bs-statistics-label-page'), wfMsg( 'bs-statistics-label-creator'));
		$this->sMode = BsDiagram::MODE_AGGREGATED;

		$this->addFilter( new BsFilterNamespace( $this, array( 0 ) ) );
		$this->addFilter( new BsFilterCategory( $this ) );
	}
}