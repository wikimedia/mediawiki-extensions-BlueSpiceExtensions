<?php

BsExtensionManager::registerExtension('InsertMagic', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['InsertMagic'] = __DIR__ . '/languages/InsertMagic.i18n.php';

$wgAutoloadClasses['InsertMagic'] = __DIR__ . '/InsertMagic.class.php';
$wgAjaxExportList[] = 'InsertMagic::ajaxGetData';

$wgResourceModules['ext.bluespice.insertMagic'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/InsertMagic/resources/bluespice.insertMagic.js',
	),
	'styles' => array(
		'extensions/BlueSpiceExtensions/InsertMagic/resources/bluespice.insertMagic.css',
	),
	'dependencies' => array(
		'ext.bluespice.extjs'
	),
	'messages' => array(
		'bs-insertmagic-dlg_title',
		'bs-insertmagic-type_tags',
		'bs-insertmagic-type_variables',
		'bs-insertmagic-type_switches',
		'bs-insertmagic-type_redirect',
		'bs-insertmagic-btn_preview',
		'bs-insertmagic-label_first',
		'bs-insertmagic-label_second',
		'bs-insertmagic-label_third',
		'bs-insertmagic-label_desc'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);
