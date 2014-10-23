<?php

BsExtensionManager::registerExtension('NamespaceManager', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgMessagesDirs['NamespaceManager'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['NamespaceManager'] = __DIR__ . '/languages/NamespaceManager.i18n.php';

$wgResourceModules['ext.bluespice.namespaceManager'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/NamespaceManager/resources/bluespice.namespaceManager.js',
	'styles' => 'extensions/BlueSpiceExtensions/NamespaceManager/resources/bluespice.namespaceManager.namespaceManagerTreeview.css',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-namespacemanager-tipadd',
		'bs-namespacemanager-tipedit',
		'bs-namespacemanager-tipremove',
		'bs-namespacemanager-msgnoteditabledelete',
		'bs-namespacemanager-labelnsname',
		'bs-namespacemanager-willdelete',
		'bs-namespacemanager-willmove',
		'bs-namespacemanager-willmovesuffix',
		'bs-namespacemanager-deletewarning',
		'bs-namespacemanager-pagepresent',
		'bs-namespacemanager-label-editable',
		'bs-ns_main',
		'bs-from-something'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$GLOBALS['wgAutoloadClasses']['NamespaceManager'] = __DIR__ . '/NamespaceManager.class.php';
$wgAutoloadClasses['NamespaceNuker'] = __DIR__ . '/includes/NamespaceNuker.php';

$wgAjaxExportList[] = 'NamespaceManager::getForm';
$wgAjaxExportList[] = 'NamespaceManager::getData';
$wgAjaxExportList[] = 'NamespaceManager::addNamespace';
$wgAjaxExportList[] = 'NamespaceManager::editNamespace';
$wgAjaxExportList[] = 'NamespaceManager::deleteNamespace';
$wgAjaxExportList[] = 'NamespaceManager::isNamespaceEmpty';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'NamespaceManager::getSchemaUpdates';