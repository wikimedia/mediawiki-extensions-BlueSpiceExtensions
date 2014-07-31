<?php

BsExtensionManager::registerExtension( 'GroupManager', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE );

$GLOBALS['wgAutoloadClasses']['GroupManager'] = __DIR__ . '/GroupManager.class.php';

$wgMessagesDirs['GroupManager'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['GroupManager'] = __DIR__ . '/languages/GroupManager.i18n.php';

$wgResourceModules['ext.bluespice.groupManager'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/GroupManager/resources/bluespice.groupManager.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-groupmanager-headergroup',
		'bs-groupmanager-tipremove',
		'bs-groupmanager-titlenewgroup',
		'bs-groupmanager-titleeditgroup',
		'bs-groupmanager-removegroup',
		'bs-groupmanager-lablename',
		'bs-groupmanager-msgnoteditable',
		'bs-groupmanager-msgnotremovable',
		'bs-groupmanager-removegroup-message-success',
		'bs-groupmanager-removegroup-message-failure'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAjaxExportList[] = 'GroupManager::getData';
$wgAjaxExportList[] = 'GroupManager::getGroups';
$wgAjaxExportList[] = 'GroupManager::addGroup';
$wgAjaxExportList[] = 'GroupManager::editGroup';
$wgAjaxExportList[] = 'GroupManager::removeGroup';
$wgAjaxExportList[] = 'GroupManager::removeGroups';