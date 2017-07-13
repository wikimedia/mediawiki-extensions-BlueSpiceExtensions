<?php
/**
 * Reads a data source for Statistics for BlueSpice.
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
 * Reads a data source for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
abstract class StatsDataProvider {
	/**
	 * Condition to match
	 * @var string Currently regular expression or SQL statement.
	 */
	public $match = false;
	/**
	 * Counts occurrences in a certain interval
	 * @param Interval $interval
	 * @return int Number of occurrences
	 */
	abstract function countInInterval( $oInterval );
}