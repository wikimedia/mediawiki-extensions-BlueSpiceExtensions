<?php
/**
 * Prepares data and chart for Statistics for BlueSpice.
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
 * Prepares data and chart for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 */
class BsCharting {

	/**
	 * Prepares data for list output
	 * @param BsDiagram $oDiagram The diagram to prepare chart for
	 * @param StatsDataProvider $provider Provider class for chart data
	 */
	public static function prepareList($oDiagram, $provider) {
		// TODO MRG (13.12.10 17:29): make configurable
		set_time_limit(120);
		$intervals = Interval::getIntervalsFromDiagram( $oDiagram );
		//$oDiagram->addFilterText( wfMessage( 'bs-statistics-from-to', $oDiagram->getStartTime(), $oDiagram->getEndTime() )->plain() );
		//$oDiagram->addFilterText( "<br/>".wfMessage( 'bs-statistics-mode' )->plain().": ".wfMessage( $oDiagram->getMessage() )->plain() );
		// PostgreSQL-Check (uses mwuser instead of user)
		global $wgDBtype;
		// Pls. keep the space after user, otherwise, user_groups is also replaced
		$sql = $oDiagram->getSQL();
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__user', '#__mwuser', $sql );
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__mwuser_', '#__user_', $sql );
		global $wgDBprefix;
		$sql = str_replace( "#__", $wgDBprefix, $sql );

		foreach ( $oDiagram->getFilters() as $oFilter ) {
			$sFilterSql = $oFilter->getSql();
			$sql = str_replace( $oFilter->getSqlKey(), $sFilterSql, $sql );
			$sActiveFilterText = $oFilter->getActiveFilterText();
			if ( !empty( $sActiveFilterText ) ) $oDiagram->addFilterText( "<br/>".$oFilter->getLabel().": ".$sActiveFilterText );
		}
		$provider->match = $sql;

		$starttime = strtotime( $oDiagram->getStartTime() );
		// end at last second of the day, hence +24*60*60-1 seconds
		$endtime = strtotime( $oDiagram->getEndTime() ) + 84599;

		$interval = new Interval();
		$interval->setStartTS($starttime);
		$interval->setEndTS($endtime);

		$oDiagram->setData( $provider->uniqueValues($interval, $oDiagram->isListable(), count($oDiagram->getListLabel() ) ) );

		//arsort($diag['data']);

		return $oDiagram;
	}

	/**
	 * Retrieve chart data per date interval
	 * @param StatsDataProvider $dataprovider Provider class for chart data
	 * @param bool $aggregated Should counts be added
	 * @param array $intervals List of Interval objects
	 * @param bool $countable If true, result can be listet.
	 * @return array List of numbers
	 */
	public static function getDataPerDateInterval($dataprovider, $aggregated, $intervals, $countable=false)
	{
		$data=array();
		$sum = 0;
		if ($aggregated == 'aggregated')
		{
			$intervalBefore = new Interval();
			$intervalBefore->setStartTS(0);
			$intervalBefore->setEndTS($intervals[0]->getStartTS());
			$sum = $dataprovider->countInInterval($intervalBefore, $countable);
		}
		foreach ($intervals as $interval)
		{
			$item = $dataprovider->countInInterval($interval, $countable);
			if ($aggregated=='aggregated')
			{
				$sum += $item;
				$data[] = $sum;
			}
			else $data[] = $item;
		}
		return $data;
	}
}
