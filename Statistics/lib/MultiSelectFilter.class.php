<?php
/**
 * Describes a multi select filter filter for Statistics for BlueSpice.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: MultiSelectFilter.class.php 7857 2012-12-20 12:46:48Z mglaser $
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Describes a multi select filter filter for Statistics for BlueSpice.
 * @package    BlueSpice_Extensions
 * @subpackage Statistics 
 */
abstract class BsMultiSelectFilter extends BsSelectFilter {

	/**
	 * Constructor of BsFilterCategory class
	 * @param BsDiagram $oDiagram Instance of diagram the filter is used with.
	 * @param array $aDefaultValues List of strings
	 */
	public function __construct( $oDiagram, $aDefaultValues=null ) {
		parent::__construct( $oDiagram, $aDefaultValues );
		if ( !is_null( $aDefaultValues ) ) {
			$this->aDefaultValues = $aDefaultValues;
		}
	}

	/**
	 * Retrieves filter value from HTTP request
	 */
	public function getValueFromRequest() {
		$this->aActiveValues = BsCore::getParam( 
						$this->getParamKey(), 
						//isset($diagram[$sDiagType]['filters_default'][$f])?isset($diagram[$sDiagType]['filters_default'][$f]):null, 
						array(),
						BsPARAMTYPE::ARRAY_STRING|BsPARAM::REQUEST|BsPARAMOPTION::DEFAULT_ON_ERROR 
					);
	}
	
	/*
	 * Returns description of active filter
	 * @return string
	 */
	public function getActiveFilterText() {
		$this->getActiveValues();
		$aI18NValues = array();
		foreach ( $this->aActiveValues as $sValue ) {
			$sValueText = preg_replace( "/^(<|&lt;)?(.*?)(>|&gt;)?$/", "$2", wfMsg( $sValue ) );
			$aI18NValues[] = $sValueText;
		}
		return join( ", ", $aI18NValues );
	}

	/**
	 * Renders form element for filter
	 * @return Rendered HTML
	 */
	public function executeFormView() {
		BsExtensionMW::getInstanceFor( 'MW::Statistics' )->registerView( 'ViewMultiSelectFilter' );
		$oMultiSelectFilterView = new ViewMultiSelectFilter();
		$oMultiSelectFilterView->setFilter( $this );
		return $oMultiSelectFilterView->execute();
	}
	
	/**
	 * Renders hidden form element for filter
	 * @return Rendered HTML
	 */
	public function executeHiddenView() {
		BsExtensionMW::getInstanceFor( 'MW::Statistics' )->registerView( 'ViewMultiSelectFilter' );
		$oMultiSelectFilterView = new ViewMultiSelectFilter();
		$oMultiSelectFilterView->setFilter( $this );
		$oMultiSelectFilterView->setHidden();
		return $oMultiSelectFilterView->execute();
	}
}