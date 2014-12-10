<?php

BsExtensionManager::registerExtension('SaferEdit', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['SaferEdit'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['SaferEdit'] = __DIR__ . '/languages/SaferEdit.i18n.php';

$GLOBALS['wgAutoloadClasses']['SaferEdit'] = __DIR__ . '/SaferEdit.class.php';

$wgResourceModules['ext.bluespice.saferedit.general'] = array(
	'scripts' => 'bluespice.SaferEdit.general.js',
	'position' => 'bottom',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/SaferEdit/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/SaferEdit/resources',
);

$wgResourceModules['ext.bluespice.saferedit.editmode'] = array(
	'scripts' => 'bluespice.SaferEdit.editmode.js',
	'messages' => array(
		'bs-saferedit-lastsavedversion',
		'bs-saferedit-restore',
		'bs-extjs-cancel',
		'bs-saferedit-unsavedchanges',
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

$wgHooks['LoadExtensionSchemaUpdates'][] = 'SaferEdit::getSchemaUpdates';

$wgAjaxExportList[] = 'SaferEdit::doCancelSaferEdit';
$wgAjaxExportList[] = 'SaferEdit::getLostTexts';
$wgAjaxExportList[] = 'SaferEdit::saveText';