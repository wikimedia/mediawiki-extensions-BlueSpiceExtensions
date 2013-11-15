<?php

BsExtensionManager::registerExtension('SaferEdit',                       BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['SaferEdit'] = __DIR__ . '/languages/SaferEdit.i18n.php';

$wgResourceModules['ext.bluespice.saferedit.general'] = array(
	'scripts' => 'bluespice.SaferEdit.general.js',
	'position' => 'bottom',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/SaferEdit/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/SaferEdit/resources',
);

$wgResourceModules['ext.bluespice.saferedit.editmode'] = array(
	'scripts' => 'bluespice.SaferEdit.editmode.js',
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
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/SaferEdit/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/SaferEdit/resources',
);

$wgAjaxExportList[] = 'SaferEdit::doCancelSaferEdit';
$wgAjaxExportList[] = 'SaferEdit::getLostTexts';
$wgAjaxExportList[] = 'SaferEdit::saveText';
