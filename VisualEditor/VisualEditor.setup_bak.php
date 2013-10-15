<?php

BsExtensionManager::registerExtension('VisualEditor', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['VisualEditor']      = __DIR__ . '/languages/VisualEditor.i18n.php';
$wgExtensionMessagesFiles['VisualEditorMagic'] = __DIR__ . '/languages/VisualEditor.i18n.magic.php';

$wgAjaxExportList[] = 'VisualEditor::doSaveArticle';
$wgAjaxExportList[] = 'VisualEditor::checkLinks';

$wgResourceModules['ext.bluespice.visualEditor'] = array(
	'scripts' => array(
		# 'extensions/BlueSpiceExtensions/VisualEditor/resources/tiny_mce/tiny_mce_src.js',
		'extensions/BlueSpiceExtensions/VisualEditor/resources/tinymce/tinymce.jquery.js',
		'extensions/BlueSpiceExtensions/VisualEditor/resources/bluespice.visualEditor.js',
	),
	'styles' => array(
		# 'extensions/BlueSpiceExtensions/VisualEditor/resources/bluespice.visualEditor.css',
	),
    'dependencies' => array(
        'jquery'
    ),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);