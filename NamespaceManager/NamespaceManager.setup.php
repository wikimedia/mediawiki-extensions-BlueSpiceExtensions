<?php

BsExtensionManager::registerExtension('NamespaceManager',                BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgResourceModules['ext.bluespice.namespaceManager'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/NamespaceManager/resources/bluespice.namespaceManager.js',
	'styles' => 'extensions/BlueSpiceExtensions/NamespaceManager/resources/bluespice.namespaceManager.namespaceManagerTreeview.css',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-namespacemanager-headerNamespaceId',
		'bs-namespacemanager-headerNamespaceName',
		'bs-namespacemanager-headerIsUserNamespace',
		'bs-namespacemanager-headerIsContentNamespace',
		'bs-namespacemanager-headerIsSearchableNamespace',
		'bs-namespacemanager-headerIsSubpagesNamespace',
		'bs-namespacemanager-headerActions',
		'bs-namespacemanager-yes',
		'bs-namespacemanager-no',
		'bs-namespacemanager-btnAddNamespace',
		'bs-namespacemanager-tipEdit',
		'bs-namespacemanager-tipRemove',
		'bs-namespacemanager-msgNotEditable',
		'bs-namespacemanager-msgNotEditableDelete',
		'bs-namespacemanager-titleNewNamespace',
		'bs-namespacemanager-labelNamespaceName',
		'bs-namespacemanager-emptyMsgNamespaceName',
		'bs-namespacemanager-labelContentNamespace',
		'bs-namespacemanager-labelSearchedNamespace',
		'bs-namespacemanager-labelSubpagesNamespace',
		'bs-namespacemanager-btnSave',
		'bs-namespacemanager-btnCancel',
		'bs-namespacemanager-titleError',
		'bs-namespacemanager-willDelete',
		'bs-namespacemanager-willMove',
		'bs-namespacemanager-willMoveSuffix',
		'bs-namespacemanager-deletewarning',
		'bs-namespacemanager-moveConflict',
		'bs-namespacemanager-articlesPresent',
		'bs-namespacemanager-btnDelete',
		'bs-namespacemanager-deleteNamespace',
		'bs-namespacemanager-showEntries',
		'bs-namespacemanager-pageSize',
		'bs-namespacemanager-label-editable'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgExtensionMessagesFiles['NamespaceManager'] = __DIR__ . '/NamespaceManager.i18n.php';

$GLOBALS['wgAutoloadClasses']['NamespaceManager'] = __DIR__ . '/NamespaceManager.class.php';
$wgAutoloadClasses['NamespaceNuker'] = __DIR__ . '/NamespaceNuker.php';

$wgAjaxExportList[] = 'NamespaceManager::getForm';
$wgAjaxExportList[] = 'NamespaceManager::getData';
$wgAjaxExportList[] = 'NamespaceManager::addNamespace';
$wgAjaxExportList[] = 'NamespaceManager::editNamespace';
$wgAjaxExportList[] = 'NamespaceManager::deleteNamespace';
$wgAjaxExportList[] = 'NamespaceManager::isNamespaceEmpty';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'NamespaceManager::getSchemaUpdates';