<?php

BsExtensionManager::registerExtension('PermissionManager', BsRUNLEVEL::FULL | BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgMessagesDirs['PermissionManager'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['PermissionManager'] = __DIR__ . '/languages/PermissionManager.i18n.php';

$wgResourceModules['ext.bluespice.permissionManager'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/PermissionManager/resources/bluespice.permissionManager.js'
	),
	'styles' => 'extensions/BlueSpiceExtensions/PermissionManager/resources/bluespice.permissionManager.css',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'htmlform-reset',
		'bs-permissionmanager-header-permissions',
		'bs-permissionmanager-header-global',
		'bs-permissionmanager-header-namespaces',
		'bs-permissionmanager-header-group',
		'bs-permissionmanager-btn-group-label',
		'bs-permissionmanager-btn-save-label',
		'bs-permissionmanager-btn-save-in-progress-label',
		'bs-permissionmanager-save-success',
		'bs-permissionmanager-btn-template-editor',
		'bs-permissionmanager-labeltpled',
		'bs-permissionmanager-labeltpled-desc',
		'bs-permissionmanager-labeltpled-active',
		'bs-permissionmanager-labeltpled-permissions',
		'bs-permissionmanager-labeltemplates',
		'bs-permissionmanager-labeltpled-add',
		'bs-permissionmanager-labeltpled-edit',
		'bs-permissionmanager-labeltpled-delete',
		'bs-permissionmanager-labeltpled-save',
		'bs-permissionmanager-labeltpled-cancel',
		'bs-permissionmanager-msgtpled-success',
		'bs-permissionmanager-msgtpled-saveonabort',
		'bs-permissionmanager-msgtpled-new',
		'bs-permissionmanager-msgtpled-edit',
		'bs-permissionmanager-msgtpled-delete',
		'bs-permissionmanager-titletpled-new',
		'bs-permissionmanager-titletpled-edit',
		'bs-permissionmanager-titletpled-delete',
		'bs-permissionmanager-unsaved-changes'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['PermissionManager'] = __DIR__ . '/PermissionManager.class.php';
$wgAutoloadClasses['PermissionTemplates'] = __DIR__ . '/includes/PermissionTemplates.class.php';

$wgAjaxExportList[] = 'PermissionManager::setTemplateData';
$wgAjaxExportList[] = 'PermissionManager::deleteTemplate';
$wgAjaxExportList[] = 'PermissionManager::savePermissions';

$wgExtensionFunctions[] = 'PermissionManager::setupLockmodePermissions';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'PermissionManager::getSchemaUpdates';

if( !isset( $bsgPermissionManagerDefaultTemplates ) ) {
	$bsgPermissionManagerDefaultTemplates = array();
}

if( !isset( $bsgPermissionManagerGroupSettingsFile ) ) {
	$bsgPermissionManagerGroupSettingsFile = BSROOTDIR . DS . 'config' . DS . 'pm-settings.php';
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