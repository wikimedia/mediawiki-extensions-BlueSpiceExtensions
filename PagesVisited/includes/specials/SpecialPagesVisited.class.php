<?php
/**
 * Renders the PagesVisited special page.
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
 * @subpackage PagesVisited
 */
class SpecialPagesVisited extends BsSpecialPage {

	function __construct() {
		parent::__construct( 'PagesVisited', 'viewreaders' );
	}

	function execute( $sParameter ) {
		parent::execute( $sParameter );

		if ( !empty( $sParameter ) ) {
			$oRequestedTitle = Title::newFromText( $sParameter );
			if ( $oRequestedTitle->getNamespace() == NS_USER ) {
				$sOut = $this->renderPagesVisitedGrid( $oRequestedTitle );
			} else {
				$oErrorView = new ViewTagErrorList();
				$oErrorView->addItem( new ViewTagError( wfMessage( 'bs-readers-article-does-not-exist' )->plain() ) );
				$sOut = $oErrorView->execute();
			}
		} else {
			$oErrorView = new ViewTagErrorList();
			$oErrorView->addItem( new ViewTagError( wfMessage( 'bs-pagesvisited-emptyinput' )->plain() ) );
			$sOut = $oErrorView->execute();
		}

		$oOutputPage = $this->getOutput();

		if ( !empty( $sParameter ) ) {
			$oOutputPage->setPageTitle( $oOutputPage->getPageTitle().': '.$oRequestedTitle->getFullText() );
			$oUser = User::newFromName( $oRequestedTitle->getText() );
			$oOutputPage->addHtml(
				'<script type="text/javascript">
					bsPagesVisitedUserID = "'.$oUser->getId().'";
				</script>'
			);
		} else {
			$oOutputPage->setPageTitle( $oOutputPage->getPageTitle() );
		}

		$oOutputPage->addHTML( $sOut );
		$oOutputPage->addModules( 'ext.bluespice.pagesvisited.special' );
	}

	private function renderPagesVisitedGrid( $oTitle ) {
		$sOut = '<div id="bs-pagesvisited-grid"></div>';
		return $sOut;
	}

}