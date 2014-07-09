<?php

BsExtensionManager::registerExtension('TopMenuBarCustomizer',            BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['TopMenuBarCustomizer'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['TopMenuBarCustomizer'] = __DIR__ . '/languages/TopMenuBarCustomizer.i18n.php';

$wgResourceModules['ext.bluespice.topmenubarcustomizer'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/TopMenuBarCustomizer/resources/bluespice.TopMenuBarCustomizer.js',
	'styles'  => 'extensions/BlueSpiceExtensions/TopMenuBarCustomizer/resources/bluespice.TopMenuBarCustomizer.css',
	'position' => 'top',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['ViewTopMenuItem'] = __DIR__.'/views/view.TopMenuItem.php';
$wgAutoloadClasses['ViewTopMenuItemMain'] = __DIR__.'/views/view.TopMenuItemMain.php';