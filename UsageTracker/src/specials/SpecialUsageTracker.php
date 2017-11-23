<?php

/**
 * Renders the Usage Tracker special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage UsageTracker
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

class SpecialUsageTracker extends BsSpecialPage {

	public $iOpenTasks = null;

	/**
	 * Constructor of SpecialUsageTracker class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		parent::__construct( 'UsageTracker' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Renders special page output.
	 * @param string $sParameter Not used.
	 * @return bool Allow other hooked methods to be executed. Always true.
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$oRequest = $this->getRequest();

		// Handle update requests (in case the user has the neccesary rights)
		if ( $this->getUser()->isAllowed( 'usagetracker-update') ) {
			if ( $oRequest->wasPosted() ) {
				$aData = BsExtensionManager::getExtension( 'UsageTracker' )->getUsageData();
				// JobQueue...getSize is not updated fast enough, so we use the
				// raw count of jobs just enqueued.
				$this->iOpenTasks = count( $aData );
			} else {
				$oJobQueue = JobQueueGroup::singleton()->get( 'usageTrackerCollectJob' );
				$oJobQueue->flushCaches();
				// This count is wrong, since some jobs are executed right at the
				// end of this page load. However, since we do not know the number
				// it's ok. Possibly, the user has to reload one time more than
				// necessary.
				$this->iOpenTasks = $oJobQueue->getSize();
			}
			$this->showUpdateForm();
		}

		$this->getOutput()->addModules( 'ext.UsageTracker.manager' );
		$this->getOutput()->addHTML( Html::element( 'div', [
			'id' => 'bs-usagetracker-manager'
		]));
	}

	/**
	 * Output a form to start collect jobs
	 */
	protected function showUpdateForm() {
		$this->getOutput()->addHTML(
			Html::openElement(
				'form',
				[
					'method' => 'post',
					'action' => $this->getContext()->getTitle()->getFullURL(),
					'name' => 'utjobs',
					'id' => 'bs-useagetracker-form1'
				]
			) .
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
			Xml::fieldset( $this->msg( 'bs-usagetracker-create-statistics' )->text() ) .
			Xml::element( 'div', [], $this->msg( 'bs-usagetracker-caution' )->text() )
		);
		if ( $this->iOpenTasks > 0 ) {
			$this->getOutput()->addHTML( Html::openElement( 'b' ) );
			$this->getOutput()->addHTML(
				$this->msg(
					'bs-usagetracker-open-tasks',
					$this->iOpenTasks,
					SpecialPage::getTitleFor( 'UsageTracker' )->getLinkURL()
				)->text()
			);
			$this->getOutput()->addHTML( Html::closeElement( 'b' ) );
		} else {
			$this->getOutput()->addHTML(
				Xml::submitButton(
					$this->msg(
						'bs-usagetracker-startjobs'
					)->text()
				)
			);
		}
		$this->getOutput()->addHTML(
			Html::closeElement('fieldset') .
			Html::closeElement('form') . "\n"
		);
	}

}