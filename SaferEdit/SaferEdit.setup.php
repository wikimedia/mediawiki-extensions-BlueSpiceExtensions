<?php

BsExtensionManager::registerExtension('SaferEdit',                       BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['SaferEdit'] = dirname( __FILE__ ) . '/languages/SaferEdit.i18n.php';

$wgResourceModules['ext.bluespice.saferedit.general'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/SaferEdit/resources/bluespice.SaferEdit.general.js',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgResourceModules['ext.bluespice.saferedit.editmode'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/SaferEdit/resources/bluespice.SaferEdit.editmode.js',
	'messages' => array(
		'bs-saferedit-lastSavedVersion',
		'bs-saferedit-editFormOk',
		'bs-saferedit-editFormCancel',
		'bs-saferedit-restore',
		'bs-saferedit-cancel',
		'bs-saferedit-unsavedChanges',
		'bs-saferedit-othersectiontitle',
		'bs-saferedit-othersectiontext1',
		'bs-saferedit-othersectiontext2',
		'bs-saferedit-othersectiontext3'
	),
	'dependencies' => array(
		'ext.bluespice.saferedit.general'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);