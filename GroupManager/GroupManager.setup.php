<?php

BsExtensionManager::registerExtension( 'GroupManager', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE );

$GLOBALS['wgAutoloadClasses']['GroupManager'] = __DIR__ . '/GroupManager.class.php';
$wgAutoloadClasses['BSApiTasksGroupManager'] = __DIR__ . '/includes/api/BSApiTasksGroupManager.php';

$wgMessagesDirs['GroupManager'] = __DIR__ . '/i18n';

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

$wgAPIModules['bs-groupmanager'] = 'BSApiTasksGroupManager';

$wgLogTypes[] = 'bs-group-manager';
$wgFilterLogTypes['bs-group-manager'] = true;
$wgLogActionsHandlers['bs-group-manager/*'] = 'LogFormatter';

$bsgConfigFiles['GroupManager'] = BSCONFIGDIR . DS . 'gm-settings.php';