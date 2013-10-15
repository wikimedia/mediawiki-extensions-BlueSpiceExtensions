<?php

BsExtensionManager::registerExtension('WikiAdmin', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionFunctions[] = 'WikiAdmin::loadModules';

$wgExtensionMessagesFiles['WikiAdmin'] = __DIR__ . '/WikiAdmin.i18n.php';

$dir = __DIR__ . '/';
// Specialpage and messages
$wgAutoloadClasses['SpecialWikiAdmin'] = $dir . 'SpecialWikiAdmin.class.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgSpecialPageGroups['SpecialWikiAdmin'] = 'bluespice';
$wgExtensionMessagesFiles['WikiAdminAlias'] = $dir . 'SpecialWikiAdmin.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)
$wgSpecialPages['SpecialWikiAdmin'] = 'SpecialWikiAdmin'; # Tell MediaWiki about the new special page and its class name