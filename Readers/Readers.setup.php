<?php

BsExtensionManager::registerExtension('Readers', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['Readers'] = __DIR__ . '/languages/Readers.i18n.php';
$wgExtensionMessagesFiles['ReadersAlias'] = __DIR__.'/languages/SpecialReaders.alias.php';

$GLOBALS['wgAutoloadClasses']['Readers'] = __DIR__ . '/Readers.class.php';
$wgAutoloadClasses['ViewReaders'] = __DIR__ . '/views/view.Readers.php';
$wgAutoloadClasses['SpecialReaders']  = __DIR__.'/includes/specials/SpecialReaders.class.php';

$wgSpecialPages['Readers'] = 'SpecialReaders';

$wgSpecialPageGroups['Readers'] = 'bluespice';

$wgAjaxExportList[] = 'Readers::getUsers';
$wgAjaxExportList[] = 'Readers::getData';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'Readers::getSchemaUpdates';

$aResourceModuleTemplate = array(
	'localBasePath' => $IP.'/extensions/BlueSpiceExtensions/Readers/resources',
	//'remoteBasePath' => &$GLOBALS['wgScriptPath'],
	'remoteExtPath' => 'BlueSpiceExtensions/Readers/resources',
);

$wgResourceModules['ext.bluespice.readers.styles'] = array(
	'styles' => array(
		'bluespice.readers.css'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.readers.specialreaders'] = array(
	'scripts' => array(
		'bluespice.readers.js',
	),
	'dependencies' => array(
		'ext.bluespice.extjs'
	),
	'position' => 'bottom',
	'messages' => array(
		'bs-readers-header-username',
		'bs-readers-header-readerspath',
		'bs-readers-header-ts'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.readers.specialreaderspath'] = array(
	'scripts' => array(
		'bluespice.readerspath.js',
	),
	'dependencies' => array(
		'ext.bluespice.extjs'
	),
	'position' => 'bottom',
	'messages' => array(
		'bs-readers-header-username',
		'bs-readers-header-readerspath',
		'bs-readers-header-ts'
	)
) + $aResourceModuleTemplate;