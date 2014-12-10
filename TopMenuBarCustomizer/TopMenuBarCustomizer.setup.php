<?php
BsExtensionManager::registerExtension( 'TopMenuBarCustomizer', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$aResourceModuleTemplate = array(
	'dependencies' => 'ext.bluespice',
	'localBasePath' => "$IP/extensions/BlueSpiceExtensions/TopMenuBarCustomizer/resources",
	'remoteExtPath' => 'BlueSpiceExtensions/TopMenuBarCustomizer/resources'
);

$wgMessagesDirs['TopMenuBarCustomizer'] = __DIR__."/i18n";

$wgExtensionMessagesFiles['TopMenuBarCustomizer'] = __DIR__."/languages/TopMenuBarCustomizer.i18n.php";

$GLOBALS['wgAutoloadClasses']['TopMenuBarCustomizer'] = __DIR__ . '/TopMenuBarCustomizer.class.php';
$wgAutoloadClasses['TopMenuBarCustomizerParser'] = __DIR__."/includes/TopMenuBarCustomizerParser.php";
$wgAutoloadClasses['ViewTopMenuItem'] = __DIR__."/views/view.TopMenuItem.php";

$wgResourceModules['ext.bluespice.topmenubarcustomizer'] = array(
	'scripts' => 'bluespice.TopMenuBarCustomizer.js',
	'position' => 'top',
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.topmenubarcustomizer.styles'] = array(
	'styles'  => 'bluespice.TopMenuBarCustomizer.css',
	'position' => 'top',
) + $aResourceModuleTemplate;

unset($aResourceModuleTemplate);