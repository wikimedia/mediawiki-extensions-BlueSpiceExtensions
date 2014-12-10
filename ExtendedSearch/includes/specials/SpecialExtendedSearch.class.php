<?php
/**
 * Special page for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Special page
 */
class SpecialExtendedSearch extends BsSpecialPage {

	/**
	 * Constructor of SpecialExtendedSearch class
	 */
	public function __construct() {
		parent::__construct( 'SpecialExtendedSearch' );
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

		$oExtendedsearchBase = new ExtendedSearchBase( $this );
		$oView = $oExtendedsearchBase->renderSpecialpage();

		$this->getOutput()->addHTML( $oView->execute() );

	}

}