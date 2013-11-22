<?php
BsExtensionManager::registerExtension('ExtensionInfo',  BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['ExtensionInfo']      = __DIR__ . '/languages/ExtensionInfo.i18n.php';
$wgExtensionMessagesFiles['ExtensionInfoAlias'] = __DIR__ . '/languages/SpecialExtensionInfo.alias.php';

$wgAutoloadClasses['SpecialExtensionInfo']   = __DIR__ . '/includes/specials/SpecialExtensionInfo.class.php';
$wgAutoloadClasses['ViewExtensionInfoTable'] = __DIR__ . '/includes/ViewExtensionInfoTable.php';

$wgSpecialPageGroups['ExtensionInfo'] = 'bluespice';
$wgSpecialPages['ExtensionInfo'] = 'SpecialExtensionInfo';

$wgResourceModules['ext.bluespice.extensioninfo'] = array(
	'scripts' => 'bluespice.extensionInfo.js',
	'styles'  => 'bluespice.extensionInfo.css',
	'messages' => array(
		'bs-extensioninfo-headerExtensionname',
		'bs-extensioninfo-headerVersion',
		'bs-extensioninfo-headerDescription',
		'bs-extensioninfo-headerStatus',
		'bs-extensioninfo-headerPackage',
		'bs-extensioninfo-btnClearGrouping',
		'bs-extensioninfo-groupingTemplateViewTextSingular',
		'bs-extensioninfo-groupingTemplateViewTextPlural'
	),
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/ExtensionInfo/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/ExtensionInfo/resources',
);