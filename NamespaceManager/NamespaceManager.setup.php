<?php

BsExtensionManager::registerExtension('NamespaceManager', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgMessagesDirs['NamespaceManager'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['NamespaceManager'] = __DIR__ . '/languages/NamespaceManager.i18n.php';

$wgResourceModules['ext.bluespice.namespaceManager'] = array(
	'scripts' => 'resources/bluespice.namespaceManager.js',
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
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'BlueSpiceExtensions/NamespaceManager'
);

$GLOBALS['wgAutoloadClasses']['NamespaceManager'] = __DIR__ . '/NamespaceManager.class.php';
$GLOBALS['wgAutoloadClasses']['NamespaceNuker'] = __DIR__ . '/includes/NamespaceNuker.php';
$GLOBALS['wgAutoloadClasses']['BSApiNamespaceStore'] = __DIR__ . '/includes/api/BSApiNamespaceStore.php';
$GLOBALS['wgAutoloadClasses']['BSApiNamespaceTasks'] = __DIR__ . '/includes/api/BSApiNamespaceTasks.php';

$wgAPIModules['bs-namespace-store'] = 'BSApiNamespaceStore';
$wgAPIModules['bs-namespace-tasks'] = 'BSApiNamespaceTasks';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'NamespaceManager::onLoadExtensionSchemaUpdates';

$wgLogTypes[] = 'bs-namespace-manager';
$wgFilterLogTypes['bs-namespace-manager'] = true;
$wgLogActionsHandlers['bs-namespace-manager/*'] = 'LogFormatter';

$bsgConfigFiles['NamespaceManager'] = BSCONFIGDIR . DS . 'nm-settings.php';