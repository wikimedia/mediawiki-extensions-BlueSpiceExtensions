<?php

/**
 * Renders the Review special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage Review
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
//Last Code Review RBV (30.06.2011)

/**
 * Review special page that renders the review edit dialogue
 * @package BlueSpice_Extensions
 * @subpackage Review
 */
class SpecialReview extends BsSpecialPage {

	/**
	 * Constructor of SpecialReview class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		parent::__construct( 'Review', 'workflowview' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Renders special page output.
	 * @param string $sParameter Name of the article, who's review should be edited, or user whos review should be displayed.
	 * @return bool Allow other hooked methods to be executed. always true.
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );
		$sOut = '';

		$oOutputPage = $this->getOutput();
		$oOutputPage->addModules( 'ext.bluespice.review.overview' );

		//TODO: Redundant?
		if ( !$this->getUser()->isAllowed('workflowview') ) {
			$sOut = wfMessage( 'bs-review-not-allowed' )->plain();
			$oOutputPage->addHTML($sOut);
			return true;
		}

		$oUser = User::newFromName( $sParameter );
		if( $oUser && $oUser->getId() > 0 ) {
			$sName = $oUser->getRealName();
			$sName = empty( $sName ) ? $oUser->getName() : $oUser->getRealName().' ('.$oUser->getName().')';
			$oOutputPage->setPageTitle( wfMessage( 'bs-review-specialreview-header', 1, $sName )->text() );
			$oOutputPage->addJsConfigVars( 'bsSpecialReviewUserID', $oUser->getId() );
			$oOutputPage->addJsConfigVars( 'bsSpecialReviewUserName', $oUser->getName() );
		} else {
			$oOutputPage->setPageTitle( wfMessage( 'bs-review-specialreview-header', 0 )->text() );
		}

		$oOutputPage->addHTML( $sOut );
		$oOutputPage->addHTML(
			Html::element( 'div', array( 'id' => 'bs-review-overview') )
		);
		return true;
	}

	public function getGroupName() {
		return 'bluespice';
	}
}