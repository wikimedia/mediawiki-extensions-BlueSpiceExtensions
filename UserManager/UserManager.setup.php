<?php

BsExtensionManager::registerExtension('UserManager',                     BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['UserManager'] = dirname( __FILE__ ) . '/languages/UserManager.i18n.php';

$wgResourceModules['ext.bluespice.userManager'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/UserManager/resources/bluespice.userManager.js',
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
		'bs-usermanager-titleEditGroups',
		'bs-usermanager-titleDeleteUser',
		'bs-usermanager-confirmDeleteUser',
		'bs-usermanager-showEntries',
		'bs-usermanager-textCannotEditOwn',
		'bs-usermanager-pageSize',
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);