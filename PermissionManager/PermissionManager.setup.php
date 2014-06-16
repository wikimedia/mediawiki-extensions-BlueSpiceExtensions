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

$GLOBALS['wgAutoloadClasses']['PermissionManager'] = __DIR__ . '/PermissionManager.class.php';
$wgAutoloadClasses['PermissionTemplates'] = __DIR__ . '/includes/PermissionTemplates.class.php';
$wgAutoloadClasses['PMCheckUser'] = __DIR__ . '/includes/PMCheckUser.class.php';

$wgAjaxExportList[] = 'PermissionManager::getAccessRules';
$wgAjaxExportList[] = 'PermissionManager::getGroupAccessData';
$wgAjaxExportList[] = 'PermissionManager::setAccessRules';
$wgAjaxExportList[] = 'PermissionManager::setTemplateData';
$wgAjaxExportList[] = 'PermissionManager::deleteTemplate';

$wgExtensionFunctions[] = 'PermissionManager::setupLockmodePermissions';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'PermissionManager::getSchemaUpdates';

if( !isset( $bsgPermissionManagerDefaultTemplates ) ) {
	$bsgPermissionManagerDefaultTemplates = array();
}

$bsgPermissionManagerDefaultTemplates = array(
	//Not namespace specific
	'bs-permissionmanager-default-template-read-general-title' => array(
		//BlueSpice
		//TODO: Move to other extensions
		'files',
		'viewfiles',
		'searchfiles'

	),

	'bs-permissionmanager-default-template-read-title' => array(
		//MediaWiki standard
		'read',

		//BlueSpice
		//TODO: Move to other extensions
		'readshoutbox',
		'universalexport-export',
		'universalexport-export-with-attachments'

	),

	//Not namespace specific
	'bs-permissionmanager-default-template-edit-general-title' => array(
		//MediaWiki standard
		'movefile',
		'move-rootuserpages',
		'upload',
			'reupload',
			'reupload-own',
			'reupload-shared',
			'upload_by_url',
		'writeapi',

		//BlueSpice
		//TODO: Move to other extensions
		'writeshoutbox'
	),

	'bs-permissionmanager-default-template-edit-title' => array(
		//MediaWiki standard
		'edit',
		'create',
		'createtalk',
		'move',
			'move-subbpages',
		'delete',

		//BlueSpice
		//TODO: Move to other extensions
		'writeshoutbox'
	),

	'bs-permissionmanager-default-template-admin-title' => array(
		//MediaWiki standard
		'bigdelete',
		'browsearchive',
		'createaccount',
		'deletedtext',
		'deletedhistory',
		'protect',
		'editprotected',
		'block',
		'rollback',
		'import',
		'userrights',

		//BlueSpice
		//TODO: Move to other extensions
		'wikiadmin',
			'editadmin', // still in use?
			'useradmin' // still in use?
	),

	'bs-permissionmanager-default-template-quality-title' => array(
		//MediaWiki FlaggedRevs
		//TODO: Move to other extensions
		'autoreview',
		'review',
		'unreviewdpages',
		'validate',

		//BlueSpice
		//TODO: Move to other extensions
		'responsibleeditors-changeresponsibility',
		'responsibleeditors-takeresponsibility',
		'responsibleeditors-viewspecialpage',
		'workflowview',
			'workflowedit', // still in use?
	)
) + $bsgPermissionManagerDefaultTemplates;