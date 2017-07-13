<?php
/**
 * Describes a select filter filter for Statistics for BlueSpice.
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
 * Describes a select filter filter for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
abstract class BsSelectFilter extends BsStatisticsFilter {

	/**
	 * Lists all available values
	 * @var array List of srings
	 */
	protected $aAvailableValues = null;
	/**
	 * List default values
	 * @var array List of strings
	 */
	protected $aDefaultValues;
	/**
	 * List of currently selected values
	 * @var array List of strings 
	 */
	protected $aActiveValues;
	/**
	 * Lists all available values with internationalized labels
	 * @var array List of srings
	 */
	protected $aLabelledAvailableValues = null;

	/**
	 * Constructor of BsFilterCategory class
	 * @param BsDiagram $oDiagram Instance of diagram the filter is used with.
	 */
	public function __construct( $oDiagram ) {
		parent::__construct( $oDiagram );
	}
	
	/**
	 * Gets list with all available values
	 * @return array List of strings 
	 */
	public function getAvailableValues() {
		return $this->aAvailableValues;
	}
	
	/**
	 * Gets list with all available values and internationalized labels
	 * @return array List of key => label pairs 
	 */
	public function getLabelledAvailableValues() {
		// This function is expensive so let's apply some caching
		// Might also be a candidate for Memcache
		if ( !is_null( $this->aLabelledAvailableValues ) ) {
			return $this->aLabelledAvailableValues;
		} else {
			$this->aLabelledAvailableValues = array();
		}
		foreach ( $this->aAvailableValues as $sValue ) {
			$this->aLabelledAvailableValues[$sValue] = $sValue;
		}
		return $this->aLabelledAvailableValues;
	}
	
	/*
	 * Returns description of active filter
	 * @return string
	 */
	public function getActiveFilterText() {
		$this->getActiveValues();
		$aI18NValues = array();
		foreach ( $this->aActiveValues as $sValue ) {
			$aI18NValues[] = $sValue;
		}
		return implode( ", ", $aI18NValues );
	}
	
	/**
	 * Retrieves filter value from HTTP request
	 */
	public function getValueFromRequest() {
		global $wgRequest;
		$this->aActiveValues = $wgRequest->getArray( $this->getParamKey(), array() );
	}

	public function getValueFromTaskData( $oTaskData ) {
		if ( isset( $oTaskData->{$this->getParamKey()} ) ) {
			$this->aActiveValues = $oTaskData->{$this->getParamKey()};
		}
	}
	/**
	 * Gets a list of selected filter values
	 * @return array List of strings
	 */
	public function getActiveValues() {
		if ( !is_null( $this->aActiveValues ) ) {
			return $this->aActiveValues;
		} else {
			$this->getValueFromRequest();
			return $this->aActiveValues;
		}
	}

	/**
	 * Checks if a given value is active
	 * @param string $sValue The value to check
	 * @return bool
	 */
	public function isActiveValue( $sValue ) {
		$this->getActiveValues();
		if ( is_array( $this->aActiveValues ) ) {
			return in_array( $sValue, $this->aActiveValues );
		}
		return false;
	}
}