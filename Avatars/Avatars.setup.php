<?php

BsExtensionManager::registerExtension('Avatars', BsRUNLEVEL::FULL | BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgMessagesDirs['Avatars'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['Avatars'] = __DIR__ . '/languages/Avatars.i18n.php';

$GLOBALS['wgAutoloadClasses']['Avatars'] = __DIR__ . '/Avatars.class.php';

$wgHooks['BeforePageDisplay'][] = "Avatars::onBeforePageDisplay";

$wgResourceModules['ext.bluespice.avatars.js'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/Avatars/resources/bluespice.avatars.js',
	),
	'messages' => array(
		'bs-avatars-upload-title',
		'bs-avatars-upload-label',
		'bs-avatars-generate-new-label',
		'bs-avatars-warning-title',
		'bs-avatars-warning-text',
		'bs-avatars-userimage-title',
		'bs-avatars-set-userimage-failed',
		'bs-avatars-set-userimage-saved',
		'bs-avatars-userimage-help',
		'bs-avatars-file-upload-fieldset-title',
		'bs-avatars-userimage-title',
		'bs-avatars-auto-generate-fieldset-title'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgForeignFileRepos[] = array(
	'class' => 'FSRepo',
	'name' => 'Avatars',
	'directory' => BS_DATA_DIR . '/Avatars/',
	'hashLevels' => 0,
	'url' => BS_DATA_PATH . '/Avatars',
);

$wgAjaxExportList[] = 'Avatars::uploadFile';
$wgAjaxExportList[] = 'Avatars::generateAvatarAjax';
$wgAjaxExportList[] = 'Avatars::setUserImage';