<?php

BsExtensionManager::registerExtension('Preferences', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$GLOBALS['wgAutoloadClasses']['Preferences'] = __DIR__ . '/Preferences.class.php';

$wgMessagesDirs['Preferences'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['Preferences'] = __DIR__ . '/languages/Preferences.i18n.php';

$wgHooks['BeforePageDisplay'][] = "BsPreferences::onBeforePageDisplay";

$aResourceModuleTemplate = array(
	'localBasePath' => 'extensions/BlueSpiceExtensions/Preferences/resources/',
	'remoteExtPath' => 'BlueSpiceExtensions/Preferences/resources'
);

$wgResourceModules['ext.bluespice.preferences'] = array(
	'scripts' => 'bluespice.preferences.js'
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );