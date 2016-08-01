<?php

BsExtensionManager::registerExtension('RSSStandards', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgAutoloadClasses['RSSStandards'] = __DIR__ . '/RSSStandards.class.php';
$wgAutoloadClasses['ApiRSSStandardsPagesStore'] = __DIR__ . '/includes/api/ApiRSSStandardsPagesStore.php';

$wgMessagesDirs['RSSStandards'] = __DIR__ . '/i18n';

$wgAPIModules['bs-rss-standards-pages-store'] = 'ApiRSSStandardsPagesStore';

$wgResourceModules['ext.bluespice.rssStandards'] = array(
	'scripts' => 'bluespice.rssStandards.js',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/RSSStandards/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/RSSStandards/resources',
	'dependencies' => array(
		'ext.bluespice.extjs'
	)
);
