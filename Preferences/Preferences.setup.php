<?php

BsExtensionManager::registerExtension('Preferences', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgAutoloadClasses['BsPreferences'] = __DIR__ . '/Preferences.class.php';

$wgMessagesDirs['Preferences'] = __DIR__ . '/i18n';

$wgHooks['BeforePageDisplay'][] = "BsPreferences::onBeforePageDisplay";

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'BlueSpiceExtensions/Preferences'
);

$wgResourceModules['ext.bluespice.preferences'] = array(
	'scripts' => 'resources/bluespice.preferences.js',
	'dependencies' => array(
		'jquery.cookie'
	),
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );
