<?php
/**
 * BsPDFTemplateProvider.
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
 * UniversalExport BsPDFTemplateProvider class.
 * @package BlueSpice_Extensions
 * @subpackage UEModulePDF
 */
class BsPDFTemplateProvider {
	
	/**
	 * Provides a array suitable for the MediaWiki HtmlFormField class 
	 * HtmlSelectField.
	 * @param array $aParams Has to contain the 'template-path' that has to be
	 * searched for valid templates.
	 * @return array A options array for a HtmlSelectField
	 */
	public static function getTemplatesForSelectOptions( $aParams ) {
		$aOptions = array();
		try {
			$sPath = realpath( $aParams['template-path'] );
			$oDirIterator = new DirectoryIterator( $sPath );
			foreach( $oDirIterator as $oFileInfo ) {
				if( $oFileInfo->isFile() || $oFileInfo->isDot() ) continue;
				$sTemplateDescriptor = $oFileInfo->getPathname().'/template.php';
				if( !file_exists( $sTemplateDescriptor ) ) continue;
				$aTemplate = include( $sTemplateDescriptor );
				$sDirName = $oFileInfo->getBasename();
				$aOptions[$aTemplate['info']['name']] = $sDirName;
			}
		}
		catch( Exception $e ) {
			wfDebugLog( 'BS::UEModulePDF', 'BsPDFTemplateProvider::getTemplatesForSelectOptions: Error: '.$e->getMessage() );
			return array( '-' => '-' );
		}
		
		return $aOptions;
		
	}
	
	/**
	 * Reads in a template file to a DOMDocuments and collects additional 
	 * information.
	 * @param array $aParams Has to contain a valid 'template' entry.
	 * @return array with the DOMDocument and some references.
	 */
	public static function getTemplate( $aParams ) {
		$aParams = array_merge(
			array(
				'language' => 'en',
				'meta'     => array()
			),
			$aParams
		);

		$sPath = realpath( $aParams['path'] );
		$sTemplatePath = $sPath.'/'.$aParams['template'];
		if( !file_exists( $sTemplatePath ) ) {
			throw new BsException( 'Requested template not found! Path:'.$sTemplatePath );
		}
		$sTemplateDescriptor = $sTemplatePath.'/template.php';
		$sTemplateMarkup     = $sTemplatePath.'/template.html';
		$aTemplate           = include( $sTemplateDescriptor );

		$oTemplateDOM = new DOMDocument();
		$oTemplateDOM->formatOutput = true;
		$oTemplateDOM->load( $sTemplateMarkup );
		
		$oHeadElement  = $oTemplateDOM->getElementsByTagName( 'head' )->item( 0 );
		$oBodyElement  = $oTemplateDOM->getElementsByTagName( 'body' )->item( 0 );
		$oTitleElement = $oTemplateDOM->getElementsByTagName( 'title' )->item( 0 );
		
		$aResources = array();
		foreach( $aTemplate['resources'] as $sType => $aFiles ) {
			foreach( $aFiles as $sFile ){
				$aResources[$sType][basename($sFile)] = $sTemplatePath.'/'.$sFile;
			}
		}
		
		//Substitue MSG elements
		$oMsgTags = $oTemplateDOM->getElementsByTagName( 'msg' );

		//Get the message data; If not available use "en" as fallback
		$aMsgs = isset($aTemplate['messages'][$aParams['language']]) 
		? $aTemplate['messages'][$aParams['language']]
		: $aTemplate['messages']['en'];

		//Be careful with "replaceChild" within "foreach"!
		//HINT: http://stackoverflow.com/questions/7035202/why-does-getelementsbytagname-only-grab-every-other-element-here
		$i = $oMsgTags->length - 1;
		while( $i > -1 ){
			$oMsgTag = $oMsgTags->item($i);
			$sKey = $oMsgTag->getAttribute('key');
			$sReplacement = '';
			if( isset($aMsgs[$sKey]) ) $sReplacement = $aMsgs[$sKey];
			$oReplacmentElement = $oTemplateDOM->createTextNode( $sReplacement );
			$oMsgTag->parentNode->replaceChild( $oReplacmentElement, $oMsgTag );
			$i--;
		}
		
		//Substitute META elements
		$oMetaTags = $oTemplateDOM->getElementsByTagName( 'meta' );
		
		$i = $oMetaTags->length - 1;
		while( $i > -1 ){
			$oMetaTag = $oMetaTags->item($i);
			$sKey = $oMetaTag->getAttribute( 'key' );
			if( isset($aParams['meta'][$sKey] ) ) {
				$oReplacmentElement = $oTemplateDOM->createTextNode( $aParams['meta'][$sKey] );
				$oMetaTag->parentNode->replaceChild( $oReplacmentElement, $oMetaTag );
			}
			else {
				$oMetaTag->parentNode->removeChild( $oMetaTag );
			}
			$i--;
		}

		//Add meta tags to head
		foreach( $aParams['meta'] as $sKey => $sValue ) {
			$oMetaTag = $oTemplateDOM->createElement( 'meta' );
			$oMetaTag->setAttribute( 'name', $sKey );
			$oMetaTag->setAttribute( 'content', $sValue );
			$oHeadElement->appendChild( $oMetaTag );
		}
		
		//Find CONTENT elements
		$oContentTags = $oTemplateDOM->getElementsByTagName( 'content' );
		$aContentTagRefs = array();
		foreach( $oContentTags as $oContentTag ) {
			$sKey = $oContentTag->getAttribute('key');
			$aContentTagRefs[$sKey] = $oContentTag;
		}
		
		//Create a bookmarks tag within the head element;
		$oBookmarksNode = $oTemplateDOM->createElement( 'bookmarks' );
		$oHeadElement->appendChild( $oBookmarksNode );
		
		//Get additional stylesheets from wiki context
		$aStyleBlocks = array();
		global $wgUseSiteCss;
		if( $wgUseSiteCss ) {
			$oTitle = Title::makeTitle( NS_MEDIAWIKI, 'Common.css' );
			$aStyleBlocks['MediaWiki:Common.css'] = BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle );
		}

		wfRunHooks( 'BSUEModulePDFBeforeAddingStyleBlocks', array( &$aTemplate, &$aStyleBlocks ) );

		foreach( $aStyleBlocks as $sBlockName => $sCss ) {
			$sCss = "\n/* ".$sBlockName." */\n".$sCss."\n";
			$oStyleElement = $oTemplateDOM->createElement( 'style',  $sCss );
			$oStyleElement->setAttribute( 'type', 'text/css' );
			$oStyleElement->setAttribute( 'rel', 'stylesheet' );
			$oHeadElement->appendChild( $oStyleElement );
		}
		
		return array(
			'resources' => $aResources,
			'dom'       => $oTemplateDOM,
			'content-elements'  => $aContentTagRefs,
			'bookmarks-element' => $oBookmarksNode,
			'head-element'      => $oHeadElement,
			'body-element'      => $oBodyElement,
			'title-element'     => $oTitleElement
		);
	}
}