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

	private $oSearchRequest = null;

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

	public function setRequest( $oSearchRequest ) {
		$this->oSearchRequest = $oSearchRequest;
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$aSearchBoxKeyValues = array();

		$sInputFields[] = Xml::input(
			'title',
			false,
			SpecialPage::getTitleFor( 'SpecialExtendedSearch' )->getFullText(),
			array(
				'type' => 'hidden'
			)
		);

		if ( BsConfig::get( 'MW::ExtendedSearch::SearchFiles' ) ) {
			$sInputFields[] = Xml::input( 'search_files', false, 1, array( 'type' => 'hidden' ) );
		}

		$sValue = ( isset( $this->oSearchRequest->sInput ) )
			? $this->oSearchRequest->sInput
			: wfMessage( 'searchsuggest-search' )->plain();

		$sInputFields[] = Xml::input(
			'q',
			false,
			$sValue,
			array (
				'id' => 'bs-extendedsearch-inputfieldtext-specialpage',
				'defaultvalue' => wfMessage( 'searchsuggest-search' )->text()
			)
		);

		$sScope = BsConfig::get( 'MW::ExtendedSearch::DefScopeUser' );
		$sInputFields[] = Xml::input(
			'search_scope',
			false,
			$sScope,
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

		$sInputFields[] = Xml::openElement(
			'button',
			array(
				'type' => 'button',
				'title' => wfMessage( 'bs-extendedsearch-pref-scope-title' )->plain(),
				'id' => 'bs-search-button-specialpage',
				'class' => 'bs-search-button-specialpage'
			)
		);
		$sInputFields[] = $sImageTitleButton;
		$sInputFields[] = Xml::closeElement( 'button' );

		$sImageFulltextButton = Xml::element(
			'div',
			array(
				'id' => 'bs-extendedsearch-fulltextbuttonimage-specialpage',
				'class' => 'bs-extendedsearch-buttonimage-specialpage'
			),
			'',
			false
		);

		$sInputFields[] = Xml::openElement(
			'button',
			array(
				'type' => 'button',
				'title' => wfMessage( 'bs-extendedsearch-pref-scope-text' )->plain(),
				'id' => 'bs-search-fulltext-specialpage',
				'class' => 'bs-search-button-specialpage'
			)
		);
		$sInputFields[] = $sImageFulltextButton;
		$sInputFields[] = Xml::closeElement( 'button' );

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

		global $wgScript;
		$aFormAttributes = array(
			'class' => 'bs-search-form',
			'id' => 'bs-extendedsearch-form-specialpage',
			'action' => $wgScript,
			'method' => 'get'
		);

		$aForm = array();
		$aForm[] = $sLinkToExtendedPage;
		$aForm[] = Html::openElement( 'form', $aFormAttributes );
		$aForm[] = implode( "\n", $sInputFields ) . $sDivSearchDomains;
		$aForm[] = Xml::closeElement( 'form' );

		return implode( "\n", $aForm );
	}

}