<?php

BsExtensionManager::registerExtension('UserManager', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$GLOBALS['wgAutoloadClasses']['UserManager'] = __DIR__ . '/UserManager.class.php';

$wgMessagesDirs['UserManager'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['UserManager'] = __DIR__ . '/languages/UserManager.i18n.php';

$wgResourceModules['ext.bluespice.userManager'] = array(
	'scripts' => 'bluespice.userManager.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-usermanager-headerusername',
		'bs-usermanager-headerrealname',
		'bs-usermanager-headeremail',
		'bs-usermanager-headergroups',
		'bs-usermanager-titleadduser',
		'bs-usermanager-titleeditdetails',
		'bs-usermanager-labelnewpassword',
		'bs-usermanager-labelpasswordcheck',
		'bs-usermanager-headergroups',
		'bs-usermanager-titledeleteuser',
		'bs-usermanager-confirmdeleteuser',
		'bs-usermanager-groups-more',
		'bs-usermanager-no-self-desysop'
	),
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/UserManager/resources'
);

$wgAjaxExportList[] = 'UserManager::getUsers';
$wgAjaxExportList[] = 'UserManager::addUser';
$wgAjaxExportList[] = 'UserManager::editUser';
$wgAjaxExportList[] = 'UserManager::deleteUser';
$wgAjaxExportList[] = 'UserManager::setUserGroups';