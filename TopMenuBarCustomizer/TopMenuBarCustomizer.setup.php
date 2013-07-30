<?php

BsExtensionManager::registerExtension('TopMenuBarCustomizer',            BsRUNLEVEL::FULL);

$wgExtensionMessagesFiles['TopMenuBarCustomizer'] = dirname( __FILE__ ) . '/languages/TopMenuBarCustomizer.i18n.php';

$wgResourceModules['ext.bluespice.topmenubarcustomizer'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/TopMenuBarCustomizer/resources/bluespice.TopMenuBarCustomizer.js',
	'styles'  => 'extensions/BlueSpiceExtensions/TopMenuBarCustomizer/resources/bluespice.TopMenuBarCustomizer.css',
	'position' => 'top',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['ViewTopMenuItem'] = dirname( __FILE__ ).'/views/view.TopMenuItem.php';
$wgAutoloadClasses['ViewTopMenuItemMain'] = dirname( __FILE__ ).'/views/view.TopMenuItemMain.php';