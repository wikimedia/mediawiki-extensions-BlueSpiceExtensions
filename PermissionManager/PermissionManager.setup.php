<?php

BsExtensionManager::registerExtension('PermissionManager', BsRUNLEVEL::FULL | BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['PermissionManager'] = __DIR__ . '/PermissionManager.i18n.php';

$wgResourceModules['ext.bluespice.permissionManager'] = array(
	'scripts' => array(
		//'extensions/BlueSpiceExtensions/PermissionManager/resources/patch/deepEquals.js',
		//'extensions/BlueSpiceExtensions/PermissionManager/resources/patch/ColumnSetterGetter.js',
		//'extensions/BlueSpiceExtensions/PermissionManager/resources/patch/JsonPath.js',
		'extensions/BlueSpiceExtensions/PermissionManager/resources/bluespice.permissionManager.js'
	),
	'styles' => 'extensions/BlueSpiceExtensions/PermissionManager/resources/bluespice.permissionManager.css',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-permissionmanager-header-permissions',
		'bs-permissionmanager-header-global',
		'bs-permissionmanager-header-namespaces',
		'bs-permissionmanager-header-group',
		'bs-permissionmanager-btn-group-label',
		'bs-permissionmanager-btn-save-label',
		'bs-permissionmanager-btn-save-in-progress-label',
		'bs-permissionmanager-btn-save-success',
		'bs-permissionmanager-btn-template-editor',
		'bs-permissionmanager-labelTemplateEditor',
		'bs-permissionmanager-labelTemplateEditor-description',
		'bs-permissionmanager-labelTemplateEditor-active',
		'bs-permissionmanager-labelTemplateEditor-permissions',
		'bs-permissionmanager-labelTemplates',
		'bs-permissionmanager-template-editor-labelAdd',
		'bs-permissionmanager-template-editor-labelEdit',
		'bs-permissionmanager-template-editor-labelDelete',
		'bs-permissionmanager-template-editor-labelSave',
		'bs-permissionmanager-template-editor-labelCancel',
		'bs-permissionmanager-template-editor-save-success',
		'bs-permissionmanager-template-editor-saveOrAbort',
		'bs-permissionmanager-template-editor-msgNew',
		'bs-permissionmanager-template-editor-msgEdit',
		'bs-permissionmanager-template-editor-delete-success' => 'Template successfully deleted',
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['PermissionTemplates'] = __DIR__ . '/includes/PermissionTemplates.class.php';
$wgAutoloadClasses['CheckUser'] = __DIR__ . '/includes/CheckUser.class.php';


$wgAjaxExportList[] = 'PermissionManager::getAccessRules';
$wgAjaxExportList[] = 'PermissionManager::getGroupAccessData';
$wgAjaxExportList[] = 'PermissionManager::setAccessRules';
$wgAjaxExportList[] = 'PermissionManager::setTemplateData';
$wgAjaxExportList[] = 'PermissionManager::deleteTemplate';
#$wgAjaxExportList[] = 'PermissionManager::getIndexData';
#$wgAjaxExportList[] = 'PermissionManager::getPermissionArray';
#$wgAjaxExportList[] = 'PermissionManager::getTemplateData';
#$wgAjaxExportList[] = 'PermissionManager::setTemplateData';
#$wgAjaxExportList[] = 'PermissionManager::getData';
#$wgAjaxExportList[] = 'PermissionManager::setDataTemporary';
#$wgAjaxExportList[] = 'PermissionManager::setDataAbort';
#$wgAjaxExportList[] = 'PermissionManager::setData';

$wgExtensionFunctions[] = 'PermissionManager::setupLockmodePermissions';
