<?php

BsExtensionManager::registerExtension( 'UniversalExport', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$GLOBALS['wgAutoloadClasses']['UniversalExport'] = __DIR__ . '/UniversalExport.class.php';

$wgMessagesDirs['UniversalExport'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['UniversalExport'] = __DIR__ . '/languages/UniversalExport.i18n.php';
$wgExtensionMessagesFiles['UniversalExportAlias'] = __DIR__ . '/languages/SpecialUniversalExport.alias.php';

$wgAutoloadClasses['SpecialUniversalExport'] = __DIR__ . '/includes/specials/SpecialUniversalExport.class.php';
$wgAutoloadClasses['ViewExportModuleOverview'] = __DIR__ . '/includes/views/ViewExportModuleOverview.php';
$wgAutoloadClasses['BsUniversalExportModule'] = __DIR__ . '/includes/UniversalExportModule.interface.php';
$wgAutoloadClasses['BsUniversalExportHelper'] = __DIR__ . '/includes/UniversalExportHelper.class.php';
$wgAutoloadClasses['BsUniversalExportTagLibrary'] = __DIR__ . '/includes/UniversalExportTagLibrary.class.php';

$wgSpecialPageGroups['UniversalExport'] = 'bluespice';
$wgSpecialPages['UniversalExport'] = 'SpecialUniversalExport';

$wgResourceModules['ext.bluespice.universalExport.css'] = array(
	'styles' => 'bluespice.universalExport.css',
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/UniversalExport/resources'
);