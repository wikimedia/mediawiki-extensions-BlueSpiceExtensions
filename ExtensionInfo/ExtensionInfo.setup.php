<?php
BsExtensionManager::registerExtension('ExtensionInfo',                   BsRUNLEVEL::FULL, BsACTION::LOAD_SPECIALPAGE);

$dir = dirname( __FILE__ );
$wgExtensionMessagesFiles['ExtensionInfo']      = $dir . '/ExtensionInfo.i18n.php';
$wgExtensionMessagesFiles['ExtensionInfoAlias'] = $dir . '/SpecialExtensionInfo.alias.php';

$wgAutoloadClasses['SpecialExtensionInfo'] = $dir . '/SpecialExtensionInfo.class.php';

$wgSpecialPageGroups['ExtensionInfo'] = 'bluespice';

$wgSpecialPages['ExtensionInfo'] = 'SpecialExtensionInfo';

$wgResourceModules['ext.bluespice.extensioninfo'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/ExtensionInfo/js/ExtensionInfo.js',
	'styles'  => 'extensions/BlueSpiceExtensions/ExtensionInfo/css/ExtensionInfo.css',
	'messages' => array(
		'bs-extensioninfo-headerExtensionname',
		'bs-extensioninfo-headerVersion',
		'bs-extensioninfo-headerDescription',
		'bs-extensioninfo-headerStatus',
		'bs-extensioninfo-btnClearGrouping',
		'bs-extensioninfo-groupingTemplateViewTextSingular',
		'bs-extensioninfo-groupingTemplateViewTextPlural'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);