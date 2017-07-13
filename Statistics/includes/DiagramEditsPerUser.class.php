<?php
/**
 * Describes edits per user diagram for Statistics for BlueSpice.
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
 * Describes edits per user for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 */
class BsDiagramEditsPerUser extends BsDiagram {

	/**
	 * Constructor of BsDiagramEditsPerUser class
	 */
	public function __construct() {
		parent::__construct();

		$this->sTitle = wfMessage( 'bs-statistics-diag-edits-per-user')->text();
		$this->sDescription = wfMessage( 'bs-statistics-diag-edits-per-user-desc')->text();
		$this->sTitlex = wfMessage( 'bs-statistics-label-time')->text();
		$this->sTitley = wfMessage( 'bs-statistics-label-count')->text();
		$this->sActualGrain = "m";
		$this->sModLabel = "M y";
		$this->sFormatX = "%01.1f";
		$this->iDataSource = BsDiagram::DATASOURCE_DATABASE;
		$this->bListable = false;
		$this->sSqlWhatForDiagram = "a/b";
		// Evtl: user->edits oder artikel->edits?
		//$this->sSqlWhatForList = "DISTINCT page_title, rev_user_text";
		// Important: Keep DISTINCT rev_id, otherwise a revision is counted once per category link
		// TODO MRG (30.04.12 01:00): Wieso werden die categorylinks überhaupt gezählt?
		$this->sSqlFromWhere = "FROM (
									SELECT count(DISTINCT rev_id) as a
										FROM #__revision AS a
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
										AND @BsFilterCategory
									) as x,
									(
										SELECT count(user_id) as b
											FROM #__user
											WHERE user_registration <= @end
											AND user_id NOT IN (
												SELECT ug_user FROM #__user_groups WHERE ug_group = 'bot'
											)
											AND NOT user_name IN (@BsFilterUsers)
									) as y";
		//$this->sListLabel = array(wfMessage( 'label-article')->text(), wfMessage( 'label-creator')->text());
		$this->sMode = BsDiagram::MODE_ABSOLUTE;

		$this->addFilter( new BsFilterNamespace( $this, array( 0 ) ) );
		$this->addFilter( new BsFilterCategory( $this ) );
		$this->addFilter( new BsFilterUsers( $this ) );
	}
}