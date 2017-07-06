<?php
/**
 * Reads data from database for Statistics for BlueSpice.
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
 * Reads data from database for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class PostGreSQLDbReader extends StatsDataProvider {
	
	/**
	 * Database server host
	 * @var string 
	 */
	public $host;
	/**
	 * Database user
	 * @var string
	 */
	public $user;
	/**
	 * Database password
	 * @var string
	 */
	public $pass;
	/**
	 * Database name
	 * @var string
	 */
	public $db;
	/**
	 * Stores current database connection
	 * @var resource
	 */
	private $conn = false;

	/**
	 * Counts occurrences in a certain interval
	 * @param Interval $interval
	 * @return int Number of occurrences
	 */
	public function countInInterval( $interval, $listable=false ) {
		$count = 0;
		$this->connect();

		$sql = $this->match;

		$sql = str_replace("@period", "BETWEEN '".$interval->getStartTS("Y-m-d H:i:s+00")."' AND '".$interval->getEndTS("Y-m-d H:i:s+00")."' ", $sql);
		$sql = str_replace("@start", " '".$interval->getStartTS("Y-m-d H:i:s+00")."' ", $sql);
		$sql = str_replace("@end", " '".$interval->getEndTS("Y-m-d H:i:s+00")."' ", $sql); 
		$sql = str_replace("stats_ts","to_timestamp(stats_ts,'YYYYMMDDHH24MISS')",$sql);
                
                $res = pg_query($this->conn, $sql);
		$row = pg_fetch_array($res);

		$item = $row[0];
		//if ($listable)
			//$count = mysql_num_rows($res);
		//else
			$count = $item;
		return $count;
	}

    /**
	 * Counts number of unique values that match a specific criterium
	 * @param Interval $interval
	 * @return array List of unique values
	 */
	public function uniqueValues( $interval, $listable=true, $count=2 ) {
		$uniqueValues = array();
		if (!$listable) return $uniqueValues;

		$this->connect();

		$sql = $this->match;

		$sql = str_replace("@period", "BETWEEN '".$interval->getStartTS("Y-m-d H:i:s+00")."' AND '".$interval->getEndTS("Y-m-d H:i:s+00")."' ", $sql);
		$sql = str_replace("@start", " '".$interval->getStartTS("Y-m-d H:i:s+00")."' ", $sql);
		$sql = str_replace("@end", " '".$interval->getEndTS("Y-m-d H:i:s+00")."' ", $sql);
                
                $sql = str_replace("stats_ts","to_timestamp(stats_ts,'YYYYMMDDHH24MISS')",$sql);

		$res = pg_query($this->conn, $sql);

		while ($row = pg_fetch_array($res)) {
			$rowArr = array();
			for ($i=0; $i<$count; $i++ )
				$rowArr[] = $row[$i];
			$uniqueValues[] = $rowArr;
		}
		



		return $uniqueValues;
	}

	/**
	 * Connect to database. This method stores the connection and reuses it.
	 * @return resource Database connection
	 */
	private function connect()
	{
		if ($this->conn) return $this->conn;
		$this->conn = pg_connect("host={$this->host} dbname={$this->db} user={$this->user} password={$this->pass}");
		return $this->conn;
	}

}
