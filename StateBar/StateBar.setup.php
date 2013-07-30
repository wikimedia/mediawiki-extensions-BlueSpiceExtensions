<?php

BsExtensionManager::registerExtension('StateBar',                        BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['StateBar'] = dirname( __FILE__ ).'/languages/StateBar.i18n.php';

$wgResourceModules['ext.bluespice.statebar'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/StateBar/resources/bluespice.StateBar.js',
	'styles'  => 'extensions/BlueSpiceExtensions/StateBar/resources/bluespice.StateBar.css',
	'position' => 'top',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['ViewStateBar'] = dirname( __FILE__ ).'/views/view.StateBar.php';
$wgAutoloadClasses['ViewStateBarTopElement'] = dirname( __FILE__ ).'/views/view.StateBarTopElement.php';
$wgAutoloadClasses['ViewStateBarBodyElement'] = dirname( __FILE__ ).'/views/view.StateBarBodyElement.php';
