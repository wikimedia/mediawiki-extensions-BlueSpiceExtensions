<?php
/**
 * Renders the ExtendedSearch search form.
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
 * This view renders the ExtendedSearch search form.
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ViewExtendedSearchFormPage extends ViewBaseElement {

	/**
	 * Fetches a View for extended options.
	 * @param string $key Type key of data to be searched
	 * @param string $headlineI18Nned Title of the options section
	 * @return ViewSearchExtendedOptionsForm View of extended options for a certain key.
	 */
	public function &getOptionsForm( $key, $headlineI18Nned ) {
		$item = new ViewSearchExtendedOptionsForm();
		$item->setOption( 'headlineI18N', $headlineI18Nned );
		$this->addItem( $item, $key );
		return $item;
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$aSearchBoxKeyValues = array();
		wfRunHooks( 'FormDefaults', array( $this, &$aSearchBoxKeyValues ) );

		$sInputFields = '';
		if ( isset( $aSearchBoxKeyValues['HiddenFields'] ) && is_array( $aSearchBoxKeyValues['HiddenFields'] ) ) {
			foreach ( $aSearchBoxKeyValues['HiddenFields'] as $key => $value ) {
				$sInputFields .= Xml::input( $key, false, $value, array( 'type' => 'hidden' ) );
			}
		}

		$sValue = ( isset( $aSearchBoxKeyValues['SearchTextFieldText'] ) )
			? $aSearchBoxKeyValues['SearchTextFieldText']
			: $aSearchBoxKeyValues['SearchTextFieldDefaultText'];

		$sInputFields .= Xml::input(
			$aSearchBoxKeyValues['SearchTextFieldName'],
			false,
			$sValue,
			array (
				'title' => $aSearchBoxKeyValues['SearchTextFieldTitle'],
				'id' => 'bs-extendedsearch-inputfieldtext-specialpage',
				'defaultvalue' => $aSearchBoxKeyValues['SearchTextFieldDefaultText'],
				'accesskey' => 'f'
			)
		);

		$sInputFields .= Xml::input(
			'search_scope',
			false,
			$aSearchBoxKeyValues['DefaultKeyValuePair'][1],
			array (
				'type' => 'hidden',
				'id' => 'bs-search-button-hidden-specialpage'
			)
		);


		$sImageTitleButton = Xml::element(
			'div',
			array(
				'id' => 'bs-extendedsearch-titlebuttonimage-specialpage',
				'class' => 'bs-extendedsearch-buttonimage-specialpage'
			),
			'',
			false
		);

		$sInputFields .= Xml::openElement(
			'button',
			array(
				'type' => 'button',
				'title' => $aSearchBoxKeyValues['TitleKeyValuePair'][1],
				'id' => 'bs-search-button-specialpage',
				'class' => 'bs-search-button-specialpage'
			)
		);
		$sInputFields .= $sImageTitleButton;
		$sInputFields .= Xml::closeElement( 'button' );

		$sImageFulltextButton = Xml::element(
			'div',
			array(
				'id' => 'bs-extendedsearch-fulltextbuttonimage-specialpage',
				'class' => 'bs-extendedsearch-buttonimage-specialpage'
			),
			'',
			false
		);

		$sInputFields .= Xml::openElement(
			'button',
			array(
				'type' => 'button',
				'title' => $aSearchBoxKeyValues['FulltextKeyValuePair'][1],
				'id' => 'bs-search-fulltext-specialpage',
				'class' => 'bs-search-button-specialpage'
			)
		);
		$sInputFields .= $sImageFulltextButton;
		$sInputFields .= Xml::closeElement( 'button' );

		$sLinkToExtendedPage = Xml::element(
			'a',
			array(
				'href' => $this->getOption( 'linkToExtendedPageUri' ),
				'id' => 'bs-extendedsearch-linktoextendedpage'
			),
			wfMessage( $this->getOption( 'linkToExtendedPageMessageKey' ) )->plain(),
			false
		);

		if ( $this->hasItems() === 0 ) {
			$sDivSearchDomains = '';
		} else {
			$itemsOut = '';
			foreach ( $this->_mItems as $item ) {
				$itemsOut .= $item->execute();
			}
			$sDivSearchDomains = Xml::openElement( 'div', array( 'class' => 'bs-extendedsearch-domaindiv' ) ).
								$itemsOut . Xml::closeElement( 'div' );
		}

		$aFormAttributes = array(
			'class' => 'bs-search-form',
			'id' => 'bs-extendedsearch-form-specialpage',
			'action' => $aSearchBoxKeyValues['SearchDestination'],
			'method' => $aSearchBoxKeyValues['method']
		);

		$sForm = $sLinkToExtendedPage;
		$sForm .= Xml::openElement( 'form', $aFormAttributes ) . $sInputFields . Xml::closeElement( 'form' );
		$sForm .= $sDivSearchDomains;

		return $sForm;
	}

}