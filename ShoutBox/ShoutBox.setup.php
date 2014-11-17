<?php

BsExtensionManager::registerExtension('ShoutBox', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['ShoutBox'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['ShoutBox'] = __DIR__ . '/languages/ShoutBox.i18n.php';
$wgExtensionMessagesFiles['ShoutBoxMagic'] = __DIR__ . '/languages/ShoutBox.i18n.magic.php';

$aResourceModuleTemplate = array(
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/ShoutBox/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/ShoutBox/resources'
);

$wgResourceModules['ext.bluespice.shoutbox'] = array(
	'scripts' => 'bluespice.shoutBox.js',
	'dependencies' => 'ext.bluespice',
	'messages' => array(
		'bs-shoutbox-confirm-text',
		'bs-shoutbox-confirm-title',
		'bs-shoutbox-entermessage',
		'bs-shoutbox-too-early',
		'bs-shoutbox-charactersleft',
		'bs-shoutbox-n-shouts'
	),
	'position' => 'bottom'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.shoutbox.styles'] = array(
	'styles' => 'bluespice.shoutBox.css',
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAjaxExportList[] = 'ShoutBox::getShouts';
$wgAjaxExportList[] = 'ShoutBox::insertShout';
$wgAjaxExportList[] = 'ShoutBox::archiveShout';

$GLOBALS['wgAutoloadClasses']['ShoutBox'] = __DIR__ . '/ShoutBox.class.php';
$wgAutoloadClasses['ViewShoutBox'] = __DIR__ . '/views/view.ShoutBox.php';
$wgAutoloadClasses['ViewShoutBoxMessageList'] = __DIR__ . '/views/view.ShoutBoxMessageList.php';
$wgAutoloadClasses['ViewShoutBoxMessage'] = __DIR__ . '/views/view.ShoutBoxMessage.php';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'ShoutBox::getSchemaUpdates';