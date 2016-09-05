<?php
wfLoadExtension( 'BlueSpiceExtensions/PermissionManager' );

if( !isset( $bsgPermissionManagerDefaultTemplates ) ) {
	$bsgPermissionManagerDefaultTemplates = array();
}

$bsgConfigFiles['PermissionManager'] = BSCONFIGDIR . DS . 'pm-settings.php';

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