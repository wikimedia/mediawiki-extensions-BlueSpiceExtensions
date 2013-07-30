<?php
/**
 * Reads the apache log for Statistics for BlueSpice.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: ApacheLogReader.class.php 2788 2011-07-04 12:15:12Z mglaser $
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Reads the apache log for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class ApacheLogReader extends StatsDataProvider {
	
	/**
	 * Path to log file.
	 * @var string
	 */
	public  $pathToLogfile;
	/**
	 * handle of the logfile
	 * @var resource
	 */ 
    private $_logfile;
	/**
	 * Debug output
	 * @var bool
	 */
    public  $debug = false;
	/**
	 * Condition to match
	 * @var sring Regular expression
	 */
    public  $match;


	/**
	 * Counts occurrences in a certain interval
	 * @param Interval $interval
	 * @return int Number of occurrences
	 */
	public function countInInterval($interval)
    {
        $count = 0;

        $file = $this->getLogfile();
        $line = fgets($file);
        while (!feof($file))
        {
            if (preg_match("/80\.153\.10\.122/", $line))
            {
                $line = fgets($file);
                continue;
            }
            
            if (preg_match("/\[(.*?):/", $line, $matches))
            {
                //print_r($matches);
                $ts = str_replace("/", " ", $matches[1]);
                $ts = strtotime($ts);
                if ($ts <  $interval->getStartTS() || $ts >  $interval->getEndTS())
                {
                    $line = fgets($file);
                    continue;
                }

                if (preg_match($this->match, $line)) $count++;
            }
            $line = fgets($file);

        }

        $this->closeLogfile();
        return $count;
    }
    /**
	 * Counts the number of lines that match a certain criteria
	 * @return int Number of occurrences
	 */
	public function countLines()
    {
        $count = 0;

        $file = $this->getLogfile();
        $line = fgets($file);

        while (!feof($file))
        {
            if (preg_match("/80\.153\.10\.122/", $line))
            {
                $line = fgets($file);
                continue;
            }

            if (preg_match("/\/Dec.*POST.*index.php\?title=Spezial:Anmelden&action=submitlogin.*/", $line)) $count++;

            $line = fgets($file);
        }

        $this->closeLogfile();
        return $count;
    }

    /**
	 * Counts number of unique values that match a specific criterium
	 * @param Interval $interval
	 * @return array List of unique values
	 */
	public function uniqueValues($interval)
    {
        $uniqueValues = array();
        $file = $this->getLogfile();
        $line = fgets($file);

        while (!feof($file))
        {
            if (preg_match("/80\.153\.10\.122/", $line))
            {
                $line = fgets($file);
                continue;
            }

            $matches = false;

            if (preg_match("/\[(.*?):/", $line, $matches))
            {
                //print_r($matches);
                $ts = str_replace("/", " ", $matches[1]);
                $ts = strtotime($ts);
                if ($ts <  $interval->getStartTS() || $ts >  $interval->getEndTS())
                {
                    $line = fgets($file);
                    continue;
                }

                if (preg_match($this->match, $line, $matches))
                {
                    //print_r($matches);
                    if (sizeof($matches)>0) $uniqueValues[$matches[1]]++;
                }
            }

            $line = fgets($file);
        }
        return $uniqueValues;
    }

    /**
	 * Get a logfile ready for output
	 * @return resource Log file handle
	 */
	private function getLogfile()
    {
        if ($this->_logfile) return $this->_logfile;
        $this->_logfile = fopen($this->pathToLogfile, "r");

        $this->debug("Returning file ", $this->_logfile);
        return $this->_logfile;
    }

    /**
	 * Close logfile after reading
	 */
	private function closeLogfile()
    {
        fclose($this->_logfile);
        $this->_logfile = false;
    }

    /**
	 * Generate debugging output. Currently not functional.
	 * @param string $msg Message to display
	 * @param string $var Variable to debug.
	 */
	private function debug($msg, $var)
    {
        //echo $msg.$var."<br/>";
    }
}