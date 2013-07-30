<?php

BsExtensionManager::registerExtension('UniversalExport', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['UniversalExport']      = __DIR__ . '/UniversalExport.i18n.php';
$wgExtensionMessagesFiles['UniversalExportAlias'] = __DIR__ . '/specialpages/SpecialUniversalExport.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)

$wgAutoloadClasses['SpecialUniversalExport'] = __DIR__ . '/specialpages/SpecialUniversalExport.class.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)

$wgSpecialPageGroups['UniversalExport'] = 'bluespice';
$wgSpecialPages['UniversalExport'] = 'SpecialUniversalExport'; # Tell MediaWiki about the new special page and its class name