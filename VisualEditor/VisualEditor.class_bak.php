<?php
/**
 * Visual Editor extension for BlueSpice
 *
 * Visual editor for MediaWiki.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
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
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbricht
 * @version    1.22.0 stable
 * @package    BlueSpice_Extensions
 * @subpackage VisualEditor
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MW I18N
 * - Loose coupling for config and additional buttons
 * v1.0.0
 * - reset revision
 * - raised to stable
 * v1.12
 * - moved addEditButton to Adapters
 * v1.0
 * - initial release
 */


/**
 * Base class for VisualEditor extension
 * @package BlueSpice_Extensions
 * @subpackage VisualEditor
 */
class VisualEditor extends BsExtensionMW {

	/**
	 * Stores whether available tag names in mediawiki have been collected. 
	 * This should only happen once, however, the hook is called more often.
	 * @var bool Availabel tags have been collected.
	 */
	private $bTagsCollected = false;
	/*
	 * Standard configuration for visual editor in full mode
	 */
	private $aConfigStandard = array(
		'mode' => "none",
		'dialog_type' => "modal",
		'skin' => 'o2k7',
		'skin_variant' => 'silver',
		'width' => "100%",
		'plugins' => array(
			"autoresize",
			"lists",
			"inlinepopups",
			"emotions",
			"table",
			"visualchars",
			"save",
			"searchreplace",
			"paste",
			"hwcode",
			"hwactions",
			"hwcontextmenu",
			"hwbehaviour",
			"spellchecker",
			"hwfullscreen",
			"advimagescale",
		), 
		'theme_advanced_buttons1' => array(
			"hwwiki",
			"hwswitch",
			"save",
			"|",
			"undo",
			"redo",
			"|",
			"search",
			"replace",
			"paste",
			"pasteword",
			"selectall",
			"|",
			"bold",
			"italic",
			"underline",
			"strikethrough",
			"|",
			"justifyleft",
			"justifycenter",
			"justifyright",
			"justifyfull",
			"|",
			"bullist",
			"numlist",
			"|",
			"outdent",
			"indent",
			"|",
			"formatselect",
			"forecolor"
		),
		'theme_advanced_buttons2' => array(
			"hr",
			"removeformat",
			"|",
			"sub",
			"sup",
			"|",
			"charmap",
			"|",
			"tablecontrols",
			"|",
			"hwsignature",
			"hwlink",
			"unlink",
			"hwcategory",
			"hwlinebreak",
			"|",
			"fullscreen",
		),
		'theme_advanced_buttons3' => "",
		'theme_advanced_blockformats' => array( 
			"p",
			"pre",
			"h2",
			"h3",
			"h4",
			"h5",
			"h6"
		),
		'save_enablewhendirty' => true,
		'theme_advanced_more_colors' => false,
		'theme_advanced_text_colors' => array(
			"#000000",
			"#993300",
			"#333300",
			"#003300",
			"#003366",
			"#000080",
			"#333399",
			"#333333",
			"#800000",
			"#FF6600",
			"#808000",
			"#008000",
			"#008080",
			"#0000FF",
			"#666699",
			"#808080",
			"#808080",
			"#FF9900",
			"#99CC00",
			"#B2B2B2",
			"#33CCCC",
			"#3366FF",
			"#800080",
			"#999999",
			"#FF00FF",
			"#FFCC00",
			"#FFFF00",
			"#00FF00",
			"#00FFFF",
			"#00CCFF",
			"#993366",
			"#C0C0C0",
			"#FF99CC",
			"#FFCC99",
			"#FFFF99",
			"#CCFFCC",
			"#CCFFFF",
			"#99CCFF",
			"#CC99FF",
			"#FFFFFF"
		),
		'convert_newlines_to_brs' => false,
		'forced_root_block' => '',
		'force_p_newlines' => true,
		'remove_linebreaks' => true,
		'theme' => "advanced",
		'fullscreen_new_window' => false,
		'theme_advanced_toolbar_location' => "top",
		'theme_advanced_toolbar_align' => "left",
		'theme_advanced_statusbar_location' => 'none',
		'button_tile_map' => true,
		'remove_trailing_nbsp' => true,
		'convert_fonts_to_spans' => true,
		'entity_encoding' => "named",
		'convert_urls' => false,
		'paste_retain_style_properties' => "all",
		'paste_strip_class_attributes' => "all",
		'paste_remove_spans' => true,
		'paste_remove_styles' => true,
		'spellchecker_languages' => "+German=de",
		'cleanup_on_startup' => true,
		'valid_children' => "+body[style],+span[ul]",
		'advimagescale_maintain_aspect_ratio' => true, /* this is the default behavior */
		'advimagescale_fix_border_glitch' => true, /* also the default behavior */
		'advimagescale_noresize_all' => false, /* set to true to prevent all resizing on images */
	);

	/**
	 * Default value for config of reduced version of the editor, which is currently stored in a private variable.
	 * @var array will be JSON encoded later for configuration. 
	 */
	private $aConfigOverwrite = array(
			"theme_advanced_buttons1" => array(
			"hwwiki",
			"hwswitch",
			"save",
			"|",
			"undo",
			"redo",
			"|",
			"bold",
			"italic",
			"underline",
			"strikethrough",
			"|",
			"justifyleft",
			"justifycenter",
			"justifyright",
			"|",
			"bullist",
			"numlist",
			"|",
			"formatselect",
			"forecolor",
			"|",
			"table",
			"cell_props",
			"row_after",
			"delete_row",
			"col_after",
			"delete_col",
			"|",
			"hwlink",
			"unlink",
			"hwcategory",
			"hwsignature",
			"|",
			"fullscreen",
		),
		"theme_advanced_buttons2" => "",
		"theme_advanced_buttons3" => "",
		'theme_advanced_statusbar_location' => 'none'
	);

	/**
	 * Constructor of VisualEditor class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'VisualEditor',
			EXTINFO::DESCRIPTION => 'Visual editor for MediaWiki.',
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION     => '1.22.0',
			EXTINFO::STATUS      => 'beta',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '1.22.0' )
		);
		$this->mExtensionKey = 'MW::VisualEditor';

		BsConfig::registerVar( 'MW::VisualEditor::disableNS',          array( NS_MEDIAWIKI ), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_INT | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-visualeditor-pref-disableNS', 'multiselectex' );
		BsConfig::registerVar( 'MW::VisualEditor::defaultNoContextNS', array( NS_SPECIAL, NS_MEDIA, NS_FILE ), BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_ARRAY_INT , 'bs-visualeditor-pref-defaultNoContextNS', 'multiselectex' );

		BsConfig::registerVar( 'MW::VisualEditor::SpecialTags',        array(), BsConfig::LEVEL_PRIVATE|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-visualeditor-pref-SpecialTags' );
		BsConfig::registerVar( 'MW::VisualEditor::AllowedTags',        array(), BsConfig::LEVEL_PRIVATE|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-visualeditor-pref-AllowedTags' );

		BsConfig::registerVar( 'MW::VisualEditor::Use',                true, BsConfig::LEVEL_USER|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-visualeditor-pref-Use', 'toggle' );
		BsConfig::registerVar( 'MW::VisualEditor::UseLimited',         false, BsConfig::LEVEL_USER|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-visualeditor-pref-UseLimited', 'toggle' );
		BsConfig::registerVar( 'MW::VisualEditor::UseForceLimited',    false, BsConfig::LEVEL_PUBLIC|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-visualeditor-pref-UseForceLimited', 'toggle' );

		BsConfig::registerVar( 'MW::VisualEditor::DebugMode',          false, BsConfig::LEVEL_PRIVATE|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-visualeditor-pref-DebugMode' );
		BsConfig::registerVar( 'MW::VisualEditor::GuiMode',            true, BsConfig::LEVEL_PRIVATE|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-visualeditor-pref-GuiMode' );
		BsConfig::registerVar( 'MW::VisualEditor::GuiSwitchable',      true, BsConfig::LEVEL_PRIVATE|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-visualeditor-pref-GuiSwitchable' );
		
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Constructor of VisualEditor class
	 */
	protected function initExt() {
		//wfProfileIn( 'BS::VisualEditor::initExt' );
		$this->mCore->registerBehaviorSwitch( 'NOEDITOR', array($this, 'noEditorCallback') ) ;

		// Hooks
		$this->setHook( 'MediaWikiPerformAction' );
		$this->setHook( 'ParserAfterTidy' );
		$this->setHook( 'BeforePageDisplay' );
		// wfProfileOut('BS::VisualEditor::initExt' );
	}
	
	/*
	 * Adds module
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean Always true
	*/
	public function onBeforePageDisplay( $out, $skin) {
		$out->addModuleStyles('ext.bluespice.visualEditor');

		if( $this->checkContext( $out->getTitle() ) === false ) {
			$this->noEditorCallback();
			return true;
		}
		
		$out->addModules('ext.bluespice.visualEditor');

		$sAction = $out->getRequest()->getVal('action', 'view');
		if( $sAction != 'edit' && $sAction != 'preview' ) return true;
		
		//TODO: Only clientside?
		$sPluginsDir = '/extensions/BlueSpiceExtensions/VisualEditor/resources/tiny_mce_plugins';
		$aPlugins = array(
			'hwcontextmenu' => "{$sPluginsDir}/hwcontextmenu/editor_plugin.js",
			'hwactions'     => "{$sPluginsDir}/hwactions/editor_plugin.js",
			'hwbehaviour'   => "{$sPluginsDir}/hwbehaviour/editor_plugin.js",
			'hwcode'        => "{$sPluginsDir}/hwcode/editor_plugin.js",
			'hwfullscreen'  => "{$sPluginsDir}/hwfullscreen/editor_plugin.js",
		);
		
		//TODO: i18n
		$aTableStyles = array(
			wfMessage('bs-visualeditor-sortable')->plain()              => 'sortable',
			wfMessage('bs-visualeditor-sortable-wikitable')->plain()    => 'sortable wikitable',
			wfMessage('bs-visualeditor-wikitable')->plain()             => 'wikitable',
			wfMessage('bs-visualeditor-contenttable')->plain()          => 'contenttable',
			wfMessage('bs-visualeditor-contenttable-black')->plain()    => 'contenttable-black',
			wfMessage('bs-visualeditor-contenttable-blue')->plain()     => 'contenttable-blue',
			wfMessage('bs-visualeditor-contenttable-darkblue')->plain() => 'contenttable-darkblue'
		);
		
		$sTableStyles = '';
		foreach( $aTableStyles as $sName => $sCSSClass ) {
			$sTableStyles .= $sName.'='.$sCSSClass.';';
		}
		
		$out->addJsConfigVars( 'bsVisualEditorTinyMCEPlugins', $aPlugins );
		$out->addJsConfigVars( 'bsVisualEditorTinyMCETableStyles', $sTableStyles );
		
		return true;
	}

	/**
	 * Hook-Handler for MediaWiki hook MediaWikiPerformAction
	 * @param OutputPage $oOutputPage MediaWiki Outpupage object.
	 * @param Article $oArticle MediaWiki article object.
	 * @param Title $oTitle MediaWiki title object.
	 * @param User $oUser MediaWiki user object.
	 * @param Request $oRequest MediaWiki request object.
	 * @param MediaWiki $oMediaWiki MediaWiki mediaWiki object.
	 * @return bool Always true.
	 */
	public function onMediaWikiPerformAction( $oOutputPage, $oArticle, $oTitle, $oUser, $oRequest, $oMediaWiki ) {
		if( $this->checkContext( $oOutputPage->getTitle() ) === false ) {
			$this->noEditorCallback(); //TODO: Neccessary?
			return true;
		}

		$this->mCore->addEditButton( 'ToggleWysiwyg', array(
			'id'      => 'edit_button',
			'msg'     => wfMessage( 'wysiwyg' )->plain(),
			'image'   => '/extensions/BlueSpiceExtensions/VisualEditor/resources/images/btn_wysiwyg.gif',
			'onclick' => "return false;"
		) );
		return true;
	}

	/**
	 * Compiles a list of tags that must be passed by the editor.
	 * @global Language $wgLang
	 * @global string $wgScriptPath
	 * @global string $wgStylePath
	 * @param Parser $oParser MediaWiki parser object.
	 * @return bool Allow other hooked methods to be executed. Always true.
	 */
	public function onParserAfterTidy( &$oParser ) {
		global $wgLang, $wgScriptPath, $wgDefaultSkin;
		/*
		$vNoEditor = $oParser->getOutput()->getProperty('bs_noeditor'); //This is the case in "preview" mode
		if( $vNoEditor === false ) { //Maybe we are not in preview
			$vNoEditor = BsArticleHelper::getInstance( $oParser->getTitle() )
				->getPageProp('bs_noeditor');
		}

		if( $vNoEditor === '' ) { //Empty string is in DB if MagicWord is prevalent
			$this->mAdapter->removeEditButton( 'ToggleWysiwyg' );
			BsConfig::set( 'MW::VisualEditor::Use', false, true );
			return true;
		}*/

		if ( $this->bTagsCollected ) return true;
		$this->bTagsCollected = true; 

		$tags = $oParser->getTags();
		$allowedTags = '';
		$specialTags = '';
		foreach($tags as $tag) {
			if ( $tag == 'pre' ) continue;
			$allowedTags .= $tag.'[*],';
			$specialTags .= $tag.'|';
		}

		BsConfig::set( 'MW::VisualEditor::SpecialTags', $specialTags );
		BsConfig::set( 'MW::VisualEditor::AllowedTags', $allowedTags );

		//TODO: There are duplicates!
		$aDefaultTags = array(
			"syntaxhighlight","source","infobox","categorytree","nowiki",
			"presentation","includeonly","onlyinclude","noinclude",
			"backlink","gallery","math","video","rss","tagcloud"
		);
		$this->aConfigStandard["specialtaglist"] = 
			BsConfig::get( 'MW::VisualEditor::SpecialTags' )
			.implode('|', $aDefaultTags);
		
		$this->aConfigStandard["extended_valid_elements"] = 
			BsConfig::get( 'MW::VisualEditor::AllowedTags' )
			.implode('[*],', $aDefaultTags).'[*],body[style]';

		// TODO SW: use string flag as parameter to allow hookhandler to 
		// determin context. This will be usefull if hook gets called in 
		// another place
		wfRunHooks( 'VisualEditorConfig', array( &$this->aConfigStandard, &$this->aConfigOverwrite ) );

		// convert inner arrays to string for json encode
		foreach( $this->aConfigStandard as $key => $value ) {
			if( !is_array( $value ) ) continue;
			$this->aConfigStandard[$key] = implode( ',', $value );
		}
		foreach( $this->aConfigOverwrite as $key => $value ) {
			if( is_array( $value ) ) {
				$this->aConfigOverwrite[$key] = implode( ',', $value );
			}
		}
		BsConfig::registerVar( 'MW::VisualEditor::ConfigStandard',  json_encode( $this->aConfigStandard ), BsConfig::LEVEL_PRIVATE|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_JSON, 'bs-visualeditor-pref-ConfigStandard' );
		BsConfig::registerVar( 'MW::VisualEditor::ConfigOverwrite', json_encode( $this->aConfigOverwrite ), BsConfig::LEVEL_PRIVATE|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_JSON, 'bs-visualeditor-pref-ConfigOverwrite' );

		return true;
	}

	/**
	 * Callback function in case __NOEDITOR__ keyword is found. Basically removes toggle button
	 */
	// Use context to block script loading
	public function noEditorCallback() {
		$this->mCore->removeEditButton( 'ToggleWysiwyg' );
		//Overwrite user setting
		BsCore::registerClientScriptBlock( $this->mExtensionKey, "bsVisualEditorUse=false;", 'NOEDITOR' );
		BsConfig::set( 'MW::VisualEditor::Use', false, true ); //This seems to be too late
	}
	
	/**
	 * 
	 * @global User $wgUser
	 * @global Language $wgLang

	 * @return string
	 */
	public static function doSaveArticle() {
		if ( BsCore::checkAccessAdmission( 'read' ) === false ) return true;
		global $wgLang, $wgRequest;
		$sArticleId = $wgRequest->getInt( 'articleId', -1 );
		$sText      = $wgRequest->getVal( 'text', '' );
		$sPageName  = $wgRequest->getVal( 'pageName', '' );
		$sSummary   = $wgRequest->getVal( 'summary', '' );
		$iSection   = $wgRequest->getInt( 'editsection', 0 );

		$sReturnEditTime = wfTimestampNow();
		if ( $sSummary == 'false' ) {
			$sSummary = wfMessage( 'bs-visualeditor-no-summary' )->plain();
		}

		/*
		$aSubmitData = array(
			'wpTextbox1'  => $sText,
			'wpStarttime' => $sReturnEditTime,
			'wpEdittime'  => $sEditTime,
			'wpEditToken' => $wgUser->isLoggedIn() ? $wgUser->editToken() : EDIT_TOKEN_SUFFIX,
			'wpSave'      => '',
			'wpSummary'   => $sSummary,
			'action'      => 'submit',
		);
		*/
		
		if ( $sArticleId == -1 ) {
			$oArticle = new Article( Title::newFromText( $sPageName ) );//Article::newFromTitle( Title::newFromText( $sPageName ) );
		} else {
			$oArticle = Article::newFromID( $sArticleId );
		}
		/*
		//TODO: This new approach had to be undone, because MW sends a 
		//Location-Header within the AJAX-Response if we use the EditPage 
		//object.
		$oRequest = new FauxRequest( $aSubmitData, true );
		//$oRequest = new WebRequest();
		$oEditor = new EditPage( $oArticle );
		$oEditor->importFormData( $oRequest );

		$aResultDetails = array(); // dont know why??
		$oSaveResult = $oEditor->internalAttemptSave( $aResultDetails );
		*/
		if ( $iSection ) {
			$sText = $oArticle->replaceSection( $iSection, $sText );
		}

		$oSaveResult = $oArticle->doEdit( $sText, $sSummary );

		/*
		if ( is_object( $oSaveResult ) ) {
			$sSaveResultCode = $oSaveResult->value;
		} else {
			$sSaveResultCode = $oSaveResult;
		}
		*/

		$sTime = $wgLang->timeanddate( $sReturnEditTime, true );
		$sMessage = '';
		$sResult = '';
		if( empty( $oSaveResult->errors ) ) {
			$sResult = 'ok';
			$sMessage = wfMessage( 'bs-visualeditor-save-message', $sTime, $sSummary )->plain();
		} else {
			$sResult = 'fail';
			$sMessage = $oSaveResult->getMessage();
		}

		$aOutput = array(
			'saveresult' => $sResult,//$oSaveResult->getMessage(),//$sSaveResultCode,
			'message'    => $sMessage,//wfMessage( 'bs-visualeditor-save-message', $sTime, $sSummary )->plain(),
			'edittime'   => $sReturnEditTime,
			'summary'    => $sSummary,
			'starttime'  => wfTimestamp( TS_MW, time() + 2 )
		);

		return json_encode( $aOutput );
	}
	
	public static function checkLinks( $links ) {
		$aResult = array();
		foreach( $links as $sTitle ) {
			$oTitle = Title::newFromText( urldecode($sTitle) );
			$aResult[] = $oTitle instanceof Title ? $oTitle->exists() : false;
		}
		return FormatJson::encode( $aResult );
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		wfProfileIn( 'BS::' . __METHOD__ );
		$aPrefs = array();

		switch ($oVariable->getName()) {
			case 'disableNS':
				global $wgContLang;
				$aExcludeNmsps = BsConfig::get('MW::VisualEditor::defaultNoContextNS');
				foreach ( $wgContLang->getNamespaces() as $sNamespace ) {
					$iNsIndex = $wgContLang->getNsIndex( $sNamespace );
					if ( !MWNamespace::isTalk( $iNsIndex ) ) continue;
					$aExcludeNmsps[] = $iNsIndex;
				}
				$aPrefs['type']		= 'multiselectex';
				$aPrefs['options']	= BsNamespaceHelper::getNamespacesForSelectOptions( $aExcludeNmsps );
				break;
			default:
		}

		wfProfileOut( 'BS::' . __METHOD__ );
		return $aPrefs;
	}
	
	/**
	 * Checks wether to set Context or not.
	 * @param Title $oTitle
	 * @return bool
	 */
	private function checkContext( $oTitle ) {
		if ( !is_object( $oTitle ) ) return false;

		global $wgRequest;
		if ( $wgRequest->getVal( 'action' ) !== 'edit'
			&& $wgRequest->getVal( 'action' ) !== 'submit' ) return false;

		if ( !$oTitle->userCan( 'edit' ) ) return false;

		$aExcludeNmsps = BsConfig::get( 'MW::VisualEditor::defaultNoContextNS' );
		$aExcludeNmsps = array_merge( $aExcludeNmsps, BsConfig::get( 'MW::VisualEditor::disableNS' ) );
		if ( in_array( $oTitle->getNamespace(), $aExcludeNmsps ) ) return false;

		return true;
	}
}
