<?php
/**
 * Admin section for ExtendedSearch
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.com>
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v0.1
 * FIRST CHANGES
 */
/**
 * Base class for ExtendedSearch admin section
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ExtendedSearchAdmin {

	/**
	 * Instance of ExtendedSearchAdmin
	 * @var Object
	 */
	protected static $oInstance = null;

	/**
	 * Constructor of ExtendedSearchAdmin class
	 */
	public function __construct() {}

	/**
	 * Return a instance of ExtendedSearchAdmin.
	 * @return ExtendedSearchAdmin Instance of ExtendedSearchAdmin
	 */
	public static function getInstance() {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( self::$oInstance === null ) {
			self::$oInstance = new self();
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return self::$oInstance;
	}

	/**
	 * Return progress information when update index is called.
	 * @param string $sMode which progress should be rendered
	 */
	public static function getProgressBar( $sMode ) {
		switch ( $sMode ) {
			case 'createForm' :
				$sOutput = ExtendedSearchAdmin::getInstance()->getCreateForm();
				break;
			case 'create':
				$sOutput = ExtendedSearchAdmin::getInstance()->getCreateFeedback();
				break;
			case 'delete':
				$sOutput = ExtendedSearchAdmin::getInstance()->getDeleteFeedback();
				break;
			case 'deleteLock':
				$sOutput = (string) self::checkLockExistence( $sMode );
				break;
			default:
				$sOutput = '';
		}
		return $sOutput;
	}

	/**
	 * Checks if lock file exists
	 * @param String $sMode
	 * @return bool existence
	 */
	public static function checkLockExistence( $sMode = '' ) {
		if ( file_exists( BSDATADIR.DS.'ExtendedSearch.lock' ) ) {
			if ( $sMode == 'deleteLock' ) {
				unlink( BSDATADIR.DS.'ExtendedSearch.lock' );
			} else {
				return true;
			}
		} else {
			return false;
		}

		return true;
	}

	/**
	 * Returns content of create index dialogue.
	 * @return string HTML to be rendered.
	 */
	public function getCreateForm() {
		return '<div id="hwstatus">
					<span id="BsExtendedSearchMode">'. wfMessage( 'bs-extendedsearch-status' )->plain().'</span>:
					<span id="BsExtendedSearchMessage">' . wfMessage( 'bs-extendedsearch-about_to_start' )->plain() . '</span>
				</div>
				<div id="BsExtendedSearchProgress">&nbsp;</div>';
	}

	/**
	 * Returns status information of create index progress.
	 * Error is indicated by return false or return null
	 * An ApacheAjaxResponse is expected
	 * If you return a string $s a new ApacheAjaxResponse($s) is created
	 * @return string Progress in percent or error message.
	 */
	public function getCreateFeedback() {
		// delete the old Index
		$this->getDeleteFeedback();
		// build the new Index
		$vRes = BuildIndexMainControl::getInstance()->buildIndex();
		/* Beware of returntype:
		 * Error is indicated by return false or return null
		 * An ApacheAjaxResponse is expected
		 * If you return a string $s a new ApacheAjaxResponse($s) is created
		 */

		return $vRes;
	}

	/**
	 * Returns status information of delete index progress.
	 * Error is indicated by return false or return null
	 * @return string information about the Progress or error message.
	 */
	public function getDeleteFeedback() {
		$sForm = '';
		$oSolr = SearchService::getInstance();
		if ( $oSolr === null ) return '';

		try {
			$iStatus = $oSolr->deleteIndex();
			if ( $iStatus == 200 ) {
				$iStatus = $oSolr->deleteIndex();
				$sForm .= wfMessage( 'bs-extendedsearch-index-successfully-deleted' )->plain() . '<br />';
			} else {
				$sForm .= wfMessage( 'bs-extendedsearch-index-error-deleting', $iStatus )->plain() . '<br />';
			}
		} catch ( Exception $e ) {
			$sForm .= wfMessage( 'bs-extendedsearch-no-success-deleting', $e->getMessage() )->plain() . '<br />';
			$sForm .= $e->getMessage();
		}

		return $sForm;
	}

}