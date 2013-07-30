<?php
/**
 * Index builder for ExtendedSearch
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Core
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v0.1
 * FIRST CHANGES
 */
/**
 * Indexer builder for ExtendedSearch
 * @package BlueSpice_Core
 * @subpackage ExtendedSearch
 */
abstract class AbstractBuildIndexAll {

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
	 * Instance of build index controller
	 * @var BsBuildIndexMainControl
	 */
	protected $oMainControl;
	/**
	 * {iLimitStart},{iLimitRange}
	 * @var string
	 */
	protected $sLimit = null;
	/**
	 * Indicator for source of documents
	 * @var string
	 */
	protected $mode;
	/**
	 * Maximum execution per document
	 * @var int Time in seconds
	 */
	protected $iTimeLimit;
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
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Sets $this->sLimit (option)
	 * @param string $sLimit {iLimitStart},{iLimitRange}
	 */
	public function setLimit( $sLimit ) {
		$this->sLimit = $sLimit;
		return $this;
	}

	/**
	 * Setter for iTimeLimit
	 * @param int $iTimeLimit Maximum execution time per document in seconds
	 * @return BsAbstractBuildIndexAll Reference to self for method chaining
	 */
	public function setTimeLimit( $iTimeLimit ) {
		$this->iTimeLimit = $iTimeLimit;
		return $this;
	}

	/**
	 * Setter for mode
	 * @param string $sMode Indicator for source of documents
	 * @return BsAbstractBuildIndexAll Reference to self for method chaining
	 */
	public function setMode( $sMode ) {
		$this->mode = $sMode;
		return $this;
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
	 * Gets limit information for sql statement.
	 * @return array Key value pair: LIMIT => limit, OFFSET => offset 
	 */
	public function getLimitForDb() {
		if ( $this->sLimit === null || empty( $this->sLimit ) ) return array();
		list( $offset, $limit ) = explode( ',', $this->sLimit );
		return array( 'LIMIT' => $limit, 'OFFSET' => $offset );
	}

	/**
	 * Wrapper function for writeLog of Main Control
	 * @param string $sMessage The message to write
	 * @param string $sMode Indicator for source of documents
	 */
	public function writeLog( $sMessage = '', $sMode = null ){
		if ( $sMode === null ) $sMode = $this->mode;
		$this->oMainControl->writeLog( $sMode, $sMessage, $this->getProgress() );
	}

}