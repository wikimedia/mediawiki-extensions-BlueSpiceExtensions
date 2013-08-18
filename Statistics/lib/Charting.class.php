<?php
/**
 * Prepares data and chart for Statistics for BlueSpice.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: Charting.class.php 7892 2012-12-21 10:14:59Z mglaser $
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */



/*
$apachelog = "c:\hallowiki2go\apache\logs\access.log";
$logfile = file ($apachelog);
fopen($ourFileName, 'r') or die("Can't open file");



fclose($logfile);
*/

// set path to ttf fonts
//define('TTF_DIR', '/usr/share/fonts/truetype/msttcorefonts/');
//define('TTF_DIR', '/usr/share/fonts/truetype/ttf-dejavu/');
define('TTF_DIR', dirname( __FILE__ ) . '/../fonts/');

//include("diagrams.php");

include( BSVENDORDIR."/jpgraph/src/jpgraph.php" );
include( BSVENDORDIR."/jpgraph/src/jpgraph_bar.php" );
include( BSVENDORDIR."/jpgraph/src/jpgraph_line.php" );
/**
 * Prepares data and chart for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class BsCharting {

	/**
	 * Prepares data for chart output
	 * @param BsDiagram $oDiagram The diagram to prepare chart for
	 * @param StatsDataProvider $provider Provider class for chart data
	 * @param string $filename Name of file with chart image.
	 */
	public static function prepareData( $oDiagram, $provider, $filename='diagram.png' ) {
		// TODO MRG (13.12.10 17:29): make configurable
		set_time_limit( 60 );
		// TODO MRG (20.12.10 00:01): already called before
		$intervals = Interval::getIntervalsFromDiagram( $oDiagram );
		$oDiagram->addFilterText( wfMsg( 'bs-statistics-from-to', $oDiagram->getStartTime(), $oDiagram->getEndTime() ) );
		// TODO MRG (20.12.10 00:19): This should be getModeText
		// Give grep a chance to find the usages:
		// bs-statistics-aggregated, bs-statistics-grouped, bs-statistics-normal, bs-statistics-absolute
		$oDiagram->addFilterText( "\n".wfMsg( 'bs-statistics-mode' ).": ".wfMsg( 'bs-statistics-'.$oDiagram->getMode() ) );
		// PostgreSQL-Check (uses mwuser instead of user)
		global $wgDBtype;
		// Pls. keep the space after user, otherwise, user_groups is also replaced
		$sql = $oDiagram->getSQL();
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__user', '#__mwuser', $sql );
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__mwuser_', '#__user_', $sql );
		$sql = str_replace( "#__", BsCore::getInstance( 'MW' )->getAdapter()->get( 'DBprefix' ), $sql );
		
		foreach ( $oDiagram->getFilters() as $oFilter ) {
			$sFilterSql = $oFilter->getSql();
			$sql = str_replace( $oFilter->getSqlKey(), $sFilterSql, $sql );
			$sActiveFilterText = $oFilter->getActiveFilterText();
			if ( !empty( $sActiveFilterText ) ) $oDiagram->addFilterText( "\n".$oFilter->getLabel().": ".$sActiveFilterText );
		}

		$provider->match = $sql;
		$oDiagram->setData( BsCharting::getDataPerDateInterval( $provider, $oDiagram->getMode(), $intervals, $oDiagram->isListable() ) );
		foreach ( $intervals as $interval ) $labels[] = $interval->getLabel();
		$labels[] = "";
		$oDiagram->setLabelsX( $labels );
		BsCharting::drawChart( $oDiagram, $filename );
	}

	/**
	 * Prepares data for list output
	 * @param BsDiagram $oDiagram The diagram to prepare chart for
	 * @param StatsDataProvider $provider Provider class for chart data
	 */
	public static function prepareList($oDiagram, $provider) {
		// TODO MRG (13.12.10 17:29): make configurable
		set_time_limit(120);
		$intervals = Interval::getIntervalsFromDiagram( $oDiagram );
		$oDiagram->addFilterText( wfMsg( 'bs-statistics-from-to', $oDiagram->getStartTime(), $oDiagram->getEndTime() ) );
		// Give grep a chance to find the usages:
		// bs-statistics-aggregated, bs-statistics-grouped, bs-statistics-normal, bs-statistics-absolute
		$oDiagram->addFilterText( "<br/>".wfMsg( 'bs-statistics-mode' ).": ".wfMsg( 'bs-statistics-'.$oDiagram->getMode() ) );
		// PostgreSQL-Check (uses mwuser instead of user)
		global $wgDBtype;
		// Pls. keep the space after user, otherwise, user_groups is also replaced
		$sql = $oDiagram->getSQL();
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__user', '#__mwuser', $sql );
		if ( $wgDBtype == 'postgres' ) $sql = str_replace( '#__mwuser_', '#__user_', $sql );
		$sql = str_replace( "#__", BsCore::getInstance( 'MW' )->getAdapter()->get( 'DBprefix' ), $sql );

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


		return BsCharting::drawTable($oDiagram);
	}

	/*
	//Deprecated

	function getDataPerMonth($sql_tmpl, $aggregated)
	{
		$data=array();
		$sum = 0;
		$year_start = 2008;
		for ($i=10; $i<=12; $i++)
		{
			$month = ($i % 12)+1;
			if ($month < 10) $month = "0".$month;
			//else $month = $i;
			$year = $year_start+floor(($i)/12);
			$sql = str_replace("@stamp", "'".$year.$month."31'", $sql_tmpl);
			$sql = str_replace("@time", "LIKE '".$year.$month."%'", $sql);
			#echo $sql;
			$res = mysql_query($sql);
			$row = mysql_fetch_array($res);
			if ($aggregated) 
			{
				$sum += $row[0];
				$item = $sum;
			}
			else $item = $row[0];
			$data[] = $item;
		}
		return $data;
	}
	*/

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

	/**
	 * Sends diagram to Charting module
	 * @param BsDiagram $oDiagram The diagram to prepare chart for
	 * @param string $filename File the diagram is written to.
	 */
	public static function drawChart($oDiagram, $filename="diagram.png") {
		//print_r($oDiagram->getData());
		if ( $oDiagram->getFormatX() ) 
			$formatx = $oDiagram->getFormatX();
		else
			$formatx = '%01.0f';

		$graph = new Graph( BsConfig::get( 'MW::Statistics::DiagramWidth' ), BsConfig::get( 'MW::Statistics::DiagramHeight' ) );

		/*
		if (is_array($oDiagram->getData()[0]))
		
		{
			$bpl = array();
			for ($i=0; $i<sizeof($diagram['data']); $i++)
			{
				$bar=new BarPlot($diagram['data'][$i]);
				$bar->SetFillColor($diagram['color'][$i]);
				$bar->value->SetFormat($formatx);
				$bar->SetFillGradient($diagram['color'][$i],"gray@0.5",GRAD_HOR);
				$bar->value->Show(); 
				$bar->value->SetFont(FF_DV_SANSSERIF);
				$bar->SetShadow("gray", 4, 2, true);
				$bpl[] = $bar;
			}
			$accbar = new AccBarPlot($bpl);
			$accbar->SetShadow("gray", 4, 2, true);
			$graph->Add($accbar);
		}
		else
		 *
		 */
		{
			switch( BsConfig::get( 'MW::Statistics::DiagramType' ) ) {
				case 'line' :
					$lineplot1=new LinePlot( $oDiagram->getData() );
					$lineplot1->SetWeight(3);
					// if there is only one datapoint, autoscaling will not work
					if ( count( $oDiagram->getData() ) > 1 ) {
						$graph->SetScale('textlin');
					} else {
						$data = $oDiagram->getData();
						$graph->SetScale( 'textlin', 0,floor($data[0]*1.3),0,1 );
					}
					break;
				case 'bar'  :
				default     : 
					$lineplot1=new BarPlot( $oDiagram->getData() );
					$lineplot1->SetFillColor("#AEC8E8@0.2");
					$graph->SetScale('textlin');
					break;
			}

			//
			$lineplot1->SetColor("#AEC8E8@0.2");
			$lineplot1->value->SetFormat( $formatx );
			//$lineplot1->SetFillGradient("blue@0.5","gray@0.5",GRAD_HOR);
			$lineplot1->value->Show(); 
			$lineplot1->value->SetFont(FF_DV_SANSSERIF);
			//$lineplot1->SetShadow("gray", 4, 2, true);
			$graph->Add($lineplot1);
		}
		$graph->xaxis->SetTickLabels( $oDiagram->getLabelsX() );

		$graph->title->Set( $oDiagram->getTitle() );
		// Doesn't work, hence php wordwrap is used some lines above when the text is set.
		//$graph->subtitle->setWordWrap(100);
		$graph->subtitle->Set( wordwrap($oDiagram->getDescription(), 100, "\n") );
		$graph->footer->left->Set( $oDiagram->getFilterText() );
		//$graph->title->SetFont(FF_DV_SANSSERIF);
		$graph->xaxis->title->Set( $oDiagram->getTitleX() );
		$graph->yaxis->title->Set( $oDiagram->getTitleY() );

		$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD, 13);
		$graph->subtitle->SetFont(FF_DV_SANSSERIF,FS_NORMAL, 10);
		$graph->footer->left->SetFont(FF_DV_SANSSERIF,FS_NORMAL, 9);
		$graph->footer->left->SetWordWrap(120);
		$graph->footer->left->SetMargin(15);
		$graph->footer->left->SetParagraphAlign('left');
		$graph->yaxis->title->SetFont(FF_DV_SANSSERIF,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_DV_SANSSERIF,FS_BOLD);

		$graph->xaxis->title->SetMargin(22);
		$graph->yaxis->title->SetMargin(15);

		$graph->xaxis->SetFont(FF_DV_SANSSERIF);
		$graph->yaxis->SetFont(FF_DV_SANSSERIF);
		$graph->xaxis->SetLabelangle(45);
		$graph->xaxis->SetLabelAlign('center', 'top');
		$graph->yaxis->scale->SetGrace(10);

		$graph->img->SetMargin(50,50,50,120);
		$graph->SetFrame(false);

		//$graph->Stroke();

		$graph->Stroke(BsConfig::get( 'MW::Statistics::DiagramDir' ).'/'.$filename);
	}

	/**
	 * Renders a table with result information
	 * @param BsDiagram $oDiagram The diagram to prepare chart for
	 * @return string Rendered HTML
	 */
// CR MRG (28.06.11 01:23): View
	public static function drawTable( $oDiagram ) {
		$data = $oDiagram->getData();
		$label = $oDiagram->getListLabel();
		$out = '';
		$out .= "<div style='font-size:14px;font-weight:bold;text-align:center;'>".$oDiagram->getTitle()."</div>";
		$out .= "<div style='font-size:12px;text-align:center;'>".$oDiagram->getDescription()."</div>";
		$out .= "<div style='font-size:11px;text-align:left;'>";
		$out .= $oDiagram->getFilterText();
		$out .= "<br>n = ".count( $data );
		$out .= "</div>";
		$out .= "<table width='80%' id='ExtendedSearchTable' border='0' cellspacing='0' cellpadding='4' class='sortable'>";
		if ($label) {
			$out .= '<tr>';
			foreach ($label as $col)
				$out .= '<th>'.$col.'</th>';
			$out .= '</tr>';
		}

		foreach ($data as $value)
		{
			$out .= '<tr>';
			foreach ($value as $col)
				$out .= '<td>'.utf8_encode($col).'</td>';
			$out .= '</tr>';
		}
		$out .= '</table>';
		return $out;
	}
}
