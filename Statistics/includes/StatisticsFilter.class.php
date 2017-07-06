<?php
/**
 * Describes a filter for Statistics for BlueSpice.
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
 * Describes a filter for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
abstract class BsStatisticsFilter {
	
	/**
	 * Prefix for URL params
	 */
	const FILTER_PARAM_PREFIX = 'hwpFilter';
	/**
	 * Prefix for SQL placeholder
	 */
	const FILTER_SQL_PREFIX = '@';
	
	/**
	 * Label for Fiter
	 * @var string 
	 */
	protected $sLabel;
	/**
	 * Filter type (Currently not used)
	 * @var string
	 */
	protected $sType;
	/**
	 * Default value for filter
	 * @var string 
	 */
	protected $sDefault;
	/**
	 * String that identifies a filter
	 * @var string 
	 */
	protected $sFilterKey;
	/**
	 * Reference to diagram that uses the filter.
	 * @var BsDiagram
	 */
	protected $oDiagram;

	/**
	 * Constructor of BsStatisticsFilter class
	 * @param BsDiagram $oDiagram Instance of diagram the filter is used with.
	 */
	public function __construct( $oDiagram ) {
		$this->oDiagram = $oDiagram;
		$this->sFilterKey = get_class( $this );
	}

	/**
	 * Returns SQL statement for data retrieval
	 * @return string SQL statement
	 */
	public abstract function getSql();
	/*
	 * Returns description of active filter
	 * @return string
	 */
	public abstract function getActiveFilterText();
	/**
	 * Retrieves active filter value from HTTP request
	 */
	public abstract function getValueFromRequest();
	/**
	 * Retrieves active filter value from API task data
	 */
	public abstract function getValueFromTaskData( $oTaskData );
	/**
	 * Checks if a given value is active
	 * @param string $sValue The value to check
	 * @return bool
	 */
	public abstract function isActiveValue( $sValue );
	
	/**
	 * Gets key for sql placehoder
	 * @return string
	 */
	public function getSqlKey() {
		return self::FILTER_SQL_PREFIX.$this->sFilterKey;
	}
	
	/**
	 * Gets key for HTTP request param
	 * @return string
	 */
	public function getParamKey() {
		return self::FILTER_PARAM_PREFIX.$this->sFilterKey;
	}
	
	/**
	 * Gets label for filter
	 * @return string 
	 */
	public function getLabel() {
		return $this->sLabel;
	}
	
}