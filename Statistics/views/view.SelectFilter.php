<?php
/**
 * Renders the Statistics select filter.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: view.SelectFilter.php 6444 2012-09-10 13:04:48Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the Statistics select filter.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
class ViewSelectFilter extends ViewBaseElement {

	/**
	 * Instance of filter to render
	 * @var BsStatisticsFilter
	 */
	protected $oFilter;
	/**
	 * Should form field be hidden?
	 * @var bool 
	 */
	protected $bHidden = false;

	/**
	 * Constructor of ViewMultiSelectFilter class
	 */
	public function  __construct() {
		parent::__construct();
	}

	/**
	 * Set filter for view
	 * @param BsStatisticsFilter $oFilter Filter to render this view from.
	 */
	public function setFilter( $oFilter ) {
		$this->oFilter = $oFilter;
	}

	/**
	 * Hide filter field in output.
	 */
	public function setHidden() {
		$this->bHidden = true;
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		if ( $this->bHidden ) {
			return $this->renderHidden();
		} else {
			return $this->renderForm();
		}
	}

	/**
	 * Generates HTML output for select filter
	 * @return string Rendered HTML.
	 */
	protected function renderForm() {
		$aOut = array();
		$sTempOut = '        <select name="'.$this->oFilter->getParamKey().'"';
		$sTempOut .= ' size="5" >' ;
		$aOut[] = $sTempOut;
		foreach ( $this->oFilter->getLabelledAvailableValues() as $sKey => $sValue ) {
			$sTempOut = '<option value="'.$sKey.'"';
			if ( $this->oFilter->isActiveValue( $sKey ) ) $sTempOut .= ' selected="selected"';
			$sTempOut .= '>'.$sValue.'</option>';
			$aOut[] = $sTempOut;
		}
		$aOut[] = '        </select>';
		return join("\n", $aOut);
	}

	/**
	 * Generates HTML output for multi select filter as hidden field
	 * @return string Rendered HTML.
	 */
	protected function renderHidden() {
		$sOut = '';
		$aValue =  $this->oFilter->getActiveValues();
		$sOut = '      <input type="hidden"  name="'.$this->oFilter->getParamKey().'" value="'.$aValue[0].'">';
		
		return $sOut;
	}
}
/*
			$sTempOut = '        <select name="hwpFilter'.$flt;
			if ( $f['multiselect'] ) $sTempOut .= '[]" size="5" multiple="multiple"';
			else $sTempOut .= '"';
			$sTempOut .= '>';
			$aOut[] = $sTempOut;
			foreach ( $f['values'] as $optkey => $optval ) {
				$sTempOut = '<option value="'.$optval.'"';
				if ( is_array( $this->aActiveFilters[$flt] ) ) {
					if ( in_array( $optval, $this->aActiveFilters[$flt] ) ) $sTempOut .= ' selected="selected"';
				} else {
					if ( $optval == $this->aActiveFilters[$flt] ) $sTempOut .= ' selected="selected"';
				}
				$sTempOut .= '>'.$optkey.'</option>';
				$aOut[] = $sTempOut;
			}
			$aOut[] = '        </select>';
			
 * 
 * 
 * 
 * 
 * 	} else {
					$aOut[] = '      <input type="hidden"  name="hwpFilter'.$flt.'" value="'.$diagFilter[$flt].'">';
				}
			}
 * 
 */