<?php
/**
 * Describes a diagram for Statistics for BlueSpice.
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
 * Describes a diagram for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
abstract class BsDiagram {
	/**
	 * Diagram time resoution: days
	 */
	const GRAIN_DAY = 'd';
	/**
	 * Diagram time resoution: (calendar) weeks
	 */
	const GRAIN_WEEK = 'W';
	/**
	 * Diagram time resoution: months
	 */
	const GRAIN_MONTH = 'm';
	/**
	 * Diagram time resoution: years
	 */
	const GRAIN_YEAR = 'y';
	
	/**
	 * Data shall be retrieved from database
	 */
	const DATASOURCE_DATABASE = 1;
	/**
	 * Data shall be retrieved fromlogfile
	 */
	const DATASOURCE_LOGFILE = 2;
	
	/**
	 * Data shall be presented as is
	 */
	const MODE_ABSOLUTE = "absolute";
	/**
	 * Data shall be aggregated (added up)
	 */
	const MODE_AGGREGATED = "aggregated";
	/**
	 * Data shall be presented as a list
	 */
	const MODE_LIST = "list";
	
	
	/**
	 * Lower boundary of date interval
	 * @var string
	 */
	protected $sStartTime;
	/**
	 * Upper boundary of date interval
	 * @var string
	 */
	protected $sEndTime;
	/**
	 * Grain to use
	 * @var string BsDiagram::GRAIN_
	 */
	protected $sActualGrain;
	/**
	 * Source of data
	 * @var int BsDiagram::DATASOURCE_
	 */
	protected $iDataSource;
	/**
	 * Mode of data presentation
	 * @var string BsDiagram::MODE_
	 */
	protected $sMode;
	/**
	 * Message data presentation
	 * @var string 
	 */
	protected $sMessage;
	/**
	 * List of available filters for this diagram
	 * @var array List of BsStatisticsFilter
	 */
	protected $aFilters = array();
	/**
	 * List of data points
	 * @var array List of integers
	 */
	protected $aData = array();
	/**
	 * List of x axis labels
	 * @var array List of strings
	 */
	protected $aLabelsX = array();
	/**
	 * Labels for x shall be generated from this
	 * @var string time format item, e.g. "M" for month (Jan, Feb...)
	 */
	protected $sModLabel;
	/**
	 * Headings for column headers
	 * @var array 
	 */
	protected $sListLabel;
	/**
	 * Table columns to select when in diagram mode.
	 * @var string
	 */
	protected $sSqlWhatForDiagram;
	/**
	 * Options for SQL statement when in diagram mode.
	 * @var string
	 */
	protected $sSqlOptionsForDiagram;
	/**
	 * Table columns to select when in list mode.
	 * @var string
	 */
	protected $sSqlWhatForList;
	/**
	 * Options for SQL statement when in list mode.
	 * @var string
	 */
	protected $sSqlOptionsForList;
	/**
	 * Condition for select string.
	 * @var string
	 */
	protected $sSqlWhatFromWhere;
	/**
	 * Can this diagram be presented in list mode.
	 * @var bool
	 */
	protected $bListable;
	/**
	 * Format of point labels
	 * @var string Format as defined by jpGraph 
	 */
	protected $sFormatX;
	/**
	 * Overall title of the diagram
	 * @var string 
	 */
	protected $sTitle;
	/**
	 * Title for x axis
	 * @var string 
	 */
	protected $sTitleX;
	/**
	 * Title for y axis
	 * @var string 
	 */
	protected $sTitleY;
	/**
	 * Description of diagram
	 * @var string 
	 */
	protected $sDescription;
	/**
	 * Description of active filters.
	 * @var string 
	 */
	protected $sFilterText = '';

	/**
	 * Constructor of BsDiagram class
	 */
	public function __construct() {}

	/**
	 * Get current SQL statement for data retrieval
	 * @return string SQL statement
	 */
	public function getSql() {
		$sql = "SELECT ";
		$sql .= $this->isList()?$this->sSqlWhatForList:$this->sSqlWhatForDiagram;
		$sql .= " ";
		$sql .= $this->sSqlFromWhere;
		$sql .= $this->isList()?$this->sSqlOptionsForList:$this->sSqlOptionsForDiagram;
		return $sql;
	}
	
	/**
	 * Is this diagram a list
	 * @return bool 
	 */
	public function isList() {
		return ( $this->sMode == BsDiagram::MODE_LIST );
	}
	
	/**
	 * Can this diagram be presented in list mode.
	 * @return bool 
	 */
	public function isListable() {
		return $this->bListable;
	}
	
	/**
	 * Set ower boundary of date interval
	 * @param string $sStartTime Date string
	 */
	public function setStartTime( $sStartTime ) {
		$this->sStartTime = $sStartTime;
	}
	
	/**
	 * Get lower boundary of date interval
	 * @return string Date string
	 */
	public function getStartTime() {
		return $this->sStartTime;
	}
	
	/**
	 * Set u boundary of date interval
	 * @param string $sEndTime Date string
	 */
	public function setEndTime( $sEndTime ) {
		$this->sEndTime = $sEndTime;
	}
	
	/**
	 * Get upper boundary of date interval
	 * @return string Date string
	 */
	public function getEndTime() {
		return $this->sEndTime;
	}
	
	/**
	 * Set grain to use
	 * @param string $sActualGrain BsDiagram::GRAIN_
	 */
	public function setActualGrain( $sActualGrain ) {
		$this->sActualGrain = $sActualGrain;
	}
	
	/**
	 * Get grain to use
	 * @return string BsDiagram::GRAIN_
	 */
	public function getActualGrain() {
		return $this->sActualGrain;
	}
	
	/**
	 * Set mode of data presentation
	 * @param string $sMode BsDiagram::MODE_
	 */
	public function setMode( $sMode ) {
		$this->sMode = $sMode;
	}
	
	/**
	 * Set mode of data presentation
	 * @param string $sMode BsDiagram::MODE_
	 */
	public function setMessage( $sMessage ) {
		$this->sMessage = $sMessage;
	}
	
	/**
	 * Get mode of data presentation
	 * @return string  BsDiagram::MODE_
	 */
	public function getMode() {
		return $this->sMode;
	}
	/**
	 * Get mode of data presentation
	 * @return string  BsDiagram::MODE_
	 */
	public function getMessage() {
		return $this->sMessage;
	}
	
	/**
	 * Set list of available filters for this diagram
	 * @param array $aFilters List of BsStatisticsFilter
	 */
	public function setFilters( $aFilters ) {
		$this->aFilters = $aFilters;
	}
	
	/**
	 * Get list of available filters for this diagram
	 * @return array List of BsStatisticsFilter
	 */
	public function getFilters() {
		return $this->aFilters;
	}
	
	/**
	 * Adds a filter to list of available filters
	 * @param BsStatisticsFilter $oFilter 
	 */
	public function addFilter( $oFilter ) {
		$this->aFilters[$oFilter->getParamKey()] = $oFilter;
	}
	
	/**
	 * Returns an active filter
	 * @param string $sFilterKey ParamKey of filter
	 * @return BsStatisticsFilter Instance of filter.
	 */
	public function getFilter( $sFilterKey ) {
		return $this->aFilters[$sFilterKey];
	}
	
	/**
	 * Sets list of data points
	 * @param array $aData List of integers
	 */
	public function setData( $aData ) {
		$this->aData = $aData;
	}
	
	/**
	 * Gets list of data points
	 * @return array List of integers 
	 */
	public function getData() {
		return $this->aData;
	}
	
	/**
	 * Sets list of x axis labels
	 * @param array $aLabelsX List of strings
	 */
	public function setLabelsX( $aLabelsX ) {
		$this->aLabelsX = $aLabelsX;
	}
	
	/**
	 * Gets list of x axis labels
	 * @return array List of strings 
	 */
	public function getLabelsX() {
		return $this->aLabelsX;
	}
	
	/**
	 * Sets format of point labels
	 * @param string $sFormatX Format as defined by jpGraph
	 */
	public function setFormatX( $sFormatX ) {
		$this->sFormatX = $sFormatX;
	}
	
	/**
	 * Gets format of point labels
	 * @return string Format as defined by jpGraph
	 */
	public function getFormatX() {
		return $this->sFormatX;
	}
	
	/**
	 * Sets overall title of the diagram
	 * @param string $sTitle 
	 */
	public function setTitle( $sTitle ) {
		$this->sTitle = $sTitle;
	}
	
	/**
	 * Gets overall title of the diagram
	 * @param string 
	 */
	public function getTitle() {
		return $this->sTitle;
	}
	
	/**
	 * Sets title for x axis
	 * @param string $sTitleX 
	 */
	public function setTitleX( $sTitleX ) {
		$this->sTitleX = $sTitleX;
	}
	
	/**
	 * Gets title for x axis
	 * @param string
	 */
	public function getTitleX() {
		return $this->sTitleX;
	}	
	
	/**
	 * Sets title for y axis
	 * @param string $sTitleY 
	 */
	public function setTitleY( $sTitleY ) {
		$this->sTitleY = $sTitleY;
	}
	
	/**
	 * Gets title for y axis
	 * @return string
	 */
	public function getTitleY() {
		return $this->sTitleY;
	}
	
	/**
	 * Sets description of diagram
	 * @param string $sDescription 
	 */
	public function setDescription( $sDescription ) {
		$this->sDescription = $sDescription;
	}
	
	/**
	 * Gets description of diagram
	 * @return string
	 */
	public function getDescription() {
		return $this->sDescription;
	}
	
	/**
	 * Adds a description of active filter.
	 * @param string $sFilterText 
	 */
	public function addFilterText( $sFilterText ) {
		$this->sFilterText .= $sFilterText;
	}
	
	/**
	 * Gets description of active filters.
	 * @return string 
	 */
	public function getFilterText() {
		return $this->sFilterText;
	}	
	
	/* this is a read only property
	public function setDataSource( $iDataSource ) {
		//$this->iDataSource = $iDataSource;
	}
	 * 
	 */
	
	/**
	 * Gets source of data
	 * @return int BsDiagram::DATASOURCE_
	 */
	public function getDataSource() {
		return $this->iDataSource;
	}
	
	/**
	 * Sets format labels for x shall be generated from
	 * @param string $sModLabel time format item, e.g. "M" for month (Jan, Feb...)
	 */
	public function setModLabel( $sModLabel ) {
		$this->sModLabel = $sModLabel;
	}
	
	/**
	 * Gets format labels for x shall be generated from
	 * @return string time format item, e.g. "M" for month (Jan, Feb...)
	 */
	public function getModLabel() {
		return $this->sModLabel;
	}
	
	/**
	 * Gets headings for column headers
	 * @return array
	 */
	public function getListLabel() {
		return $this->sListLabel;
	}
	
	/**
	 * Gets the diagram key
	 * @return string 
	 */
	public function getDiagramKey() {
		return get_class( $this );
	}
}