<?php
/**
 * Describes an interval for Statistics for BlueSpice.
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
 * Describes an interval for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 */
class Interval {

	/**
	 * Shortcut for getting a series of Intervals from a diagram
	 * @param object Diagram $oDiagram
	 * @return object Interval
	 */
	public static function getIntervalsFromDiagram( $oDiagram ) {
		return Interval::getIntervals( $oDiagram->getActualGrain(), $oDiagram->getStartTime(), $oDiagram->getEndTime(), $oDiagram->getModLabel() );
	}

	/**
	 * Get a list of intervals for a given period
	 * @param string $step Interval descriptor as defined in BsDiagram
	 * @param string $offset Date string
	 * @param string $limit Date string
	 * @param string $modLabel Date format item, e.g. "M" for "Jan, Feb"
	 * @return Interval
	 */
	public static function getIntervals( $step="W", $offset='05/01/2007', $limit='01/31/2009', $modLabel=false ) {
		$intervals=array();
		$starttime = strtotime($offset);
		$endtime = strtotime($limit);

		$interval = new Interval();
		$interval->setStartTS($starttime);

		$old = idate($step, $starttime);
		if ($modLabel)
			$interval->setLabel(date($modLabel, $starttime));
		else
			$interval->setLabel($old);
		$oldts = $starttime;

		for ($ts=$starttime; $ts<=$endtime; $ts+=86400)
		{
			$oldts = $ts-1;
			$cur = idate($step, $ts);
			if ($old != $cur)
			{
				$interval->setEndTS($oldts);
				$intervals[] = $interval;
				$interval = new Interval();
				$interval->setStartTS($ts);
				if ($modLabel)
					$interval->setLabel(date($modLabel, $ts));
				else
					$interval->setLabel($cur);
				//echo "<br/>-----------------------";
			}
			$old = $cur;

			//echo "<br/>".$oldts." :: ".date("d.m.YHis", $oldts-1);
		}
		$oldts = $oldts+86400;
		$interval->setEndTS($oldts);
		$intervals[] = $interval;

		//var_dump($intervals);
		return $intervals;
	}

    /**
	 * Lower interval boundary
	 * @var string Date string
	 */
	private $startTS;
    /**
	 * Upper interval boundary
	 * @var string Date string
	 */
    private $endTS;
    /**
	 * Date label for this interval
	 * @var string E.g. "Jan"
	 */
    private $label;

    /**
	 * Set lower interval boundary
	 * @param string $timestamp Date string
	 */
	public function setStartTS($timestamp)
    {
        $this->startTS = $timestamp;
    }

   /**
	 * Set upper interval boundary
	 * @param string $timestamp Date string
	 */
    public function setEndTS($timestamp)
    {
        $this->endTS = $timestamp;
    }

   /**
	 * Set date label for this interval
	 * @param string $label  E.g. "Jan"
	 */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
	 * Get lower interval boundary
	 * @param string $format Date format string
	 * @return string Formatted date string
	 */
    public function getStartTS($format=false)
    {
        if ($format)
            return date($format, $this->startTS);
        else
            return $this->startTS;
    }

    /**
	 * Get upper interval boundary
	 * @param string $format Date format string
	 * @return string Formatted date string
	 */
    public function getEndTS($format=false)
    {
        if ($format)
            return date($format, $this->endTS);
        else
            return $this->endTS;
    }

    /**
	 * Get label for interval
	 * @return string Date label
	 */
    public function getLabel()
    {
        return $this->label;
    }

}