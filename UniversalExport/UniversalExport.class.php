<?php
/**
 * UniversalExport extension for BlueSpice
 *
 * Enables MediaWiki to export pages into different formats.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @version    1.22.0
 * @version    $Id: UniversalExport.class.php 9826 2013-06-20 12:39:37Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage UniversalExport
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * v1.1.0
 * - Support for attachments
 * - Removed Beta state
 * v1.0.0b
 * - Reset version numbering
 * - Reset to beta
 * v3.1.0
 * - Raised to stable
 * - Added events
 * - Changed configuration
 * v3.0.0b
 * - Initial release
 * - Port from HalloWiki Sunrise 1.9
*/

/**
 * Base class for UniversalExport extension
 * @package BlueSpice_Extensions
 * @subpackage UniversalExport
 */
class UniversalExport extends BsExtensionMW {

	protected $aCategoryWhitelist = array( 'Exportable',   'Public' );
	protected $aCategoryBlacklist = array( 'Confidential', 'Internal' );

	//These values may be overridden by application logic. I.e. by query string parameter or tag
	protected $aParamsDefaults = array(
		'format'      => 'pdf',
		'pdf-merging' => '0',
		'attachments' => '0',
		'recursive'   => false,
		'filter'      => 'use-all'
	);

	//These values override values found by the application logic.
	protected $aParamsOverrides = array(
		'use-serverside-caching'  => false,
		'pdf-attachment-formats'  => array( 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'odg', 'svg' ) //Not yet supported
	);

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['UniversalExport'] = dirname( __FILE__ ) . '/UniversalExport.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'UniversalExport',
			EXTINFO::DESCRIPTION => 'Enables MediaWiki to export pages into different formats.',
			EXTINFO::AUTHOR      => 'Robert Vogel',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9826 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
				'bluespice' => '1.22.0',
				'StateBar'  => '1.22.0',
				'WidgetBar' => '1.22.0',
			)
		);
		$this->mExtensionKey = 'MW::UniversalExport';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 *  Initialization of UniversalExport extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		//Hooks
		$this->setHook( 'ParserFirstCallInit', 'onParserFirstCallInit' );
		$this->setHook( 'BSWidgetBarGetDefaultWidgets' );
		$this->setHook( 'BSWidgetListHelperInitKeyWords' );
		$this->setHook( 'BSStateBarAddSortBodyVars', 'onStatebarAddSortBodyVars' );
		$this->setHook( 'BSStateBarBeforeBodyViewAdd' );
		$this->setHook( 'SpecialPage_initList' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );

		//Configuration variables
		$aMetadataDefaults = array(
			'creator'  => 'Hallo Welt! Medienwerkstatt GmbH',
		);
		$aMetadataOverrides = array(
			'producer' => 'UniversalExport 1.20 (BlueSpice for MediaWiki)'
		);

		BsConfig::registerVar( 'MW::UniversalExport::CategoryWhitelist', $this->aCategoryWhitelist,  BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_STRING );
		BsConfig::registerVar( 'MW::UniversalExport::CategoryBlacklist', $this->aCategoryBlacklist,  BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_STRING );
		BsConfig::registerVar( 'MW::UniversalExport::MetadataDefaults',  json_encode( $aMetadataDefaults ),  BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-universalexport-pref-MetadataDefaults', 'textarea' );
		BsConfig::registerVar( 'MW::UniversalExport::MetadataOverrides', json_encode( $aMetadataOverrides ), BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-universalexport-pref-MetadataOverrides', 'textarea' );
		BsConfig::registerVar( 'MW::UniversalExport::ParamsDefaults',    $this->aParamsDefaults,     BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_MIXED );
		BsConfig::registerVar( 'MW::UniversalExport::ParamsOverrides',   $this->aParamsOverrides,    BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_ARRAY_MIXED );

		//Register interfaces and classes
		$sDir = dirname( __FILE__ );
		BsCore::registerInterface( 'BsUniversalExportModule', $sDir.'/lib' );
		BsCore::registerClass( 'BsUniversalExportHelper',     $sDir.'/lib' );
		BsCore::registerClass( 'BsUniversalExportTagLibrary', $sDir.'/lib' );

		//Permissions
		$this->mAdapter->registerPermission( 'universalexport-export' );
		//$this->mAdapter->registerPermission( 'universalexport-export-unfiltered' );
		//$this->mAdapter->registerPermission( 'universalexport-export-recursive' );

		//SpecialPage
//		$this->mAdapter->registerSpecialPage( 'SpecialUniversalExport', $sDir.'/specialpages/', 'UniversalExportAlias' );

		$this->registerView( 'ViewExportModuleOverview' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function onSpecialPage_initList( &$aList ) {
		$aList['UniversalExport'] = 'SpecialUniversalExport';
		return true;
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		//MetadataDefaults and MetadataOverrides
		$aPrefs = array( 'rows' => 5 );
		
		return $aPrefs;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 * @param array $aSortBodyVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortBodyVars( &$aSortBodyVars ) {
		$aSortBodyVars['statebarbodyuniversalexportmeta']   = wfMsg( 'bs-universalexport-statebarbodyuniversalexportmeta' );
		$aSortBodyVars['statebarbodyuniversalexportparams'] = wfMsg( 'bs-universalexport-statebarbodyuniversalexportparams' );
		return true;
	}
	
	/**
	 * Hook handler that adds information to the StateBar body
	 * @param StateBar $oStatebar
	 * @param array $aBodyViews
	 * @param User $oUser
	 * @param Title $oTitle
	 * @return boolean Always true to keep the hook running
	 */
	public function onBSStateBarBeforeBodyViewAdd( $oStatebar, &$aBodyViews, $oUser, $oTitle ) {
		$oMeta   = BsArticleHelper::getInstance($oTitle)->getJSONPageProp( 'bs-universalexport-meta' );
		$oParams = BsArticleHelper::getInstance($oTitle)->getJSONPageProp( 'bs-universalexport-params' );

		if( $oMeta instanceof stdClass ) {
			$aBodyViews['statebarbodyuniversalexportmeta'] =
				BsUniversalExportTagLibrary::makeStateBarBodyElementKeyValueTable(
					array(
						'rows'    => (array)$oMeta,
						'heading' => wfMessage('bs-universalexport-statebarbodyuniversalexportmeta')->plain(),
						'key'     => 'statebarbodyuniversalexportmeta'
					)
				);
		}

		if( $oParams instanceof stdClass ) {
			$aBodyViews['statebarbodyuniversalexportparams'] =
				BsUniversalExportTagLibrary::makeStateBarBodyElementKeyValueTable(
					array(
						'rows'    => (array)$oParams,
						'heading' => wfMessage('bs-universalexport-statebarbodyuniversalexportparams')->plain(),
						'key'     => 'statebarbodyuniversalexportparams'
					)
				);
		}
		
		return true;
	}

	/**
	 * Callback for WidgetBar. Adds the Bookshelf Widget to the WidgetBar as default filling.
	 * @param array $aViews The Views array
	 * @param User $oUser Current MediaWiki User object
	 * @param Title $oTitle Current MediaWiki Title object
	 * @return boolean Always true to keep the hook running
	 */
	public function onBSWidgetBarGetDefaultWidgets( &$aViews, $oUser, $oTitle ) {
		$oWidget = $this->getWidget();
		if( $oWidget !== null ) $aViews[] = $oWidget;
		return true;
	}

	/**
	 * Renders widget view of InfoBox. Called by MW::WidgetBar::DefaultWidgets.
	 * @param BsEvent $oEvent The Event object
	 * @param array $aWidgets An array of widgets. Add your Widget to this array.
	 * @return bool allow other hooked methods to be executed. always true
	 * @deprecated in 1.1.1
	 */
	public function onDefaultWidgets( $oEvent, $aWidgets ) {
		$oWidget = $this->getWidget();
		if( $oWidget !== null ) $aWidgets[] = $oWidget;
		return $aWidgets;
	}

	/**
	 * Hook-Handler for 'MW::Utility::WidgetListHelper::InitKeywords'. Registers a callback for the UNIVERSALEXPORT Keyword.
	 * @param array $aKeywords
	 * @param Title $oTitle
	 * @return boolean Always true to keep the hook running
	 */
	public function onBSWidgetListHelperInitKeyWords( &$aKeywords, $oTitle ) {
		$aKeywords['UNIVERSALEXPORT'] = array( $this, 'onWidgetListKeyword' );
		return true;
	}

	/**
	 * Creates a Widget for the UNIVERSALEXPORT Keyword.
	 * @return ViewWidget
	 */
	public function onWidgetListKeyword() {
		return $this->getWidget();
	}

	/**
	 * Hook-Handler for the MediaWiki 'ParserFirstCallInit' hook. Dispatches registration og TagExtensions to the TagLibrary.
	 * @param Parser $oParser The MediaWiki Parser object
	 * @return bool Always true to keep the hook runnning.
	 */
	public function onParserFirstCallInit( &$oParser ) {
		return BsUniversalExportTagLibrary::onParserFirstCallInit( $oParser );
	}

	/**
	 * Creates a Widget object
	 * @return ViewWidget 
	 */
	public function getWidget() {
		$sAction = BsCore::getParam( 'action', 'view', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		if( !in_array($sAction, array( 'view', 'historysubmit' ) ) ) return null;

		$oCurrentTitle = $this->mAdapter->get('Title');
		if( $oCurrentTitle->quickUserCan( 'universalexport-export' ) === false ) return null;

		$aCurrentQueryParams = BsCore::getParams( BsPARAM::GET );
		$sSpecialPageParameter = BsCore::sanitize( $aCurrentQueryParams['title'], '', BsPARAMTYPE::STRING );
		$oSpecialPage = SpecialPage::getTitleFor( 'UniversalExport',$sSpecialPageParameter );
		if( isset( $aCurrentQueryParams['title'] ) ) unset( $aCurrentQueryParams['title'] );

		$aModules = array();
		wfRunHooks(
			'BSUniversalExportGetWidget',
			array( $this, &$aModules, $oSpecialPage, $oCurrentTitle, $aCurrentQueryParams )
		);

		if( empty( $aModules ) ) return null;

		$sList = '';
		foreach( $aModules as $oModuleView ) {
			if( $oModuleView instanceof ViewBaseElement ) {
				$sList .= $oModuleView->execute();
			}
			else {
				wfDebugLog( 'BS::UniversalExport', 'getWidget: Invalid view.' );
			}
		}

		$oWidgetView = new ViewWidget();
		$oWidgetView->setId( 'universalexport' )
					->setTitle( wfMsg( 'bs-universalexport-widget-title' ) )
					->setBody( $sList )
					->setTooltip( wfMsg( 'bs-universalexport-widget-tooltip' ) );

		return $oWidgetView;
	}
	
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'tags' ) return true;

		$oResponse->result[] = array(
			'id' => 'bs:uemeta',
			'type' => 'tag',
			'name' => 'uemeta',
			'desc' => wfMsgExt( 'bs-universalexport-tag-meta-desc', array( 'parse' ) ),
			'code' => '<bs:uemeta someMeta="Some Value" anotherMeta="Another Value" />',
		);
		
		$oResponse->result[] = array(
			'id' => 'bs:ueparams',
			'type' => 'tag',
			'name' => 'ueparams',
			'desc' => wfMsgExt( 'bs-universalexport-tag-params-desc', array( 'parse' ) ),
			'code' => '<bs:ueparams someParam="Some Value" anotherMeta="Another Value" />',
		);
		
		$oResponse->result[] = array(
			'id' => 'bs:uepagebreak',
			'type' => 'tag',
			'name' => 'uepagebreak',
			'desc' => wfMsgExt( 'bs-universalexport-tag-pagebreak-desc', array( 'parse' ) ),
			'code' => '<bs:uepagebreak />',
		);
		
		$oResponse->result[] = array(
			'id' => 'bs:uenoexport',
			'type' => 'tag',
			'name' => 'uenoexport',
			'desc' => wfMsgExt( 'bs-universalexport-tag-noexport-desc', array( 'parse' ) ),
			'code' => '<bs:uenoexport>Not included in export</bs:uenoexport>',
		);

		return true;
	}
}