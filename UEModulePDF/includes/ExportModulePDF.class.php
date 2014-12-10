<?php
/**
 * BsExportModulePDF.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage UEModulePDF
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * UniversalExport BsExportModulePDF class.
 * @package BlueSpice_Extensions
 * @subpackage UEModulePDF
 */
class BsExportModulePDF implements BsUniversalExportModule {

	/**
	 * Implementation of BsUniversalExportModule interface. Uses the
	 * Java library xhtmlrenderer to create a PDF file.
	 * @param SpecialUniversalExport $oCaller
	 * @return array array( 'mime-type' => 'application/pdf', 'filename' => 'Filename.pdf', 'content' => '8F3BC3025A7...' );
	 */
	public function createExportFile( &$oCaller ) {
		global $wgUser, $wgRequest;
		$aPageParams = $oCaller->aParams;

		$aPageParams['title']      = $oCaller->oRequestedTitle->getPrefixedText();
		$aPageParams['article-id'] = $oCaller->oRequestedTitle->getArticleID();
		$aPageParams['oldid']      = $wgRequest->getInt( 'oldid', 0 );
		if( BSConfig::get('MW::UEModulePDF::SuppressNS') ) {
			$aPageParams['display-title'] = $oCaller->oRequestedTitle->getText();
		}
		//If we are in history mode and we are relative to an oldid
		$aPageParams['direction'] = $wgRequest->getVal('direction', '');
		if( !empty( $aPageParams['direction'] ) ) {
			$oCurrentRevision = Revision::newFromId( $aPageParams['oldid'] );
			switch( $aPageParams['direction'] ) {
				case 'next': $oCurrentRevision = $oCurrentRevision->getNext();
					break;
				case 'prev': $oCurrentRevision = $oCurrentRevision->getPrevious();
					break;
				default: break;
			}
			if( $oCurrentRevision !== null ) {
				$aPageParams['oldid'] = $oCurrentRevision->getId();
			}
		}

		//Get Page DOM
		$aPage = BsPDFPageProvider::getPage( $aPageParams );

		//Prepare Template
		$aTemplateParams = array(
			'path'     => BsConfig::get( 'MW::UEModulePDF::TemplatePath' ),
			'template' => BsConfig::get( 'MW::UEModulePDF::DefaultTemplate' ),
			'language' => $wgUser->getOption( 'language', 'en' ),
			'meta'     => $aPage['meta']
		);

		//Override template param if needed. The override may come from GET (&ue[template]=...) or from a tag (<bs:ueparams template="..." />)
		//TODO: Make more generic
		if(!empty( $oCaller->aParams['template'] ) ) {
			$aTemplateParams['template'] = $oCaller->aParams['template'];
		}

		$aTemplate = BsPDFTemplateProvider::getTemplate( $aTemplateParams );

		//Combine Page Contents and Template
		$oDOM = $aTemplate['dom'];

		//Add the bookmarks
		$aTemplate['bookmarks-element']->appendChild(
			$aTemplate['dom']->importNode( $aPage['bookmark-element'], true )
		);
		$aTemplate['title-element']->nodeValue = $oCaller->oRequestedTitle->getPrefixedText();

		$aContents = array(
			'content' => array( $aPage['dom']->documentElement )
		);
		wfRunHooks( 'BSUEModulePDFBeforeAddingContent', array( &$aTemplate, &$aContents, $oCaller, &$aPage ) );

		$oContentTags = $oDOM->getElementsByTagName( 'content' );
		$i = $oContentTags->length - 1;
		while( $i > -1 ){
			$oContentTag = $oContentTags->item($i);
			$sKey = $oContentTag->getAttribute('key');
			if( isset($aContents[$sKey] ) ) {
				foreach( $aContents[$sKey] as $oNode ) {
					$oNode = $oDOM->importNode( $oNode, true );
					$oContentTag->parentNode->insertBefore( $oNode, $oContentTag );
				}
			}
			$oContentTag->parentNode->removeChild($oContentTag);
			$i--;
		}

		$oCaller->aParams['document-token']   = md5( $oCaller->oRequestedTitle->getPrefixedText() ).'-'.$oCaller->aParams['oldid'];
		$oCaller->aParams['soap-service-url'] = BsConfig::get( 'MW::UEModulePDF::PdfServiceURL' );
		$oCaller->aParams['backend-url']      = BsConfig::get( 'MW::UEModulePDF::PdfServiceURL' ); //Duplicate to replace 'soap-service-url' in future
		$oCaller->aParams['resources']        = $aTemplate['resources'];

		wfRunHooks( 'BSUEModulePDFBeforeCreatePDF', array( $this, $oDOM, $oCaller ) );

		//Prepare response
		$aResponse = array(
			'mime-type' => 'application/pdf',
			'filename'  => '%s.pdf',
			'content'   => ''
		);

		if ( RequestContext::getMain()->getRequest()->getVal( 'debugformat', '' ) == 'html' ) {
			$aResponse['content'] = $oDOM->saveXML( $oDOM->documentElement );
			$aResponse['mime-type'] = 'text/html';
			$aResponse['filename'] = sprintf(
				'%s.html',
				$oCaller->oRequestedTitle->getPrefixedText()
			);
			$aResponse['disposition'] = 'inline';
			return $aResponse;
		}

		$sBackendClass = BsConfig::get('MW::UEModulePDF::Backend');
		$oPDFBackend = new $sBackendClass( $oCaller->aParams );
		$aResponse['content'] = $oPDFBackend->createPDF( $oDOM );

		$aResponse['filename'] = sprintf(
			$aResponse['filename'],
			$oCaller->oRequestedTitle->getPrefixedText()
		);

		return $aResponse;
	}

	/**
	 * Implementation of BsUniversalExportModule interface. Creates an overview
	 * over the PdfExportModule
	 * @return ViewExportModuleOverview
	 */
	public function getOverview() {
		$oModuleOverviewView = new ViewExportModuleOverview();

		$oModuleOverviewView->setOption( 'module-title', wfMessage( 'bs-uemodulepdf-overview-title' )->plain() );
		$oModuleOverviewView->setOption( 'module-description', wfMessage( 'bs-uemodulepdf-overview-desc' )->plain() );
		$oModuleOverviewView->setOption( 'module-bodycontent', '' );

		$oWebserviceStateView = new ViewBaseElement();
		$oWebserviceStateView->setTemplate(
			'{LABEL}: <span style="font-weight: bold; color:{COLOR}">{STATE}</span>'
			);

		$sWebServiceUrl = BsConfig::get( 'MW::UEModulePDF::PdfServiceURL' );
		$sWebserviceState = wfMessage( 'bs-uemodulepdf-overview-webservice-state-not-ok' )->plain();
		$sColor = 'red';
		if( BsConnectionHelper::testUrlForTimeout( $sWebServiceUrl ) ) {
			$sColor = 'green';
			$sWebserviceState = wfMessage( 'bs-uemodulepdf-overview-webservice-state-ok' )->plain();

			$oWebserviceUrlView = new ViewBaseElement();
			$oWebserviceUrlView->setTemplate(
				'{LABEL}: <a href="{URL}" target="_blank">{URL}</a><br/>'
			);
			$oWebserviceUrlView->addData(array(
				'LABEL' => wfMessage( 'bs-uemodulepdf-overview-webservice-webadmin' )->plain(),
				'URL' => $sWebServiceUrl,
			));
			$oModuleOverviewView->addItem( $oWebserviceUrlView );
		}

		$oWebserviceStateView->addData(array(
			'LABEL' => wfMessage( 'bs-uemodulepdf-overview-webservice-state' )->plain(),
			'COLOR' => $sColor,
			'STATE' => $sWebserviceState
		));

		$oModuleOverviewView->addItem( $oWebserviceStateView );

		return $oModuleOverviewView;
	}
}
