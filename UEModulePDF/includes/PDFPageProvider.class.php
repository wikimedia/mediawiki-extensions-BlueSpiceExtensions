<?php
/**
 * BsPDFPageProvider.
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
 * UniversalExport BsPDFPageProvider class.
 * @package BlueSpice_Extensions
 * @subpackage UEModulePDF
 */
class BsPDFPageProvider {

	/**
	 * Fetches the requested pages markup, cleans it and returns a DOMDocument.
	 * @param array $aParams Needs the 'article-id' or 'title' key to be set and valid.
	 * @return array 
	 */
	public static function getPage( $aParams ) {
		wfRunHooks( 'BSUEModulePDFbeforeGetPage', array( &$aParams ) );

		$oBookmarksDOM = new DOMDocument();
		$oBookmarksDOM->loadXML('<bookmarks></bookmarks>');

		$oTitle = null;
		if( isset($aParams['article-id']) ) {
			$oTitle = Title::newFromID($aParams['article-id']);
		}
		if( $oTitle == null ){
			//HINT: This is probably the wrong place for urldecode(); Should be 
			//done by caller. I.e. BookExportModulePDF
			$oTitle = Title::newFromText(urldecode($aParams['title']));
		}
		
		$oPCP = new BsPageContentProvider();
		$oPageDOM = $oPCP->getDOMDocumentContentFor( 
			$oTitle, 
			$aParams + array( 'follow-redirects' => true )
		); // TODO RBV (06.12.11 17:09): Follow Redirect... setting or default?

		//Collect Metadata
		$aData = self::collectData( $oTitle, $oPageDOM, $aParams );

		//Cleanup DOM
		self::cleanUpDOM( $oTitle, $oPageDOM, $aParams );

		$oBookmarkNode = BsUniversalExportHelper::getBookmarkElementForPageDOM( $oPageDOM );
		//HINT: http://www.mm-newmedia.de/blog/2010/05/wrong-document-error-wtf/
		$oBookmarksDOM->documentElement->appendChild(
			$oBookmarksDOM->importNode( $oBookmarkNode, true )
		);
		
		$oDOMXPath = new DOMXPath( $oPageDOM );
		$oFirstHeading = $oDOMXPath->query( "//*[contains(@class, 'firstHeading')]" )->item(0);
		$oBodyContent  = $oDOMXPath->query( "//*[contains(@class, 'bodyContent')]" )->item(0);
		
		// TODO RBV (01.02.12 11:28): What if no TOC?
		$oTOCULElement = $oDOMXPath->query( "//*[contains(@class, 'toc')]//ul" )->item(0);
		
		if( isset($aParams['display-title'] ) ) {
			$oBookmarkNode->setAttribute( 'name', $aParams['display-title'] );
			$oFirstHeading->nodeValue = $aParams['display-title'];
			$aData['meta']['title']   = $aParams['display-title'];
		}
		
		$aPage = array(
			'resources' => $aData['resources'],
			'dom' => $oPageDOM,
			'firstheading-element' => $oFirstHeading,
			'bodycontent-element'  => $oBodyContent,
			'toc-ul-element'   => $oTOCULElement,
			'bookmarks-dom'    => $oBookmarksDOM,
			'bookmark-element' => $oBookmarkNode,
			'meta'             => $aData['meta']
		);
		
		wfRunHooks( 'BSUEModulePDFgetPage', array( $oTitle, &$aPage, &$aParams, $oDOMXPath ) );
		return $aPage;
	}

	/**
	 * Collects metadata and additional resources for this page
	 * @param Title $oTitle
	 * @param DOMDocument $oPageDOM
	 * @param array $aParams
	 * @return array array( 'meta' => ..., 'resources' => ...);
	 */
	private static function collectData( $oTitle, $oPageDOM, $aParams ) {
		$aMeta      = array();
		$aResources = array(
			'ATTACHMENT' => array(),
			'STYLESHEET' => array(),
			'IMAGE' => array()
		);
		
		// TODO RBV (01.02.12 13:51): Handle oldid
		$aCategories = array();
		if( $oTitle->exists() ) {
			// TODO RBV (27.06.12 11:47): Throws an exception. Maybe better use try ... catch instead of $oTitle->exists()
			$aAPIParams = new FauxRequest( array(
					'action' => 'parse',
					//'oldid'  => ,
					'page'  => $oTitle->getPrefixedText(),
					'prop'   => 'images|categories|links'
			));

			$oAPI = new ApiMain( $aAPIParams );
			$oAPI->execute();

			$aResult = $oAPI->getResultData();

			foreach($aResult['parse']['categories'] as $aCat ) {
				$aCategories[] = $aCat['*'];
			}
		}
		/*
		//For future use...
		foreach($aResult['parse']['images'] as $sFileName ) {
			$oImage = RepoGroup::singleton()->getLocalRepo()->newFile( Title::newFromText( $sFileName, NS_FILE ) );
			if( $oImage->exists() ) {
				$sAbsoluteFileSystemPath = $oImage->getFullPath();
			}
		}
		 */
		
		//Dublin Core:
		$aMeta['DC.title'] = $oTitle->getPrefixedText();
		$aMeta['DC.date']  = wfTimestamp( TS_ISO_8601 ); // TODO RBV (14.12.10 14:01): Check for conformity. Maybe there is a better way to acquire than wfTimestamp()?

		//Custom
		global $wgLang;
		$sCurrentTS = $wgLang->userAdjust( wfTimestampNow() );
		$aMeta['title']           = $oTitle->getPrefixedText();
		$aMeta['exportdate']      = $wgLang->sprintfDate( 'd.m.Y', $sCurrentTS );
		$aMeta['exporttime']      = $wgLang->sprintfDate( 'H:i', $sCurrentTS );
		$aMeta['exporttimeexact'] = $wgLang->sprintfDate( 'H:i:s', $sCurrentTS );
		
		//Custom - Categories->Keywords
		$aMeta['keywords'] = implode( ', ', $aCategories );

		$oDOMXPath = new DOMXPath( $oPageDOM );
		$oMetadataElements = $oDOMXPath->query( "//div[@class='bs-universalexport-meta']" );
		foreach( $oMetadataElements as $oMetadataElement ) {
			if( $oMetadataElement->hasAttributes() ) {
				foreach( $oMetadataElement->attributes as $oAttribute ) {
					if( $oAttribute->name !== 'class' ) {
						$aMeta[ $oAttribute->name ] = $oAttribute->value;
					}
				}
			}
			$oMetadataElement->parentNode->removeChild( $oMetadataElement );
		}
		
		//If it's a normal article
		if( !in_array( $oTitle->getNamespace(), array( NS_SPECIAL, NS_IMAGE, NS_CATEGORY ) ) ) {
			$oArticle = new Article($oTitle);
			$aMeta['author'] = $oArticle->getUserText(); // TODO RBV (14.12.10 12:19): Realname/Username -> DisplayName
			$aMeta['date']   = $wgLang->sprintfDate( 'd.m.Y', $oArticle->getTouched() );
		}

		wfRunHooks( 'BSUEModulePDFcollectMetaData', array( $oTitle, $oPageDOM, &$aParams, $oDOMXPath, &$aMeta ) );
		$aMetaDataOverrides = json_decode( BsConfig::get( 'MW::UniversalExport::MetadataOverrides' ), true );
		$aMeta = array_merge( $aMeta, $aMetaDataOverrides );
		
		return array(
			'meta'      => $aMeta,
			'resources' => $aResources
		);
	}

	/**
	 * Cleans the DOM: removes editsections, script tags, some elementy 
	 * by classes, makes links absolute and pages paginatable and prevents 
	 * large images from clipping in the PDF
	 * @param Title $oTitle
	 * @param DOMDocument $oPageDOM
	 * @param array $aParams 
	 */
	private static function cleanUpDOM( $oTitle, $oPageDOM, $aParams ) {
		global $wgServer;
		$aClassesToRemove = array( 'editsection', 'bs-universalexport-exportexclude' );
		$oDOMXPath = new DOMXPath($oPageDOM );
		wfRunHooks( 'BSUEModulePDFcleanUpDOM', array( $oTitle, $oPageDOM, &$aParams, $oDOMXPath, &$aClassesToRemove ) );

		//Remove script-Tags
		foreach( $oPageDOM->getElementsByTagName( 'script' ) as $oScriptElement ) {
			$oScriptElement->parentNode->removeChild( $oScriptElement );
		}

		//Remove elements by class
		$aContainsStmnts = array();
		foreach( $aClassesToRemove as $sClass ){
			$aContainsStmnts[] = "contains(@class, '".$sClass."')";
		}
		$sXPath = '//*['.implode(' or ', $aContainsStmnts ).']';

		$oEditSectionElements = $oDOMXPath->query( $sXPath );
		foreach( $oEditSectionElements as $oEditSectionElement ) {
			$oEditSectionElement->parentNode->removeChild( $oEditSectionElement );
		}

		//Make internal hyperlinks absolute
		$oInternalAnchorElements = $oDOMXPath->query( "//a[not(contains(@class, 'external')) and not(starts-with(@href, '#'))]" ); //No external and no jumplinks
		foreach( $oInternalAnchorElements as $oInternalAnchorElement ) {
			$sRelativePath = $oInternalAnchorElement->getAttribute( 'href' );
			$oInternalAnchorElement->setAttribute(
				'href',
				$wgServer.$sRelativePath
			);
		}
		
		
		//<editor-fold defaultstate="collapsed" desc="Reference Tags">
		// TODO RBV (31.01.12 17:17): This should be in an extra extension like CiteConnector!
		//$oReferenceTags = $oDOMXPath->query( "//a[contains(@class, 'references')]" );
		//Old Code from Book.class.php
		/*
			$sOut = preg_replace_callback(
						'#<ol class="references">(.*?)</ol>#si',
						array( $this, 'tranformReferenceTags'),
						$sOut
					);

		protected function tranformReferenceTags( $matches ) {
			$referencesOl = '<hr /><ol class="references">';

			$listBody = preg_replace_callback(
									'#(<li.*?>)(.*?)</li>#si',
									array( $this, 'processListItems' ),
									$matches[1] );

			$referencesOl .= $listBody.'</ol>';
			return $referencesOl;
		}

		private function processListItems( $matches ) {
			$listItemStartTag = $matches[1];
			$listItemContent  = $matches[2];

			$startOfSupTag = strpos( $listItemContent, '<sup>' );
			if ( $startOfSupTag ) {
				$listItemContent = substr( $listItemContent, $startOfSupTag );
			}
			else {
				$sUp = $this->mI18N->msg('reference-tag-up');
				$listItemContent = preg_replace('#(<a.*?>).*?</a>(.*?)$#', '\2 \1('.$sUp.')</a>', $listItemContent );
			}

			return $listItemStartTag.$listItemContent.'</li>';
		}*/
		//</editor-fold>
		
		//Make tables paginatable
		$oTableElements = $oPageDOM->getElementsByTagName( 'table' );
		foreach( $oTableElements as $oTableElement ) {
			$oTableRows = $oTableElement->childNodes; //We only want direct children, so we cannot use getElementsByTagName
			$aRows = array();
			foreach( $oTableRows as $oTableRow ){
				//Filter for <tr>
				if( $oTableRow instanceof DOMElement && $oTableRow->tagName == 'tr' )
				$aRows[] = $oTableRow;
			}

			$oTHead = $oPageDOM->createElement('thead');
			$oTBody = $oPageDOM->createElement('tbody');
			foreach( $aRows as $oTableRow ){
				// TODO RBV (06.02.12 15:07): Examine behavior when TH in row with TDs
				$oTHs = $oTableRow->getElementsByTagName('th');

				if( $oTHs->length != 0 ) {
					if( $oTBody->hasChildNodes() ) {
						$oTableElement->appendChild($oTBody);
						$oTBody = $oPageDOM->createElement('tbody');
					}
					$oTHead->appendChild( $oTableRow );
				}
				else {
					if( $oTHead->hasChildNodes() ) {
						$oTableElement->appendChild($oTHead);
						$oTHead = $oPageDOM->createElement('thead');
					}
					$oTBody->appendChild( $oTableRow );
				}
			}
			if( $oTHead->hasChildNodes() ) {
					$oTableElement->appendChild($oTHead);
				}
			if( $oTBody->hasChildNodes() ) {
				$oTableElement->appendChild($oTBody);
			}
		}
		
		//TODO: Should this be in PdfServlet::findFiles()? Or we should add the images as attachments
		//Prevent large images from clipping
		foreach( $oPageDOM->getElementsByTagName( 'img' ) as $oImgElement ) {
			$iWidth = $oImgElement->getAttribute( 'width' );
			if( $iWidth > 700 ) {
				$oImgElement->setAttribute( 'width', 700 );
				$oImgElement->removeAttribute( 'height' );
				
				$sClasses = $oImgElement->getAttribute( 'class' );
				$oImgElement->setAttribute( 'class', $sClasses.' maxwidth' );
			}
		}
		
		//Prevent "first page empty" bug
		$oBodyContent  = $oDOMXPath->query( "//*[contains(@class, 'bodyContent')]" )->item(0);
		$oAntiBugP = $oPageDOM->createElement( 'p' );
		$oAntiBugP->nodeValue = 'I am here to prevent the first-page-empty bug!';
		$oAntiBugP->setAttribute( 'style', 'visibility:hidden;height:0px;margin:0px;padding:0px' );
		$oBodyContent->insertBefore( $oAntiBugP, $oBodyContent->firstChild );
	}
}
