<?php

BsExtensionManager::registerExtension('PageAccess', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$GLOBALS['wgAutoloadClasses']['PageAccess'] = __DIR__ . '/PageAccess.class.php';

$wgMessagesDirs['PageAccess'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['PageAccess'] = __DIR__ . '/languages/PageAccess.i18n.php';

$wgAutoloadClasses['SpecialPageAccess'] = __DIR__ . '/includes/specials/SpecialPageAccess.class.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgSpecialPageGroups['PageAccess'] = 'bluespice';
$wgExtensionMessagesFiles['PageAccessAlias'] = __DIR__ . '/includes/specials/SpecialPageAccess.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)
$wgSpecialPages['PageAccess'] = 'SpecialPageAccess'; # Tell MediaWiki about the new special page and its class name
