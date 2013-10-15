<?php

BsExtensionManager::registerExtension('RSSStandards',                    BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['RSSStandards'] = __DIR__ . '/RSSStandards.i18n.php';

$wgResourceModules['ext.bluespice.rssStandards'] = array(
	'scripts' => 'bluespice.rssStandards.js',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/RSSStandards/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/RSSStandards/resources',
);

$wgAjaxExportList[] = 'RSSStandards::getPages';
