<?php
/**
 * Special page for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v0.1
 * FIRST CHANGES
 */
/**
 * Special page class for ExtendedSearch for MediaWiki
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class SpecialExtendedSearch extends BsSpecialPage {

	/**
	 * Reference to instance of SearchOptions class.
	 * @var Object SearchOptions class.
	 */
	protected $oSearchOptions = null;
	/**
	 * Reference to instance of base class.
	 * @var Object ExtendedSearchBase class.
	 */
	protected $oExtendedsearchBase = null;
	/**
	 * Reference to instance of SearchUriBuilder.
	 * @var Object SearchUriBuilder class.
	 */
	protected $oUriBuilder = null;

	/**
	 * Constructor of SpecialExtendedSearch class
	 */
	function __construct() {
		parent::__construct( 'SpecialExtendedSearch' );
		$this->oExtendedsearchBase = ExtendedSearchBase::getInstance();
		$this->oSearchOptions = SearchOptions::getInstance();
		$this->oUriBuilder = SearchUriBuilder::getInstance();
	}

	/**
	 * Actually render the page content.
	 * @param string $sParameter URL parameters to special page.
	 * @return string Rendered HTML output.
	 */
	function execute( $sParameter ) {
		parent::execute( $sParameter );

		$this->getOutput()->addModuleStyles( 'ext.bluespice.extendedsearch.specialpage.style' );
		$this->getOutput()->addModules( 'ext.bluespice.extendedsearch.specialpage' );

		$oSearchformView = new ViewBaseElement();
		$oSearchform  = new ViewExtendedSearchFormPage();
		$aMonitor = array();

		if ( $this->oSearchOptions->getOptionBool( 'bExtendedForm' ) ) {
			$aMonitor['linkToExtendedPageMessageKey'] = 'bs-extendedsearch-specialpage-form-return-to-simple';
			$aMonitor['linkToExtendedPageUri'] = $this->oUriBuilder->buildUri( SearchUriBuilder::ALL );

			$oSearchformView->addItem( $this->renderExtendedForm( $oSearchform ) );
			$bShowExtendedForm = true;
		} else {
			$aMonitor['linkToExtendedPageMessageKey'] = 'bs-extendedsearch-specialpage-form-expand-to-extended';
			$aMonitor['linkToExtendedPageUri'] = $this->oUriBuilder->buildUri( SearchUriBuilder::ALL | SearchUriBuilder::EXTENDED );
		}

		$oSearchform->setOptions( $aMonitor );
		$oSearchformView->addItem( $oSearchform );

		$this->getOutput()->addHTML( $oSearchformView->execute() );

		if ( isset( $bShowExtendedForm ) && $bShowExtendedForm == 'true' ) return;

		try {
			$oView = $this->oExtendedsearchBase->renderSpecialpage();
		} catch ( BsException $e ) {
			if ( $e->getMessage() == 'redirect' ) return;
			throw $e;
		}

		//		- Initialisieren des Suchmanagers
		//			=> Abgabe des Ablaufs an ExtendedSearch
		//			- Zusammenbau des Requests
		//		- Abfragen: Suchformular oben oder unten
		//		- Ausgabe der views:
		//			- output Suchformular
		//			- output Suchergebnis

		$this->getOutput()->addHTML( $oView->execute() );
	}

	/**
	 * Renders extended search options form.
	 * @param array $aMonitor List that contains form view.
	 * @return ViewBaseElement View that describes search options.
	 */
	public function renderExtendedForm( &$aMonitor ) {

		global $wgContLang;

		$aHiddenFieldsInForm = array();
		$aHiddenFieldsInForm['search_asc'] = $this->oSearchOptions->getOption( 'asc' );
		$aHiddenFieldsInForm['search_order'] = $this->oSearchOptions->getOption( 'order' );// score|titleSort|type|ts
		$aHiddenFieldsInForm['search_submit'] = '1';

		$aMonitor->setOptions(
			array(
				'hiddenFields' => $aHiddenFieldsInForm,
				'files' => $this->oSearchOptions->getOption( 'files' ),
				'method' => ( ( strcasecmp( BsConfig::get( 'MW::ExtendedSearch::FormMethod' ), 'post' ) == 0 ) ? 'post' : 'get' ),
				'scope' => $this->oSearchOptions->getOption( 'scope' )
			)
		);

		$vOptionsFormWiki = $aMonitor->getOptionsForm( 'wiki', wfMessage( 'bs-extendedsearch-search-wiki' )->plain() );
		$vNamespaceBox = $vOptionsFormWiki->getBox( 'NAMESPACE-FIELD', 'bs-extendedsearch-search-namespace', 'na[]' );
		$oMwContLang = $wgContLang;
		$aMwNamespaces = $oMwContLang->getNamespaces();
		$aSelectedNamespaces = $this->oSearchOptions->getOption( 'namespaces' );

		if ( BsConfig::get( 'MW::SortAlph' ) ) asort( $aMwNamespaces );

		foreach ( $aMwNamespaces as $namespace ) {
			$iNsIndex = $oMwContLang->getNsIndex( $namespace );
			if ( $iNsIndex < 0 ) continue;
			if ( $iNsIndex == 0 ) $namespace = wfMessage( 'bs-extendedsearch-articles' )->plain();
			$vNamespaceBox->addEntry(
				$iNsIndex,
				array(
					'value' => $iNsIndex,
					'text' => $namespace,
					'selected' => in_array( (string) $iNsIndex, $aSelectedNamespaces )
				)
			);
		}

		$checkboxSearchFilesAttributes = array(
			'type' => 'checkbox',
			'id' => 'bs-extendedsearch-checkbox-searchfiles'
		);

		if ( BsConfig::get( 'MW::ExtendedSearch::SearchFiles' ) || $this->oSearchOptions->getOption( 'files' ) ) {
			$checkboxSearchFilesAttributes['checked'] = 'checked';
		}
		$checkboxSearchFiles = Xml::input( 'search_files', false, 1, $checkboxSearchFilesAttributes );
		$checkboxSearchFiles .= wfMessage( 'bs-extendedsearch-files' )->plain();

		$vNamespaceBox->dirtyAppend( '<br />'.$checkboxSearchFiles );

		$dbr = wfGetDB( DB_SLAVE ); // needed for categories and editors

		$catRes = $dbr->select(
				array( 'category' ),
				array( 'cat_id', 'cat_title' ),
				'',
				null,
				array( 'ORDER BY' => 'cat_title asc' )
		);
		if ( $dbr->numRows( $catRes ) != 0 ) {
			$vCategoryBox = $vOptionsFormWiki->getBox( 'CATEGORY-FIELD', 'bs-extendedsearch-search-category', 'ca[]' );
			$aSelectedCategories = $this->oSearchOptions->getOption( 'cats' );
			while ( $catRow = $dbr->fetchObject( $catRes ) ) {
				$vCategoryBox->addEntry(
					$catRow->cat_title,
					array(
						'value' => $catRow->cat_title,
						'text' => $catRow->cat_title,
						'selected' => in_array( $catRow->cat_title, $aSelectedCategories )
					)
				);
			}
		}

		$dbr->freeResult( $catRes );

		$vEditorsBox = $vOptionsFormWiki->getBox( 'EDITORS-FIELD', 'bs-extendedsearch-search-editors', 'ed[]' );
		$edRes = $dbr->select(
			array( 'revision' ),
			array( 'DISTINCT rev_user_text' ),
			'',
			null,
			array( 'ORDER BY' => 'rev_user_text' )
		);
		$aSelectedEditors = $this->oSearchOptions->getOption( 'editor' );
		while ( $edRow = $dbr->fetchObject( $edRes ) ) {
			$oUser = User::newFromName( $edRow->rev_user_text );
			if ( !is_object( $oUser ) ) continue;

			$vEditorsBox->addEntry(
				$oUser->getName(),
				array(
					'value' => $oUser->getName(),
					'text' => $oUser->getName(),
					'selected' => in_array( $oUser->getName(), $aSelectedEditors )
				)
			);
		}

		$dbr->freeResult( $edRes );

		$vbe = new ViewBaseElement();
		$vbe->setAutoElement( false );
		return $vbe;
	}

}