<?php

BsExtensionManager::registerExtension('Avatars', BsRUNLEVEL::FULL | BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['Avatars'] = __DIR__ . '/languages/Avatars.i18n.php';
$wgExtensionMessagesFiles['AvatarsAlias'] = __DIR__ . '/languages/SpecialAvatars.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)

$wgAutoloadClasses['SpecialAvatars'] = __DIR__ . '/includes/specials/SpecialAvatars.class.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgAutoloadClasses['Avatars'] = __DIR__ . '/Avatars.class.php';

$wgHooks['BeforePageDisplay'][] = "Avatars::onBeforePageDisplay";
#$wgSpecialPageGroups['Avatars'] = 'bluespice';
#$wgSpecialPages['Avatars'] = 'SpecialAvatars'; # Tell MediaWiki about the new special page and its class name

$wgResourceModules['ext.bluespice.avatars.js'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/Avatars/resources/bluespice.avatars.js',
	),
	'messages' => array(
		'bs-avatars-upload-title',
		'bs-avatars-upload-label',
		'bs-avatars-generate-new-label',
		'bs-avatars-warning-label',
		'bs-avatars-warning-text',
		'bs-avatars-userimage-label',
		'bs-avatars-set-userimage-failed',
		'bs-avatars-set-userimage-saved',
		'bs-avatars-userimage-save-button',
		'bs-avatars-userimage-help',
		'bs-avatars-cancel-button',
		'bs-avatars-file-upload-fieldset-title',
		'bs-avatars-user-image-fieldset-title',
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