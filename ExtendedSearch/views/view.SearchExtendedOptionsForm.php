<?php
/**
 * Renders the ExtendedSearch extended options page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/**
 * Hierarchically constructed:
 * Form
 *  Table
 * 	 Inputfields etc.
 * Thus recursively assembled
 */
/**
 * This view renders the ExtendedSearch extended options page.
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ViewSearchExtendedOptionsForm extends ViewBaseElement {

	/**
	 * This method actually generates the output
	 * @return String HTML output
	 */
	public function execute( $aParams = false ) {
		$itemsOut = '';
		foreach ( $this->_mItems as $item ) {
			$itemsOut .= $item->execute();
		}

		$sDivBoxes = Xml::openElement( 'div', array( 'class' => 'bs-extendedsearch-optionsformentries', 'style' => 'display: inline-block;' ) ).
					$itemsOut.
					Xml::closeElement( 'div' );

		return $sDivBoxes;
	}

	/**
	 * Renders a single options box.
	 * @param String $sFieldName Name of options field, e.g. namespace
	 * @param String $sI18nKeyName Internationalized name of field for display.
	 * @param String $sUrlFieldName Link of field.
	 * @return ViewSearchMultivalueField View that describes the output.
	 */
	public function &getBox( $sFieldName, $sI18nKeyName, $sUrlFieldName ) {
		$oVmvf = $this->getViewMultiValueField( $sFieldName );
		$oVmvf->setOptions( array(
				'i18nKeyName'  => $sI18nKeyName,
				'urlFieldName' => $sUrlFieldName
			)
		);

		return $oVmvf;
	}

	/**
	 * Actually prepares Field for getBox function.
	 * @param String $sKey Name of options field, e.g. namespace
	 * @return ViewSearchMultivalueField View that describes the output.
	 */
	protected function &getViewMultiValueField( $sKey ) {
		if ( isset( $this->_mItems[$sKey] ) ) return $this->_mItems[$sKey];

		$oRes = new ViewSearchMultivalueField();
		$this->addItem( $oRes, $sKey );

		return $oRes;
	}

}
