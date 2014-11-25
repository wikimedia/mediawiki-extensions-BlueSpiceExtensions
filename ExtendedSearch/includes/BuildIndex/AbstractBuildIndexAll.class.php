<?php
/**
 * Index builder for ExtendedSearch
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Abstract index builder
 */
abstract class AbstractBuildIndexAll {

	/**
	 * Instance of BuildIndexMainControl
	 * @var BsBuildIndexMainControl
	 */
	protected $oMainControl = null;
	/**
	 * {iLimitStart},{iLimitRange}
	 * @var string
	 */
	protected $iLimit = 5000;
	/**
	 * Indicator for source of documents
	 * @var string
	 */
	protected $mode = '';
	/**
	 * Maximum execution per document
	 * @var int Time in seconds
	 */
	protected $iTimeLimit = 0;
	/**
	 * Number of documents already indexed.
	 * @var int
	 */
	protected $count = 0;
	/**
	 * Number of documents found as potential candidates for indexing.
	 * @var int
	 */
	protected $totalNoDocumentsCrawled = 0;

	/**
	 * Constructor for BsAbstractBuildIndexAll class
	 * @param BsBuildIndexMainControl $oMainControl Instance of build index controller
	 */
	public function __construct( $oMainControl ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->oMainControl = $oMainControl;

		if ( !$this->oMainControl->bCommandLineMode ) {
			$this->iTimeLimit = ini_get( 'max_execution_time' );
		}

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Sets $this->iLimit (option)
	 * @param string $iLimit {iLimitStart},{iLimitRange}
	 */
	public function setLimit( $iLimit ) {
		$this->iLimit = $iLimit;
	}

	/**
	 * Setter for iTimeLimit
	 * @param int $iTimeLimit Maximum execution time per document in seconds
	 * @return BsAbstractBuildIndexAll Reference to self for method chaining
	 */
	public function setTimeLimit( $iTimeLimit ) {
		$this->iTimeLimit = $iTimeLimit;
	}

	/**
	 * Setter for mode
	 * @param string $sMode Indicator for source of documents
	 * @return BsAbstractBuildIndexAll Reference to self for method chaining
	 */
	public function setMode( $sMode ) {
		$this->mode = $sMode;
	}

	/**
	 * Checks whether system settings are ok for a build run.
	 *
	 * if not implemented by child classes
	 * OR if implemented and called via
	 * parent::areYouAbleToRunWithSystemSettings
	 * @param array &$aErrorMessageKeys to be filled with keys of error messages
	 * @return array error-message-keys as values if some prerequisite is not met;
	 */
	public static function areYouAbleToRunWithSystemSettings( &$aErrorMessageKeys = array() ) {
		return empty( $aErrorMessageKeys );
	}

	/**
	 * Calculates current progress
	 * @return int Progress value between 0 and 100
	 */
	public function getProgress() {
		$safeTotalNo = ( $this->totalNoDocumentsCrawled > 0 ) ? $this->totalNoDocumentsCrawled : 1;
		$progress = ceil( $this->count / $safeTotalNo * 100 );
		return $progress;
	}

	/**
	 * Wrapper function for writeLog of Main Control
	 * @param string $sMessage The message to write
	 * @param string $sMode Indicator for source of documents
	 */
	public function writeLog( $sMessage = '', $sMode = null ){
		if ( $sMode === null ) $sMode = $this->mode;
		$this->oMainControl->write( $sMode, $sMessage, $this->getProgress() );
	}

}