<?php

BsExtensionManager::registerExtension( 'PermissionManager',
									   BsRUNLEVEL::FULL | BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE );

$wgExtensionMessagesFiles[ 'PermissionManager' ] = __DIR__ . '/PermissionManager.i18n.php';

$wgResourceModules[ 'ext.bluespice.permissionManager' ] = array (
	'scripts'		 => array (
		//'extensions/BlueSpiceExtensions/PermissionManager/resources/patch/deepEquals.js',
		//'extensions/BlueSpiceExtensions/PermissionManager/resources/patch/ColumnSetterGetter.js',
		//'extensions/BlueSpiceExtensions/PermissionManager/resources/patch/JsonPath.js',
		'extensions/BlueSpiceExtensions/PermissionManager/resources/bluespice.permissionManager.js'
	),
	'styles'		 => 'extensions/BlueSpiceExtensions/PermissionManager/resources/bluespice.permissionManager.css',
	'dependencies'	 => 'ext.bluespice.extjs',
	'messages'		 => array (
		'bs-permissionmanager-header-permissions',
		'bs-permissionmanager-header-global',
		'bs-permissionmanager-header-namespaces',
		'bs-permissionmanager-btn-group-label',
		'bs-permissionmanager-btn-save-label',
		'bs-permissionmanager-btn-save-in-progress-label',
		'bs-permissionmanager-btn-save-success'
	),
	'localBasePath'	 => $IP,
	'remoteBasePath' => &$GLOBALS[ 'wgScriptPath' ]
);

$wgAutoloadClasses[ 'PermissionTemplates' ] = __DIR__ . '/includes/PermissionTemplates.class.php';
$wgAutoloadClasses[ 'CheckUser' ] = __DIR__ . '/includes/CheckUser.class.php';


$wgAjaxExportList[ ] = 'PermissionManager::getAccessRules';
$wgAjaxExportList[ ] = 'PermissionManager::setAccessRules';
#$wgAjaxExportList[] = 'PermissionManager::getIndexData';
#$wgAjaxExportList[] = 'PermissionManager::getPermissionArray';
#$wgAjaxExportList[] = 'PermissionManager::getTemplateData';
#$wgAjaxExportList[] = 'PermissionManager::setTemplateData';
#$wgAjaxExportList[] = 'PermissionManager::getData';
#$wgAjaxExportList[] = 'PermissionManager::setDataTemporary';
#$wgAjaxExportList[] = 'PermissionManager::setDataAbort';
#$wgAjaxExportList[] = 'PermissionManager::setData';

$wgExtensionFunctions[] = 'PermissionManager::setupLockmodePermissions';
