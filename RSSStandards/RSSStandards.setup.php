<?php

BsExtensionManager::registerExtension('RSSStandards', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgAutoloadClasses['RSSStandards'] = __DIR__ . '/RSSStandards.class.php';

$wgMessagesDirs['RSSStandards'] = __DIR__ . '/i18n';

$wgResourceModules['ext.bluespice.rssStandards'] = array(
	'scripts' => 'bluespice.rssStandards.js',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/RSSStandards/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/RSSStandards/resources',
	'dependencies' => array(
		'ext.bluespice.extjs'
	)
);

$wgAjaxExportList[] = 'RSSStandards::getPages';