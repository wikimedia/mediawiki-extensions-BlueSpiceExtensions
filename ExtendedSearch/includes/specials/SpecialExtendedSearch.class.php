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
	 * Reference to instance of base class.
	 * @var Object ExtendedSearchBase class.
	 */
	protected $oExtendedsearchBase = null;


	/**
	 * Constructor of SpecialExtendedSearch class
	 */
	public function __construct() {
		parent::__construct( 'SpecialExtendedSearch' );
		$this->oExtendedsearchBase = new ExtendedSearchBase( $this );
	}

	/**
	 * Actually render the page content.
	 * @param string $sParameter URL parameters to special page.
	 * @return string Rendered HTML output.
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$this->getOutput()->addModuleStyles( 'ext.bluespice.extendedsearch.specialpage.style' );
		$this->getOutput()->addModules( 'ext.bluespice.extendedsearch.specialpage' );

		$oView = $this->oExtendedsearchBase->renderSpecialpage();

		$this->getOutput()->addHTML( $oView->execute() );

	}

}