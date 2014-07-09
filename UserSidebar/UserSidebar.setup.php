<?php

BsExtensionManager::registerExtension('UserSidebar', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['UserSidebar'] = __DIR__ . '/UserSidebar.i18n.php';

$wgAutoloadClasses['ApiSidebar'] = __DIR__ . '/api/ApiSidebar.php';

$aResourceModuleTemplate = array(
	'localBasePath' => 'extensions/BlueSpiceExtensions/UserSidebar/resources/',
	'remoteExtPath' => 'BlueSpiceExtensions/UserSidebar/resources'
);

$wgResourceModules['ext.bluespice.usersidebar'] = array(
	'scripts'  => 'bluespice.userSidebar.js'
) + $aResourceModuleTemplate;