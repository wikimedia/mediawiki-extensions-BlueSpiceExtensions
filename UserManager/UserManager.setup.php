<?php

BsExtensionManager::registerExtension('UserManager', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$GLOBALS['wgAutoloadClasses']['UserManager'] = __DIR__ . '/UserManager.class.php';
$wgAutoloadClasses['BSApiTasksUserManager'] = __DIR__ . '/includes/api/BSApiTasksUserManager.php';
$wgAutoloadClasses['BSApiChangeableGroupStore'] = __DIR__ . '/includes/api/BSApiChangeableGroupStore.php';
$wgAutoloadClasses['SpecialUserManager'] = __DIR__ . '/includes/specials/SpecialUserManager.class.php';
#$wgAutoloadClasses['Block'] = __DIR__ . '/../../../includes/Block.php';

$wgMessagesDirs['UserManager'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['UserManager'] = __DIR__ . '/languages/UserManager.i18n.php';

$wgResourceModules['ext.bluespice.userManager'] = array(
	'scripts' => 'bluespice.userManager.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		"bs-usermanager-headerusername",
		"bs-usermanager-headerrealname",
		"bs-usermanager-headeremail",
		"bs-usermanager-headergroups",
		"bs-usermanager-titleadduser",
		"bs-usermanager-titleeditdetails",
		"bs-usermanager-labelnewpassword",
		"bs-usermanager-labelpasswordcheck",
		"bs-usermanager-headergroups",
		"bs-usermanager-titledeleteuser",
		"bs-usermanager-confirmdeleteuser",
		"bs-usermanager-groups-more",
		"bs-usermanager-no-self-desysop",
		"bs-usermanager-headerenabled",
		"bs-usermanager-endisable",
		"bs-usermanager-confirmdisableuser",
		"bs-usermanager-confirmenableuser",
		"bs-usermanager-titledisableuser",
		"bs-usermanager-titleenableuser",
		"bs-usermanager-editgroups",
		"bs-usermanager-editpassword",
		"bs-usermanager-editpassword-successful",
		"bs-usermanager-title-nouserselected",
		"bs-usermanager-nouserselected",
		"bs-usermanager-title-multipleuserselected",
		"bs-usermanager-multipleuserselected",
		"bs-usermanager-invalid-groups"
	),
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/UserManager/resources'
);

$wgAPIModules['bs-usermanager-tasks'] = 'BSApiTasksUserManager';
$wgAPIModules['bs-usermanager-group-store'] = 'BSApiChangeableGroupStore';