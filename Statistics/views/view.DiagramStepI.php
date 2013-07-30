<?php
/**
 * Renders the Statistics dialogue page 1.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: view.DiagramStepI.php 6444 2012-09-10 13:04:48Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the Statistics dialogue page 1.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class ViewDiagramStepI extends ViewBaseElement {
	
	/**
	 * List of available diagrams.
	 * @var array (String)Key => (BsDiagram) diagram
	 */
	protected $aDiagramList;
	/**
	 * Key of currently selected diagram (if any)
	 * @var string
	 */
	protected $sActiveDiagram;

	/**
	 * Constructor of ViewDiagramStepI class
	 */
	public function  __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sOut  = $this->renderStepI();
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
	public function setActiveDiagram( $sActiveDiagram ) {
		$this->sActiveDiagram = $sActiveDiagram;
	}

	/**
	 * Generates HTML output for diagram step I
	 * @return string Rendered HTML.
	 */
	protected function renderStepI() {

		$aOut = array();
		$aOut[] = '<form name="hw_statistics" action="'.BsAdapter::getRequestURI().'" method="post">';
		$aOut[] = '  <table border="0" width="100%">';
		$aOut[] = '    <tr><td colspan="3">';
		$aOut[] = '      '.wfMsg( 'bs-statistics-step1' ).'<br/>';
		$aOut[] = '      <div id="hw_statistics_nav">';
		$aOut[] = '        <span id="hw_statistics_nav_step_1" class="hw_statistics_nav_current">'.wfMsg( 'bs-statistics-nav-step-1' ).'</span>';
		$aOut[] = '        <a id="hw_statistics_nav_step_2" class="hw_statistics_nav_available" href="#" onclick="document.hw_statistics.submit()">'.wfMsg( 'bs-statistics-nav-step-2' ).'</a>';
		$aOut[] = '        <span id="hw_statistics_nav_step_3" class="hw_statistics_nav_disabled">'.wfMsg( 'bs-statistics-nav-step-3' ).'</span>';
		$aOut[] = '      </div>';
		$aOut[] = '    <hr/>';
		$aOut[] = '    </td></tr>';
		$aOut[] = '    <tr>';
		$aOut[] = '      <td width="10%" valign="top">'.wfMsg( 'bs-statistics-diagram' ).'</td>';
		$aOut[] = '      <td width="30%">';
		$aOut[] = '        <select name="hwpDiag" size="6" style="width:250px">';
		foreach ( $this->aDiagramList as $sDiagramKey => $oDiagram ) {
			$sTempOut = '';
			$sTempOut .= '        <option value="'.$oDiagram->getDiagramKey().'"';
			if ( $sDiagramKey == $this->sActiveDiagram ) {
				$sTempOut .= ' selected="selected"';
			}
			$sTempOut .= ' onclick="document.getElementById(\'bs_statistics_diag_info\').innerHTML = \''.$oDiagram->getDescription().'\';">'.$oDiagram->getTitle().'</option>';
			$aOut[] = $sTempOut;
		}
		$aOut[] = '        </select>';
		$aOut[] = '      </td>';

		$aOut[] = '      <td width="60%" valign="top">';
		$aOut[] = '        <div id="bs_statistics_diag_info">'.($this->sActiveDiagram?$this->aDiagramList[$this->aActiveDiagram]->getDescription():'').'</div>';
		$aOut[] = '      </td>';

		$aOut[] = '    </tr>';
		$aOut[] = '    <tr>';
		$aOut[] = '      <td colspan="3">'."<hr/>";
		$aOut[] = '        <input type="hidden" name="hwpStatisticsStep" value="2">';
		//$aOut[] = '        <input type="submit" name="hwpStatistics" value="'.wfMsg( 'continue-step-2' ).'">';
		$aOut[] = '      </td>';
		$aOut[] = '    </tr>';
		$aOut[] = '  </table>';
		$aOut[] = '</form>';

		return implode( "\n", $aOut );
	}
}
