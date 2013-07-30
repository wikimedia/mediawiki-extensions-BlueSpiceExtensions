<?php
/**
 * Special page for PageAccess for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Marc Reymann <reymann@hallowelt.biz>
 * @version    $Id$
 * @package    BlueSpice_PageAccess
 * @subpackage PageAccess
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class SpecialPageAccess extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'PageAccess' );
	}

	public function execute( $par ) {
		parent::execute( $par );
		$oOutputPage = $this->getOutput();

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
				array( 'page_props' ),
				array( 'pp_page', 'pp_value' ),
				array( 'pp_propname' => 'bs-page-access' )
		);

		#TODO: Beautify output! Ext grid?
		while( $row = $dbr->fetchObject( $res ) ) {
			$iPageID = $row->pp_page;
			$oTitle = Title::newFromID( $iPageID );
			$oOutputPage->addHtml( Linker::link( $oTitle, null, array(), array(), array( 'known' ) ) );
			$oOutputPage->addHtml( " (" . $row->pp_value . ")" );
			$oOutputPage->addHtml( "<br/>" );
		}
	}
}
