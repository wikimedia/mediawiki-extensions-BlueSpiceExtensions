<?php

BsExtensionManager::registerExtension('VisualEditor', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['VisualEditor'] = __DIR__ . '/VisualEditor.class.php';

$wgMessagesDirs['VisualEditor'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['VisualEditor'] = __DIR__ . '/languages/VisualEditor.i18n.php';
$wgExtensionMessagesFiles['VisualEditorMagic'] = __DIR__ . '/languages/VisualEditor.i18n.magic.php';

$wgAjaxExportList[] = 'VisualEditor::doSaveArticle';
$wgAjaxExportList[] = 'VisualEditor::checkLinks';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/VisualEditor/resources'
);

/**
 * This is the pure TinyMCE implementation. Use this module, if you just need the
 * TinyMCE in a general mediawiki context (eg. GuidedEditing)
 */
$wgResourceModules['ext.bluespice.visualEditor.tinymce'] = array(
	'scripts' => array(
		'tinymce/tinymce.jquery.js',
		'tinymce.startup.js'
	),
	'dependencies' => array(
		'jquery'
	),
	'messages' => array(
		'bs-visualeditor-bsactions-wiki',
		'bs-visualeditor-bsactions-switchgui',
		'bs-visualeditor-bsactions-linebreak',
		'bs-visualeditor-bsactions-signature',
		'bs-visualeditor-bsactions-save',
		'bs-visualeditor-bsactions-headings',
		'bs-visualeditor-bsactions-paragraph',
		'bs-visualeditor-bsactions-heading2',
		'bs-visualeditor-bsactions-heading3',
		'bs-visualeditor-bsactions-heading4',
		'bs-visualeditor-bsactions-heading5',
		'bs-visualeditor-bsactions-heading6',
	)
) + $aResourceModuleTemplate;

/**
 * This is the VisualEditor implementation which loads and prepares TinyMCE for the
 * default Mediawiki edit page.
 */
$wgResourceModules['ext.bluespice.visualEditor'] = array(
	'scripts' => array(
		'bluespice.visualEditor.js',
	),
	'dependencies' => array(
		'ext.bluespice.visualEditor.tinymce'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.visualEditor.styles'] = array(
	'styles' => array(
		'bluespice.visualEditor.css',
	)
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );