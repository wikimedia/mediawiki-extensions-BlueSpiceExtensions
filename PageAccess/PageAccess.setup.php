<?php

BsExtensionManager::registerExtension('PageAccess',                      BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

// Messages
$wgExtensionMessagesFiles['PageAccess'] = __DIR__ . '/PageAccess.i18n.php';

// Specialpage
$wgAutoloadClasses['SpecialPageAccess'] = __DIR__ . '/includes/specials/SpecialPageAccess.class.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgSpecialPageGroups['PageAccess'] = 'bluespice';
$wgExtensionMessagesFiles['PageAccessAlias'] = __DIR__ . '/includes/specials/SpecialPageAccess.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)
$wgSpecialPages['PageAccess'] = 'SpecialPageAccess'; # Tell MediaWiki about the new special page and its class name
