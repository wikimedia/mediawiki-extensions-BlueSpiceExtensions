<?php

BsExtensionManager::registerExtension('WidgetBar',                       BsRUNLEVEL::FULL);

$wgExtensionMessagesFiles['WidgetBar'] = dirname( __FILE__ ) . '/WidgetBar.i18n.php';

$wgResourceModules['ext.bluespice.widgetbar'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/WidgetBar/js/WidgetBar.js',
	'styles'  => 'extensions/BlueSpiceExtensions/WidgetBar/css/WidgetBar.css',
	'position' => 'top',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);