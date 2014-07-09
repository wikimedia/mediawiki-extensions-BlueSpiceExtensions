<?php

BsExtensionManager::registerExtension('UserManager', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['UserManager'] = __DIR__ . '/languages/UserManager.i18n.php';

$wgResourceModules['ext.bluespice.userManager'] = array(
	'scripts' => 'bluespice.userManager.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-usermanager-headerUsername',
		'bs-usermanager-headerRealname',
		'bs-usermanager-headerEmail',
		'bs-usermanager-headerGroups',
		'bs-usermanager-headerActions',
		'bs-usermanager-tipEditPass',
		'bs-usermanager-tipEditDetails',
		'bs-usermanager-tipEditGroups',
		'bs-usermanager-tipDeleteUser',
		'bs-usermanager-btnOk',
		'bs-usermanager-btnCancel',
		'bs-usermanager-titleError',
		'bs-usermanager-unknownError',
		'bs-usermanager-titleAddUser',
		'bs-usermanager-titleEditDetails',
		'bs-usermanager-labelUsername',
		'bs-usermanager-labelRealname',
		'bs-usermanager-labelEmail',
		'bs-usermanager-labelChangetext',
		'bs-usermanager-titleEditPassword',
		'bs-usermanager-labelNewPassword',
		'bs-usermanager-labelPasswordCheck',
		'bs-usermanager-labelgroups',
		'bs-usermanager-titleEditGroups',
		'bs-usermanager-titleDeleteUser',
		'bs-usermanager-confirmDeleteUser',
		'bs-usermanager-showEntries',
		'bs-usermanager-textCannotEditOwn',
	),
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/UserManager/resources'
);

$wgAjaxExportList[] = 'UserManager::getUsers';
$wgAjaxExportList[] = 'UserManager::addUser';
$wgAjaxExportList[] = 'UserManager::editUser';
$wgAjaxExportList[] = 'UserManager::deleteUser';