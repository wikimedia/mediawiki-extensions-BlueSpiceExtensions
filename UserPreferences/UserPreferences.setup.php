<?php

BsExtensionManager::registerExtension('UserPreferences', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE|BsACTION::LOAD_ON_API);

$wgExtensionMessagesFiles['UserPreferences'] = __DIR__ . '/languages/UserPreferences.i18n.php';

$wgHooks['UserLoadOptions'][] = 'UserPreferences::onUserLoadOptions';

$wgResourceModules['ext.bluespice.userpreferences'] = array(
	'styles' => 'bluespice.userpreferences.css',
	'localBasePath' => __DIR__.'/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/UserPreferences/resources'
);
