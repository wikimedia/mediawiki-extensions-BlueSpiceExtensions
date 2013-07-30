<?php
/**
 * Renders the Statistics dialogue page 3.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: view.DiagramStepIII.php 6577 2012-09-24 09:46:57Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the Statistics dialogue page 3.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class ViewDiagramStepIII extends ViewBaseElement {

	/**
	 * List of available diagrams.
	 * @var array (String)Key => (BsDiagram) diagram
	 */
	protected $aDiagramList;
	//protected $aFilterList;
	/**
	 * Key of currently selected diagram (if any)
	 * @var string
	 */
	protected $oActiveDiagram;
	/**
	 * List of active filters. 
	 * Currently not used.
	 * @var array List of filters.
	 */
	protected $aActiveFilters;
	/**
	 * Lower boundary of time interval.
	 * @var string Date string
	 */
	protected $sFrom;
	/**
	 * Upper boundary of time interval.
	 * @var string Date string
	 */
	protected $sTo;
	/**
	 * Diagram mode
	 * @var string values are constants defined in BsDiagram.
	 */
	protected $sMode;
	/**
	 * List of possibly available grains
	 * @var array List of strings
	 */
	protected $aAvailableGrains;
	/**
	 * Name of active grain
	 * @var string Active grain
	 */
	protected $sActiveGrain;
	/**
	 * File path and name of diagram file
	 * @var string Path 
	 */
	protected $sFilename;
	/**
	 * Rendered HTML of list view
	 * @var string Rendered HTML
	 */
	protected $sList = false;

	/**
	 * Constructor of ViewDiagramStepIII class
	 */
	public function  __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sOut  = $this->renderStepIII();
		return $sOut;
	}

	/**
	 * Setter for $aDiagramList
	 * @param array $aDiagramList List of available diagrams: (String)Key => (BsDiagram) diagram
	 */
	public function setDiagramList( $aDiagramList ) {
		$this->aDiagramList = $aDiagramList;
	}

	/**
	 * Setter for active diagram.
	 * @param string $sActiveDiagram Key of active diagram.
	 */
	public function setActiveDiagram( $oActiveDiagram ) {
		$this->oActiveDiagram = $oActiveDiagram;
	}

	/*
	public function setFilterList( $aFilterList ) {
	 
		$this->aFilterList = $aFilterList;
	}
	 * 
	 */

	/**
	 * Setter for active filters.
	 * @param array $aActiveFilters array of active filters keys
	 */
	public function setActiveFilters( $aActiveFilters ) {
		$this->aActiveFilters = $aActiveFilters;
	}

	/**
	 * Setter for lower time interval boundary
	 * @param string $sFrom 
	 */
	public function setFrom( $sFrom ) {
		$this->sFrom = $sFrom;
	}
	
	/**
	 * Setter for upper time interval boundary
	 * @param string $sTo 
	 */
	public function setTo( $sTo ) {
		$this->sTo = $sTo;
	}
	
	/**
	 * Setter for mode
	 * @param string $sMode values are constants defined in BsDiagram.
	 */
	public function setMode( $sMode ) {
		$this->sMode = $sMode;
	}
	
	/**
	 * Setter for available grains.
	 * @param array $aAvailableGrains List of strings
	 */
	public function setAvailableGrains( $aAvailableGrains ) {
		$this->aAvailableGrains = $aAvailableGrains;
	}
	
	/**
	 * Setter for active grain.
	 * @param string $sActiveGrain Key for grain
	 */
	public function setActiveGrain( $sActiveGrain ) {
		$this->sActiveGrain = $sActiveGrain;
	}

	/**
	 * Setter for file name.
	 * @param string $sFilename Name of diagram file
	 */
	public function setFilename( $sFilename ) {
		$this->sFilename = $sFilename;
	}
	
	/**
	 * Setter for list view.
	 * @param string $sList Rendered HTML of list view.
	 */
	public function setList( $sList ) {
		$this->sList = $sList;
	}	

	/**
	 * Generates HTML output for diagram step III
	 * @return string Rendered HTML.
	 */
	protected function renderStepIII() {

		$aOut = array();
		$aOut[] = '<form name="hw_statistics" action="'.BsAdapter::getRequestURI().'" method="post">';
		$aOut[] = '  <table border="0" width="100%">';
		$aOut[] = '    <tr><td >';
		$aOut[] = '      '.wfMsg( 'bs-statistics-step3' )."<br/>";
		$aOut[] = '      <div id="hw_statistics_nav">';
		$aOut[] = '        <a id="hw_statistics_nav_step_1" class="hw_statistics_nav_available"  href="'.BsAdapter::getRequestURI().'">'.wfMsg( 'bs-statistics-nav-step-1' ).'</a>';
		$aOut[] = '        <a id="hw_statistics_nav_step_2" class="hw_statistics_nav_available" onclick="document.hw_statistics.submit()" href="#">'.wfMsg( 'bs-statistics-nav-step-2' ).'</a>';
		$aOut[] = '        <a id="hw_statistics_nav_step_3" class="hw_statistics_nav_current">'.wfMsg( 'bs-statistics-nav-step-3' ).'</a>';
		$aOut[] = '      </div>';
		$aOut[] = '    <hr/>';
		$aOut[] = '    </td></tr>';
		$aOut[] = '    <tr><td>';

		$aOut[] = '      <input type="hidden" name="hwpStatisticsStep" value="2">';
		$aOut[] = '      <input type="hidden" name="hwpDiag" value="'.$this->oActiveDiagram->getDiagramKey().'" id="hwpDiag">';
		$aOut[] = '      <input type="hidden" name="hwpFrom" value="'.$this->sFrom.'" >';
		$aOut[] = '      <input type="hidden" name="hwpTo" value="'.$this->sTo.'" >';
		$aOut[] = '      <input type="hidden" name="hwpMode" value="'.$this->sMode.'" id="hwpMode">';
		$aOut[] = '      <input type="hidden" name="hwpGrain" value="'.$this->sActiveGrain.'" id="hwpGrain">';

		foreach ( $this->oActiveDiagram->getFilters() as $oFilter ) {

			$aOut[] = $oFilter->executeHiddenView();

		}
		//$aOut[] = '      <input type="submit" name="hwpStatistics" value="'.wfMsg( 'back-step-2').'">';
		$aOut[] = '    </td></tr>';
		$aOut[] = '  </table>';
		$aOut[] = '</form>';
		$aOut[] = '<br/>';
		if ( $this->sList !== false ) {
			$aOut[] = $this->sList;
		} else {
			$aOut[] = '<img src="'.BsCore::getInstance( 'MW' )->getAdapter()->get( 'ScriptPath' ).'/'.BsConfig::get( 'MW::Statistics::DiagramDir' ).'/'.$this->sFilename.'" />';
			
		}

		return implode( "\n", $aOut );
	}
}
