<?php

BsExtensionManager::registerExtension('VisualEditor', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['VisualEditor']      = __DIR__ . '/languages/VisualEditor.i18n.php';
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
