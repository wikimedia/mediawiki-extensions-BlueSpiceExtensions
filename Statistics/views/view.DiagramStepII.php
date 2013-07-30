<?php
/**
 * Renders the Statistics dialogue page 2.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: view.DiagramStepII.php 6691 2012-10-02 11:52:09Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the Statistics dialogue page 2.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class ViewDiagramStepII extends ViewBaseElement {

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
	 * Constructor of ViewDiagramStepII class
	 */
	public function  __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sOut  = $this->renderStepII();
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
	 * Generates HTML output for diagram step II
	 * @return string Rendered HTML.
	 */
	protected function renderStepII() {

		$aOut = array();
		$aOut[] = '<form name="hw_statistics" action="'.BsAdapter::getRequestURI().'" method="post">';
		$aOut[] = '  <input type="hidden" name="hwpDiag" value="'.$this->oActiveDiagram->getDiagramKey().'" id="hwpDiag">';
		$aOut[] = '  <table border="0" width="100%">';
		
		$aOut[] = '    <tr><td colspan="2">';
		$aOut[] = '      '.wfMsg( 'bs-statistics-step2' )."<br/>";
		$aOut[] = '      <div id="hw_statistics_nav">';
		$aOut[] = '        <a id="hw_statistics_nav_step_1" class="hw_statistics_nav_available" href="#" onclick="location.href=\''.BsAdapter::getRequestURI().'\';">'.wfMsg( 'bs-statistics-nav-step-1' ).'</a>';
		$aOut[] = '        <span id="hw_statistics_nav_step_2" class="hw_statistics_nav_current" href="#">'.wfMsg( 'bs-statistics-nav-step-2' ).'</span>';
		$aOut[] = '        <a id="hw_statistics_nav_step_3" class="hw_statistics_nav_available" onclick="document.hw_statistics.submit()" href="#">'.wfMsg( 'bs-statistics-nav-step-3' ).'</a>';
		$aOut[] = '      </div>';
		$aOut[] = '    <hr/>';
		$aOut[] = '    </td></tr>';
		$aOut[] = '    <tr><td>';
		$aOut[] = '      '.wfMsg( 'bs-statistics-diagram' );
		$aOut[] = '    </td><td>';
		$aOut[] = '      '.$this->oActiveDiagram->getTitle();
		$aOut[] = '    </td></tr>';

		$aOut[] = '<tr>';
		$aOut[] = '<td colspan="2">';
		$aOut[] = '<fieldset class="hw_statistics_section">';
		$aOut[] = '  <legend>'.wfMsg( 'bs-statistics-depiction' ).'</legend>';
		$aOut[] = '</fieldset>';
		$aOut[] = '</td>';
		$aOut[] = '</tr>';

		$aOut[] = '      <td>'.wfMsg( 'bs-statistics-mode' ).'</td>';
		$aOut[] = '      <td>';
		$sTempOut = '        <input type="radio" name="hwpMode" value="'.BsDiagram::MODE_ABSOLUTE.'" id="hwpModeNormal"';
		if ( $this->sMode == BsDiagram::MODE_ABSOLUTE ) $sTempOut .= ' checked="checked"';
		$sTempOut .= '>'.wfMsg( 'bs-statistics-normal' );
		$sTempOut .= '&nbsp;&nbsp;';
		$sTempOut .= '<input type="radio" name="hwpMode" value="'.BsDiagram::MODE_AGGREGATED.'" id="hwpModeAggregated"';
		if (  $this->sMode == BsDiagram::MODE_AGGREGATED ) $sTempOut .= ' checked="checked"';
		$sTempOut .= '>'.wfMsg( 'bs-statistics-aggregated' );
		if ( $this->oActiveDiagram->isListable() ) {
			$sTempOut .= '&nbsp;&nbsp;';
			$sTempOut .= '<input type="radio" name="hwpMode" value="'.BsDiagram::MODE_LIST.'" id="hwpModeList"';
			if (  $this->sMode == BsDiagram::MODE_LIST ) $sTempOut .= ' checked="checked"';
			$sTempOut .= '>'.wfMsg( 'bs-statistics-list' );
		}
		$aOut[] = $sTempOut;
		$aOut[] = '      </td>';
		$aOut[] = '    </tr>';

		$aOut[] = '    <tr>';
		$aOut[] = '      <td>'.wfMsg( 'bs-statistics-grain').'</td>';
		$aOut[] = '      <td>';
		$aOut[] = '        <select name="hwpGrain">';
		foreach ( $this->aAvailableGrains as $sGrain ) {
			$sTempOut = '';
			$sTempOut .= '          <option value="'.$sGrain.'"';
			if ( $sGrain == $this->sActiveGrain ) $sTempOut .= ' selected="selected"';
			$sTempOut .= '>'.wfMsg( 'bs-statistics-' . $sGrain ).'</option>';
			$aOut[] = $sTempOut;
		}
		$aOut[] = '        </select>';
		$aOut[] = '      </td>';
		$aOut[] = '    </tr>';

		$aOut[] = '<tr>';
		$aOut[] = '<td colspan="2">';
		$aOut[] = '<fieldset class="hw_statistics_section">';
		$aOut[] = '  <legend>'.wfMsg( 'bs-statistics-filters' ).'</legend>';
		$aOut[] = '</fieldset>';
		$aOut[] = '</td>';
		$aOut[] = '</tr>';

		$aOut[] = '    <tr>';
		$aOut[] = '      <td width="10%">'.wfMsg( 'bs-statistics-from' ).'</td>';
		$aOut[] = '      <td width="90%">';
		$aOut[] = '        <input type="text" name="hwpFrom" value="'.$this->sFrom.'" id="hwpFrom">';
		$aOut[] = '      </td>';
		$aOut[] = '    </tr>';

		$aOut[] = '    <tr>';
		$aOut[] = '      <td>'.wfMsg( 'bs-statistics-to' ).'</td>';
		$aOut[] = '      <td>';
		$aOut[] = '        <input type="text" name="hwpTo" value="'.$this->sTo.'" id="hwpTo">';
		$aOut[] = '      </td>';
		$aOut[] = '    </tr>';

		$aOut[] = '    <tr>';


		foreach ( $this->oActiveDiagram->getFilters() as $oFilter ) {
			/*
			if ( isset( $f['fillFilterCallback'] ) ) {
				$f['values'] = $f['fillFilterCallback']();
			}
			*/
			$aOut[] = '    <tr>';
			$aOut[] = '      <td valign="top">'.$oFilter->getLabel().'</td>';
			$aOut[] = '      <td>';
			$aOut[] = $oFilter->executeFormView();
			$aOut[] = '      </td>';
			$aOut[] = '    </tr>';
		}


		$aOut[] = '    <tr>';
		$aOut[] = '      <td colspan="2">'."<hr/>";
		//$aOut[] = '        <input type="button" name="hwpStatisticsCancel" value="'.wfMsg( 'back-step-1').'" onclick="location.href=\''.BsAdapter::getRequestURI().'\';">';
		$aOut[] = '        <input type="hidden" name="hwpStatisticsStep" value="3">';
		//$aOut[] = '        <input type="submit" name="hwpStatistics" value="'.wfMsg( 'continue-step-3').'">';
		$aOut[] = '      </td>';
		$aOut[] = '    </tr>';

		$aOut[] = '  </table>';
		$aOut[] = '</form>';

		return implode( "\n", $aOut );
	}
}
