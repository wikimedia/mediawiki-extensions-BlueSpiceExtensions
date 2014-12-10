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
 * @author     Stefan Widmann <widmann@hallowelt.biz>
 * @version    2.22.0 stable
 * @package    BlueSpice_Extensions
 * @subpackage VisualEditor
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for VisualEditor extension
 * @package BlueSpice_Extensions
 * @subpackage VisualEditor
 */
class VisualEditor extends BsExtensionMW {

	private $bStartEditor = true;

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
		'selector' => '#wpTextbox1',
		'plugins' => array(
			"lists",
			//"emoticons",
			"table",
			"visualchars",
			"save",
			"searchreplace",
			"paste",
			//"spellchecker",
			"fullscreen",
			"textcolor",
			"contextmenu",
			//"link" //Needed for "unlink"
			//"autoresize",
			"charmap",
			"noneditable",
			"colorpicker"
		),
		'external_plugins' => array(
			'bswikicode'  => '../tiny_mce_plugins/bswikicode/plugin.js',
			'bsbehaviour' => '../tiny_mce_plugins/bsbehaviour/plugin.js',
			'bsactions'   => '../tiny_mce_plugins/bsactions/plugin.js',
			'bsautoresize'=> '../tiny_mce_plugins/bsautoresize/plugin.js'
		),
		'menubar' => false,
		'statusbar' => false,
		//'inline' => true,
		'menu' => false,
		'toolbar1' => array(
			'bswiki', /* 'bsswitch', */ 'bssave', '|', 'undo', 'redo', '|',
			'searchreplace', 'paste', '|', 'bssignature', 'unlink', '|',
			'table', '|', 'bstableaddrowbefore',
			'bstableaddrowafter', 'bstabledeleterow', 'bstableaddcolumnbefore',
			'bstableaddcolumnafter', 'bstabledeletecolumn'
		),
		'toolbar2' => array(
			'bold', 'italic', 'underline', 'strikethrough', '|',
			'bullist', 'numlist', '|', 'outdent', 'indent', '|',
			'charmap', 'bslinebreak', '|',
			'styleselect', 'bsheadings', 'forecolor', 'removeformat', '|',
			'fullscreen'
		),
		// autofocus on the editor instance with this id
		'auto_focus' => 'wpTextbox1',
		// the default text direction for the editor
		'directionality' => 'ltr',
		// use the browser spellcheck?
		'browser_spellcheck' => true,
		// default language
		'language' => 'en',
		// don't wrap the editable element?
		'nowrap' => false,
		// enable resizing for element like images, tables or media objects
		'object_resizing' => true,
		// convert font tags into spans with styles
		'convert_fonts_to_spans' => true,
		// the html mode for tag creation (we need xhtml)
		'element_format' => 'xhtml',
		// define the element what all inline elements needs to be wrapped in
		'forced_root_block' => 'p',
		// keep current style on pressing return
		'keep_styles' => true,
		// save plugin
		'save_enablewhendirty' => true,
		//Allow style tags in body and unordered lists in spans (inline)
		'valid_children' => "+span[ul]",
		//set the id of the body tag in iframe to bodyContent, so styles do
		//apply in a correct manner. This may be dangerous.
		'body_id' => 'bodyContent',
		'autoresize_max_height' => 15000,
		#'document_base_url' => $GLOBALS['wgServer'],
		'style_formats' => array(
			array('title' => 'Headers', 'items' => array(
				array('title' => 'Header 2', 'format' => 'h2'),
				array('title' => 'Header 3', 'format' => 'h3'),
				array('title' => 'Header 4', 'format' => 'h4'),
				array('title' => 'Header 5', 'format' => 'h5'),
				array('title' => 'Header 6', 'format' => 'h6')
			)),
			array('title' => 'Inline', 'items'  => array(
				array('title' => 'Code', 'format' => 'code', 'icon' => 'code' ),
				array('title' => 'Superscript', 'format' => 'superscript', 'icon' => 'superscript' ),
				array('title' => 'Subscript', 'format' => 'subscript', 'icon' => 'subscript' ),
			)),
			array('title' => 'Alignment', 'items'  => array(
				array('title' => 'Left', 'format' => 'alignleft', 'icon' => 'alignleft' ),
				array('title' => 'Center', 'format' => 'aligncenter', 'icon' => 'aligncenter' ),
				array('title' => 'Right', 'format' => 'alignright', 'icon' => 'alignright' ),
				array('title' => 'Top', 'selector' => 'td', 'classes' => 'bs-aligntop' ),
				array('title' => 'Middle', 'selector' => 'td', 'classes' => 'bs-alignmiddle' ),
				array('title' => 'Bottom', 'selector' => 'td', 'classes' => 'bs-alignbottom' )
			)),
			array('title' => 'Table', 'items'  => array(
				array('title' => 'bs-visualeditor-sortable', 'selector' => 'table', 'classes' => 'sortable'),
				array('title' => 'bs-visualeditor-wikitable', 'selector' => 'table', 'classes' => 'wikitable'),
				array('title' => 'bs-visualeditor-contenttable', 'selector' => 'table', 'classes' => 'contenttable'),
				array('title' => 'bs-visualeditor-contenttable-black', 'selector' => 'table', 'classes' => 'contenttable-black'),
				array('title' => 'bs-visualeditor-contenttable-blue', 'selector' => 'table', 'classes' => 'contenttable-blue'),
				array('title' => 'bs-visualeditor-contenttable-darkblue', 'selector' => 'table', 'classes' => 'contenttable-darkblue'),
				array('title' => 'bs-visualeditor-cuscosky', 'selector' => 'table', 'classes' => 'cuscosky'),
				array('title' => 'bs-visualeditor-casablanca', 'selector' => 'table', 'classes' => 'casablanca'),
				array('title' => 'bs-visualeditor-greyscale', 'selector' => 'table', 'classes' => 'greyscale'),
				array('title' => 'bs-visualeditor-greyscale-narrow', 'selector' => 'table', 'classes' => 'greyscale-narrow'),
			)),
			array('title' => 'Cell', 'items'  => array(
				array('title' => 'Left', 'selector' => 'td', 'format' => 'alignleft', 'icon' => 'alignleft' ),
				array('title' => 'Center', 'selector' => 'td', 'format' => 'aligncenter', 'icon' => 'aligncenter' ),
				array('title' => 'Right', 'selector' => 'td', 'format' => 'alignright', 'icon' => 'alignright' ),
				array('title' => 'bs-visualeditor-aligntop', 'selector' => 'td', 'styles' => array( 'vertical-align' => 'top') ),
				array('title' => 'bs-visualeditor-alignmiddle', 'selector' => 'td', 'styles' => array( 'vertical-align' => 'middle') ),
				array('title' => 'bs-visualeditor-alignbottom', 'selector' => 'td', 'styles' => array( 'vertical-align' => 'bottom') )
			)),
			array('title' => 'Pre', 'block' => 'pre', 'classes' => 'bs_pre_from_space'),
			array('title' => 'Paragraph', 'block' => 'p')
		),
		'contextmenu' => 'bsContextMenuMarker image | inserttable bstableprops bsdeletetable bscell bsrow bscolumn'
	);

	/**
	 * Default value for config of reduced version of the editor, which is currently stored in a private variable.
	 * @var array will be JSON encoded later for configuration.
	 */
	private $aConfigOverwrite = array(
		'toolbar1' => array(
			'bswiki', 'bsswitch', 'save', '|', 'undo', 'redo', '|',
			'bold', 'italic', 'underline', 'strikethrough', '|',
			'alignleft', 'aligncenter', 'alignright', 'alignjustify'
		),
		'toolbar2' => array(
			'bssignature', 'bslink', 'unlink',
			'bscategory', 'bschecklist', 'bslinebreak', '|', 'fullscreen'
		)
	);
	private $aMergeToString = array(
		'plugins', 'toolbar1', 'toolbar2'
	);
	protected $bShowToolbarIcon = true;

	/**
	 * Constructor of VisualEditor class
	 */
	public function __construct() {
		wfProfileIn('BS::' . __METHOD__);
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME => 'VisualEditor',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-visualeditor-desc' )->escaped(),
			EXTINFO::AUTHOR => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array('bluespice' => '2.22.0')
		);
		$this->mExtensionKey = 'MW::VisualEditor';

		BsConfig::registerVar( 'MW::VisualEditor::disableNS', array( NS_MEDIAWIKI ), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_INT | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-visualeditor-pref-disablens', 'multiselectex');
		BsConfig::registerVar( 'MW::VisualEditor::defaultNoContextNS', array( NS_SPECIAL, NS_MEDIA, NS_FILE ), BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_ARRAY_INT );

		BsConfig::registerVar( 'MW::VisualEditor::SpecialTags', array(), BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_BOOL );
		BsConfig::registerVar( 'MW::VisualEditor::AllowedTags', array(), BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_BOOL );

		BsConfig::registerVar( 'MW::VisualEditor::Use', true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-visualeditor-pref-use', 'toggle');
		BsConfig::registerVar( 'MW::VisualEditor::UseLimited', false, BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_BOOL );
		BsConfig::registerVar( 'MW::VisualEditor::UseForceLimited', false, BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_BOOL );

		BsConfig::registerVar( 'MW::VisualEditor::DebugMode', false, BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_BOOL );
		BsConfig::registerVar( 'MW::VisualEditor::GuiMode', true, BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_BOOL );
		BsConfig::registerVar( 'MW::VisualEditor::GuiSwitchable', true, BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_BOOL );

		wfProfileOut('BS::' . __METHOD__);
	}

	/**
	 * Constructor of VisualEditor class
	 */
	protected function initExt() {
		$this->mCore->registerBehaviorSwitch(
			'NOEDITOR', array( $this, 'noEditorCallback' )
		);

		// Hooks
		$this->setHook( 'ParserAfterTidy' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSExtendedEditBarBeforeEditToolbar' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
	}

	public function onBSExtendedEditBarBeforeEditToolbar(&$aRows, &$aButtonCfgs) {
		if ( !$this->checkContext( $this->getTitle() ) ) {
			return true;
		}

		$aRows[0]['editing'][10] = 'bs-editbutton-visualeditor';

		$aButtonCfgs['bs-editbutton-visualeditor'] = array(
			'tip' => wfMessage('bs-visualeditor-editbutton-hint')->plain()
		);
		return true;
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object $oResponse reference
	 * $param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if ( $type != 'switches' ) return true;

		$oResponse->result[] = array(
			'id' => '__NOEDITOR__',
			'type' => 'switch',
			'name' => 'NOEDITOR',
			'desc' => wfMessage( 'bs-visualeditor-switch-noeditor-desc' )->plain(),
			'code' => '__NOEDITOR__',
		);

		return true;
	}

	/**
	 * Compiles a list of tags that must be passed by the editor.
	 * @global Language $wgLang
	 * @global OutputPage $wgOut
	 * @param Parser $oParser MediaWiki parser object.
	 * @return bool Allow other hooked methods to be executed. Always true.
	 */
	public function onParserAfterTidy(&$oParser) {
		global $wgLang, $wgOut;

		if ($this->bTagsCollected) return true;
		$this->bTagsCollected = true;

		$tags = $oParser->getTags();
		$allowedTags = '';
		$specialTags = '';
		foreach ($tags as $tag) {
			if ($tag == 'pre')
				continue;
			$allowedTags .= $tag . '[*],';
			if ($tag == 'nowiki')
				continue;
			$specialTags .= $tag . '|';
		}
		$allowedTags .= 'div[*],';

		BsConfig::set('MW::VisualEditor::SpecialTags', $specialTags);
		BsConfig::set('MW::VisualEditor::AllowedTags', $allowedTags);

		//TODO: There are duplicates!
		$aDefaultTags = array(
			"syntaxhighlight", "source", "infobox", "categorytree",
			"presentation", "includeonly", "onlyinclude", "noinclude",
			"backlink", "gallery", "math", "video", "rss", "tagcloud"
		);
		$this->aConfigStandard["specialtaglist"] = BsConfig::get('MW::VisualEditor::SpecialTags')
				. implode('|', $aDefaultTags);

		$this->aConfigStandard["extended_valid_elements"] = BsConfig::get('MW::VisualEditor::AllowedTags')
				. implode('[*],', $aDefaultTags);

		// find the right language file
		$language = $wgLang->getCode();
		$langDir = __DIR__ . DS . 'resources' . DS . 'tinymce' . DS . 'langs';
		if (!file_exists("{$langDir}" . DS . "{$language}.js")) {
			//i don't know what language files use underscores, but i'll leave it here
			$aLanguage = explode('_', $language);
			if (count($aLanguage)<2)
				$aLanguage = explode('-', $language);
			if (file_exists("{$langDir}" . DS . "{$aLanguage[0]}.js")) {
				$language = $aLanguage[0];
			} else {
				$language = 'en';
			}
		}
		$this->aConfigStandard['language'] = $language;

		$aLoaderUsingDeps = array(
			'ext.bluespice'
		);
		// TODO SW: use string flag as parameter to allow hookhandler to
		// determin context. This will be usefull if hook gets called in
		// another place
		wfRunHooks(
			'VisualEditorConfig',
			array(
				&$this->aConfigStandard,
				&$this->aConfigOverwrite,
				&$aLoaderUsingDeps
			)
		);

		foreach(  $this->aConfigStandard['style_formats'] as &$aStyles ){
			foreach ( $aStyles as $key => &$val ){
				if ( $key == "title" ) {
					$oMsg = wfMessage($val);
				}
				if ( $oMsg->exists() ) {
					$val = $oMsg->plain();
				} elseif ( $key == "items" && is_array($val) ){
					foreach ( $val as &$item ) {
						$oMsg = wfMessage($item['title']);
						if ( $oMsg->exists() ) {
							$item['title'] = $oMsg->plain();
						}
					}
				}
			}
		}

		$this->aConfigStandard = $this->_prepareConfig($this->aConfigStandard);
		$this->aConfigOverwrite = $this->_prepareConfig($this->aConfigOverwrite);

		$wgOut->addJsConfigVars('BsVisualEditorConfigDefault', $this->aConfigStandard);
		$wgOut->addJsConfigVars('BsVisualEditorConfigAlternative', array_merge(
			$this->aConfigStandard, $this->aConfigOverwrite
		));
		$wgOut->addJsConfigVars('BsVisualEditorLoaderUsingDeps', $aLoaderUsingDeps);

		return true;
	}

	protected function _prepareConfig($config) {
		$tmp = array();

		foreach ($config as $key => $value) {
			if (in_array($key, $this->aMergeToString)) {
				$tmp[$key] = join(' ', $value);
			} else {
				$tmp[$key] = $value;
			}
		}

		return $tmp;
	}

	/*
	 * Adds module
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean Always true
	 */

	public function onBeforePageDisplay($out, $skin) {

		if ($this->checkContext($out->getTitle()) === false) {
			$this->noEditorCallback();
			return true;
		}

		$sAction = $out->getRequest()->getVal('action', 'view');
		if ($sAction != 'edit' && $sAction != 'preview' && $sAction != 'submit')
			return true;

		$out->addModuleStyles('ext.bluespice.visualEditor.styles');
		// $out->addModules breaks IE8
		$out->addModuleScripts('ext.bluespice.visualEditor.tinymce');
		$out->addModuleScripts('ext.bluespice.visualEditor');

		$out->addModuleMessages('ext.bluespice.visualEditor.tinymce');

		return true;
	}

	/**
	 * Callback function in case __NOEDITOR__ keyword is found. Basically removes toggle button
	 */
	public function noEditorCallback() {
		$this->bShowToolbarIcon = false;

		$this->bStartEditor = false;
		//Overwrite user setting
		$this->getOutput()->addJsConfigVars('bsVisualEditorUse', false);
		BsConfig::set('MW::VisualEditor::Use', false, true); //This seems to be too late
	}

	/**
	 *
	 * @global User $wgUser
	 * @global Language $wgLang
	 * @return string
	 */
	public static function doSaveArticle() {
		if (BsCore::checkAccessAdmission('read') === false)
			return true;
		global $wgLang, $wgRequest;
		$sArticleId = $wgRequest->getInt('articleId', 0);
		$sText = $wgRequest->getVal('text', '');
		$sPageName = $wgRequest->getVal('pageName', '');
		$sSummary = $wgRequest->getVal('summary', '');
		$iSection = $wgRequest->getInt('editsection', 0);

		$sReturnEditTime = wfTimestampNow();
		if ($sSummary == 'false') {
			$sSummary = '/* '.wfMessage( 'bs-visualeditor-no-summary' )->plain().' */';
		}

		$oArticle = Article::newFromID($sArticleId);
		if ( $oArticle === null ) {
			$oArticle = new Article(Title::newFromText($sPageName));
		}

		if ($iSection) {
			$sText = $oArticle->replaceSection($iSection, $sText);
		}

		$oSaveResult = $oArticle->doEdit($sText, $sSummary);

		$sTime = $wgLang->timeanddate($sReturnEditTime, true);
		$sMessage = '';
		$sResult = '';
		if (empty($oSaveResult->errors)) {
			$sResult = 'ok';
			$sMessage = wfMessage( 'bs-visualeditor-save-message', $sTime, $sSummary )->plain();
		} else {
			$sResult = 'fail';
			$sMessage = $oSaveResult->getMessage();
		}

		$aOutput = array(
			'saveresult' => $sResult, //$oSaveResult->getMessage(),//$sSaveResultCode,
			'message' => $sMessage, //wfMessage( 'bs-visualeditor-save-message', $sTime, $sSummary )->plain(),
			'edittime' => $sReturnEditTime,
			'summary' => $sSummary,
			'starttime' => wfTimestamp(TS_MW, time() + 2)
		);

		return FormatJson::encode($aOutput);
	}

	public static function checkLinks($links) {
		$aResult = array();
		foreach ($links as $sTitle) {
			$oTitle = Title::newFromText(urldecode($sTitle));
			$aResult[] = $oTitle instanceof Title ? $oTitle->exists() : false;
		}
		return FormatJson::encode($aResult);
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin($sAdapterName, $oVariable) {
		wfProfileIn('BS::' . __METHOD__);
		$aPrefs = array();

		switch ($oVariable->getName()) {
			case 'disableNS':
				global $wgContLang;
				$aExcludeNmsps = BsConfig::get('MW::VisualEditor::defaultNoContextNS');
				foreach ($wgContLang->getNamespaces() as $sNamespace) {
					$iNsIndex = $wgContLang->getNsIndex($sNamespace);
					if (!MWNamespace::isTalk($iNsIndex))
						continue;
					$aExcludeNmsps[] = $iNsIndex;
				}
				$aPrefs['type'] = 'multiselectex';
				$aPrefs['options'] = BsNamespaceHelper::getNamespacesForSelectOptions($aExcludeNmsps);
				break;
			default:
		}

		wfProfileOut('BS::' . __METHOD__);
		return $aPrefs;
	}

	/**
	 * Checks wether to set Context or not.
	 * @param Title $oTitle
	 * @return bool
	 */
	private function checkContext($oTitle) {
		if (!is_object($oTitle))
			return false;

		global $wgRequest;
		if ($wgRequest->getVal('action') !== 'edit' && $wgRequest->getVal('action') !== 'submit')
			return false;

		if (!$oTitle->userCan('edit'))
			return false;

		$aExcludeNmsps = BsConfig::get('MW::VisualEditor::defaultNoContextNS');
		$aExcludeNmsps = array_merge(
				$aExcludeNmsps, BsConfig::get('MW::VisualEditor::disableNS')
		);
		if (in_array($oTitle->getNamespace(), $aExcludeNmsps))
			return false;

		return true;
	}

}
