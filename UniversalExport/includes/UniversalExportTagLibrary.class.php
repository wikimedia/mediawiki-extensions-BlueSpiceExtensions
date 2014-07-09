<?php
/**
 * The TagLibrary of the UniversalExport Extension.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage UniversalExport
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * UniversalExport TagLibrary class.
 * @package BlueSpice_Extensions
 * @subpackage UniversalExport
 */
class BsUniversalExportTagLibrary {
	/**
	 * Hook-Handler for the MediaWiki 'ParserFirstCallInit' hook. Registers TagExtensions within the Parser.
	 * @param Parser $oParser The MediaWiki Parser object
	 * @return bool Always true to keep the hook runnning.
	 */
	public static function onParserFirstCallInit( &$oParser ) {
		$oParser->setHook( 'pdfpagebreak',                 'BsUniversalExportTagLibrary::onPagebreakTag' );
		$oParser->setHook( 'universalexport:pagebreak',    'BsUniversalExportTagLibrary::onPagebreakTag' );
		$oParser->setHook( 'bs:universalexport:pagebreak', 'BsUniversalExportTagLibrary::onPagebreakTag' );
		$oParser->setHook( 'bs:uepagebreak',               'BsUniversalExportTagLibrary::onPagebreakTag' );

		$oParser->setHook( 'nopdf',                       'BsUniversalExportTagLibrary::onExcludeTag' );
		$oParser->setHook( 'universalexport:exclude',     'BsUniversalExportTagLibrary::onExcludeTag' );
		$oParser->setHook( 'bs:universalexport:exclude',  'BsUniversalExportTagLibrary::onExcludeTag' );
		$oParser->setHook( 'universalexport:noexport',    'BsUniversalExportTagLibrary::onExcludeTag' );
		$oParser->setHook( 'bs:universalexport:noexport', 'BsUniversalExportTagLibrary::onExcludeTag' );
		$oParser->setHook( 'bs:uenoexport',               'BsUniversalExportTagLibrary::onExcludeTag' );

		$oParser->setHook( 'pdfhidetitle',                 'BsUniversalExportTagLibrary::onHideTitleTag' );
		$oParser->setHook( 'universalexport:hidetitle',    'BsUniversalExportTagLibrary::onHideTitleTag' );
		$oParser->setHook( 'bs:universalexport:hidetitle', 'BsUniversalExportTagLibrary::onHideTitleTag' );
		$oParser->setHook( 'bs:uehidetitle',               'BsUniversalExportTagLibrary::onHideTitleTag' );

		$oParser->setHook( 'pdfexcludepage',                    'BsUniversalExportTagLibrary::onExcludeArticleTag' );
		$oParser->setHook( 'universalexport:excludearticle',    'BsUniversalExportTagLibrary::onExcludeArticleTag' );
		$oParser->setHook( 'bs:universalexport:excludearticle', 'BsUniversalExportTagLibrary::onExcludeArticleTag' );
		$oParser->setHook( 'bs:ueexcludearticle',               'BsUniversalExportTagLibrary::onExcludeArticleTag' );

		$oParser->setHook( 'universalexport:meta',    'BsUniversalExportTagLibrary::onMetaTag' );
		$oParser->setHook( 'bs:universalexport:meta', 'BsUniversalExportTagLibrary::onMetaTag' );
		$oParser->setHook( 'bs:uemeta',               'BsUniversalExportTagLibrary::onMetaTag' );

		$oParser->setHook( 'universalexport:params',    'BsUniversalExportTagLibrary::onParamsTag' );
		$oParser->setHook( 'bs:universalexport:params', 'BsUniversalExportTagLibrary::onParamsTag' );
		$oParser->setHook( 'bs:ueparams',               'BsUniversalExportTagLibrary::onParamsTag' );
		return true;
	}

	public static function onPagebreakTag( $sContent, $aAttributes, $oParser ) {
		$aOut = array();

		// TODO RBV (08.02.11 11:34): Use CSS class for styling
		$aOut[] = '<div class="bs-universalexport-pagebreak" style="border-top: 2px dotted #999; background-color: #F5F5F5; color: #BBB; font-style: italic; text-align: center;">';
		$aOut[] = wfMessage( 'bs-universalexport-tag-pagebreak-text' )->plain();
		$aOut[] = '</div>';

		return implode( '', $aOut );
	}

	public static function onExcludeTag( $sContent, $aAttributes, $oParser ) {
		$aOut = array();

		// TODO RBV (08.02.11 11:34): Use CSS class for styling
		$aOut[] = '<div class="bs-universalexport-exportexclude" title="'.wfMessage( 'bs-universalexport-tag-exclude-text' )->plain().'">';
		$aOut[] = $oParser->recursiveTagParse( $sContent );
		$aOut[] = '</div>';

		return implode( '', $aOut );
	}

	private static $mHideTitleTagCount = 0;

	public static function onHideTitleTag( $sContent, $aAttributes, $oParser ) {
		$oParser->getOutput()->setProperty(
			'bs-universalexport-hidetitle', 
			true
		);

		$oHideTitleTagView = new ViewStateBarBodyElement();
		$oHideTitleTagView->setKey( 'bs-universalexport-hidetitle-'.self::$mHideTitleTagCount );
		$oHideTitleTagView->setHeading( wfMessage( 'bs-universalexport-tag-hidetitle-text' )->plain() );

		//PW(06.09.2012): return view instead of add statebar element
		//StateBar::addBodyElement( $oHideTitleTagView, 'statebaruniversal' );
		self::$mHideTitleTagCount++;
		return $oHideTitleTagView->execute();
	}

	private static $mExcludeArticleTagCount = 0;

	public static function onExcludeArticleTag( $sContent, $aAttributes, $oParser ) {
		$oExcludeArticleTagView = new ViewStateBarBodyElement();
		$oExcludeArticleTagView->setKey( 'bs-universalexport-excludearticle-'.self::$mExcludeArticleTagCount );
		$oExcludeArticleTagView->setHeading( wfMessage( 'bs-universalexport-tag-excludearticle-text' )->plain() );

		//PW(06.09.2012): return view instead of add statebar element
		//StateBar::addBodyElement( $oExcludeArticleTagView, 1030 + self::$mExcludeArticleTagCount );
		self::$mExcludeArticleTagCount++;
		return $oExcludeArticleTagView->execute();
	}

	public static function onMetaTag( $sContent, $aAttributes, $oParser ) {
		$oParser->getOutput()->setProperty(
			'bs-universalexport-meta', 
			json_encode( $aAttributes )
		);

		$aOut = array();
		$aOut[] = '<div class="bs-universalexport-meta"';
		foreach( $aAttributes as $sKey => $sValue ) {
			$aOut[] = ' '.$sKey.'="'.$sValue.'"';
		}
		$aOut[] = '></div>';

		return implode( '', $aOut );
	}

	public static function onParamsTag( $sContent, $aAttributes, $oParser ) {
		$oParser->getOutput()->setProperty(
			'bs-universalexport-params', 
			json_encode( $aAttributes )
		);

		$aOut = array();
		$aOut[] = '<div class="bs-universalexport-params"';
		foreach( $aAttributes as $sKey => $sValue ) {
			$aOut[] = ' '.$sKey.'="'.$sValue.'"';
		}
		$aOut[] = '></div>';

		return implode( '', $aOut );
	}

	public static function makeStateBarBodyElementKeyValueTable( $aParameters ) {
		$oKeyValueTable = new ViewBaseElement();
		$oKeyValueTable->setTemplate( '<table class="contenttable">###ROWS###</table>' );

		$oRowView = new ViewBaseElement();
		$oRowView->setTemplate( '<tr><td>{KEY}</td><td><em>{VALUE}</em></td></tr>' );
		foreach( $aParameters['rows'] as $sKey => $sValue ){
			$oRowView->addData( array(
				'KEY'   => wfMessage( 'bs-universalexport-' . $sKey )->plain(),
				'VALUE' => $sValue
			));
		}

		$oKeyValueTable->addItem( $oRowView, 'ROWS' );

		$oKeyValueStateBarBodyView = new ViewStateBarBodyElement();
		$oKeyValueStateBarBodyView->setKey( $aParameters['key'] );
		$oKeyValueStateBarBodyView->setHeading( $aParameters['heading'] );
		$oKeyValueStateBarBodyView->setBodyText( $oKeyValueTable->execute() );

		return $oKeyValueStateBarBodyView;
	}
}