<?php
/**
 * Renders the Readers special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    $Id: SpecialReaders.class.php 9950 2013-06-26 14:58:43Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage ResponsibleEditors
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Readers SpecialPage
 * @package BlueSpice_Extensions
 * @subpackage Readers
 */
class SpecialReaders extends BsSpecialPage {

	function __construct() {
		parent::__construct( 'Readers', 'viewreaders' );
	}

	function execute( $sParameter ) {
		parent::execute( $sParameter );
		$oRequestedTitle = null;

		if ( !empty( $sParameter ) ) {
			$oRequestedTitle = Title::newFromText( $sParameter );
			if ( $oRequestedTitle->exists() && $oRequestedTitle->getNamespace() != NS_USER ) {
				$sOut = $this->renderReadersGrid( $oRequestedTitle );
			} else {
				$oErrorView = new ViewTagErrorList();
				$oErrorView->addItem( new ViewTagError( wfMessage( 'bs-readers-article-does-not-exist' )->plain() ) );
				$sOut = $oErrorView->execute();
			}
		} else {
			$oErrorView = new ViewTagErrorList();
			$oErrorView->addItem( new ViewTagError( wfMessage( 'bs-readers-emptyinput' )->plain() ) );
			$sOut = $oErrorView->execute();
		}

		$oOut = $this->getOutput();

		if ( $oRequestedTitle === null ) {
			$oOut->setPageTitle( $oOut->getPageTitle() );
		} else {
			$oOut->setPageTitle( $oOut->getPageTitle().': '.$oRequestedTitle->getFullText() );
			$oOut->addHtml(
				'<script type="text/javascript">
					bsReadersTitle = "'.$oRequestedTitle->getPrefixedText().'";
				</script>'
			);
		}

		$oOut->addHTML( $sOut );
		$this->getOutput()->addModules( 'ext.bluespice.readers.specialreaders' );
	}

	private function renderReadersGrid( $oTitle ) {

		$sOut = '<div id="bs-readers-grid"></div>';
		return $sOut;
	}

}