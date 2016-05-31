<?php
/**
 * Renders the ResponsibleEditors special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage ResponsibleEditors
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// Last review MRG (01.07.11 11:48)

/**
 * ResponsibleEditors SpecialPage
 * @package BlueSpice_Extensions
 * @subpackage ResponsibleEditors
 */
class SpecialResponsibleEditors extends BsSpecialPage {

	protected static $messagesLoaded = false;

	public function __construct() {
		parent::__construct( 'ResponsibleEditors', 'responsibleeditors-viewspecialpage' );
	}

	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$sOut = $this->renderOverviewGrid();

		$this->getOutput()->addHTML( $sOut );
	}

	private function renderOverviewGrid() {
		$sUserIsAllowedToChangeResponsibilities = false;
		if ( $this->getUser()->isAllowed( 'responsibleeditors-changeresponsibility' ) ) {
			$sUserIsAllowedToChangeResponsibilities = true;
		}

		$this->getOutput()->addJsConfigVars(
			'bsUserMayChangeResponsibilities',
			$sUserIsAllowedToChangeResponsibilities
		);
		$this->getOutput()->addModules('ext.bluespice.responsibleEditors.manager');

		return Html::element(
			'div',
			array(
				'id' => 'bs-responsibleeditors-container'
			)
		);
	}

	protected function getGroupName() {
		return 'bluespice';
	}
}