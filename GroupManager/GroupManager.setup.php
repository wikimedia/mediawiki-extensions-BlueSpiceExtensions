<?php

BsExtensionManager::registerExtension('GroupManager',                    BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['GroupManager'] = __DIR__ . '/GroupManager.i18n.php';

$wgResourceModules['ext.bluespice.groupManager'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/GroupManager/resources/bluespice.groupManager.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-groupmanager-headerGroupname',
		'bs-groupmanager-headerActions',
		'bs-groupmanager-btnAddGroup',
		'bs-groupmanager-tipEdit',
		'bs-groupmanager-tipRemove',
		'bs-groupmanager-titleNewGroup',
		'bs-groupmanager-titleEditGroup',
		'bs-groupmanager-titleError',
		'bs-groupmanager-removeGroup',
		'bs-groupmanager-lableName',
		'bs-groupmanager-msgNotEditable',
		'bs-groupmanager-msgNotRemovable'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAjaxExportList[] = 'GroupManager::getData';
$wgAjaxExportList[] = 'GroupManager::getGroups';
$wgAjaxExportList[] = 'GroupManager::addGroup';
$wgAjaxExportList[] = 'GroupManager::editGroup';
$wgAjaxExportList[] = 'GroupManager::removeGroup';