<?php

BsExtensionManager::registerExtension('WhoIsOnline', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['WhoIsOnline'] = __DIR__ . '/WhoIsOnline.i18n.php';

$wgResourceModules['ext.bluespice.whoisonline'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/WhoIsOnline/resources/WhoIsOnline.js',
	'position' => 'bottom',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$GLOBALS['wgAutoloadClasses']['WhoIsOnline'] = __DIR__ . '/WhoIsOnline.class.php';
$wgAutoloadClasses['ViewWhoIsOnlineTag'] = __DIR__ . '/views/view.WhoIsOnlineTag.php';
$wgAutoloadClasses['ViewWhoIsOnlineItemWidget'] = __DIR__ . '/views/view.WhoIsOnlineItemWidget.php';
$wgAutoloadClasses['ViewWhoIsOnlineWidget'] = __DIR__ . '/views/view.WhoIsOnlineWidget.php';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'WhoIsOnline::getSchemaUpdates';