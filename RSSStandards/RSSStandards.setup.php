<?php

BsExtensionManager::registerExtension('RSSStandards', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['RSSStandards'] = __DIR__ . '/RSSStandards.class.php';

$wgMessagesDirs['RSSStandards'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['RSSStandards'] = __DIR__ . '/languages/RSSStandards.i18n.php';

$wgResourceModules['ext.bluespice.rssStandards'] = array(
	'scripts' => 'bluespice.rssStandards.js',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/RSSStandards/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/RSSStandards/resources',
);

$wgAjaxExportList[] = 'RSSStandards::getPages';