<?php

BsExtensionManager::registerExtension('UserPreferences', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE|BsACTION::LOAD_ON_API);

$wgExtensionMessagesFiles['UserPreferences'] = __DIR__ . '/UserPreferences.i18n.php';

$wgResourceModules['ext.bluespice.userpreferences'] = array(
	'styles' => 'bluespice.userpreferences.css',
	'localBasePath' => __DIR__.'/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/UserPreferences/resources'
);
