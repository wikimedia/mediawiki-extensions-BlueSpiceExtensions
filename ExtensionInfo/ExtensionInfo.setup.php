<?php
BsExtensionManager::registerExtension('ExtensionInfo',  BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$GLOBALS['wgAutoloadClasses']['ExtensionInfo'] = __DIR__ . '/ExtensionInfo.class.php';

$wgMessagesDirs['ExtensionInfo'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['ExtensionInfo'] = __DIR__ . '/languages/ExtensionInfo.i18n.php';
$wgExtensionMessagesFiles['ExtensionInfoAlias'] = __DIR__ . '/languages/SpecialExtensionInfo.alias.php';

$wgAutoloadClasses['SpecialExtensionInfo'] = __DIR__ . '/includes/specials/SpecialExtensionInfo.class.php';
$wgAutoloadClasses['ViewExtensionInfoTable'] = __DIR__ . '/includes/ViewExtensionInfoTable.php';

$wgSpecialPageGroups['ExtensionInfo'] = 'bluespice';
$wgSpecialPages['ExtensionInfo'] = 'SpecialExtensionInfo';

$wgResourceModules['ext.bluespice.extensioninfo.styles'] = array(
	'styles' => 'bluespice.extensionInfo.css',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/ExtensionInfo/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/ExtensionInfo/resources'
);

$wgResourceModules['ext.bluespice.extensioninfo'] = array(
	'scripts' => 'bluespice.extensionInfo.js',
	'messages' => array(
		'bs-extensioninfo-headerextname',
		'bs-extensioninfo-headerversion',
		'bs-extensioninfo-headerdesc',
		'bs-extensioninfo-headerstatus',
		'bs-extensioninfo-headerpackage',
		'bs-extensioninfo-groupingtemplateviewtext'
	),
	'dependencies' => array(
		'mediawiki.jqueryMsg'
	),
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/ExtensionInfo/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/ExtensionInfo/resources',
);