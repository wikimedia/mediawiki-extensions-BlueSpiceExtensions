<?php

BsExtensionManager::registerExtension('WhoIsOnline', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['WhoIsOnline'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['WhoIsOnline'] = __DIR__ . '/languages/WhoIsOnline.i18n.php';
$wgExtensionMessagesFiles['WhoIsOnlineMagic'] = __DIR__ . '/languages/WhoIsOnline.i18n.magic.php';

$wgResourceModules['ext.bluespice.whoisonline'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/WhoIsOnline/resources/bluespice.whoIsOnline.js',
	'position' => 'bottom',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$GLOBALS['wgAutoloadClasses']['WhoIsOnline'] = __DIR__ . '/WhoIsOnline.class.php';
$wgAutoloadClasses['ViewWhoIsOnlineTag'] = __DIR__ . '/views/view.WhoIsOnlineTag.php';
$wgAutoloadClasses['ViewWhoIsOnlineItemWidget'] = __DIR__ . '/views/view.WhoIsOnlineItemWidget.php';
$wgAutoloadClasses['ViewWhoIsOnlineWidget'] = __DIR__ . '/views/view.WhoIsOnlineWidget.php';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'WhoIsOnline::getSchemaUpdates';