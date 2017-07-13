<?php
/**
 * Describes number of edits diagram for Statistics for BlueSpice.
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
 * Describes number of edits for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 */
class BsDiagramNumberOfEdits extends BsDiagram {

	/**
	 * Constructor of BsDiagramNumberOfEdits class
	 */
	public function __construct() {
		parent::__construct();

		$this->sTitle = wfMessage( 'bs-statistics-diag-number-of-edits')->text();
		$this->sDescription = wfMessage( 'bs-statistics-diag-number-of-edits-desc')->text();
		$this->sTitlex = wfMessage( 'bs-statistics-label-time')->text();
		$this->sTitley = wfMessage( 'bs-statistics-label-count')->text();
		$this->sActualGrain = "m";
		$this->sModLabel = "M y";
		$this->iDataSource = BsDiagram::DATASOURCE_DATABASE;
		$this->bListable = false;
		$this->sSqlWhatForDiagram = "count(DISTINCT rev_id)";
		// Evtl: user->edits oder artikel->edits?
		//$this->sSqlWhatForList = "DISTINCT page_title, rev_user_text";
		// Important: Keep DISTINCT rev_id, otherwise a revision is counted once per category link
		//            cf also EditsPerUser
		// TODO MRG (30.04.12 01:00): Wieso werden die categorylinks überhaupt gezählt?
		$this->sSqlFromWhere = "FROM #__revision AS a
									JOIN #__page ON #__page.page_id = a.rev_page
									LEFT JOIN #__categorylinks AS c ON c.cl_from = a.rev_page
								WHERE rev_timestamp @period
								AND @BsFilterNamespace
								AND NOT rev_user IN (
									SELECT ug_user
									FROM #__user_groups
									WHERE ug_group = 'bot'
								)
								AND NOT rev_user_text IN (@BsFilterUsers)
								AND @BsFilterCategory";
		//$this->sListLabel = array(wfMessage( 'label-article')->text(), wfMessage( 'label-creator')->text());
		$this->sMode = BsDiagram::MODE_ABSOLUTE;

		$this->addFilter( new BsFilterNamespace( $this, array( 0 ) ) );
		$this->addFilter( new BsFilterCategory( $this ) );
		$this->addFilter( new BsFilterUsers( $this ) );
	}
}