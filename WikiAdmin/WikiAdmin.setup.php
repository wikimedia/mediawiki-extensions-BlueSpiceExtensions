<?php

BsExtensionManager::registerExtension('WikiAdmin', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$GLOBALS['wgAutoloadClasses']['WikiAdmin'] = __DIR__ . '/WikiAdmin.class.php';

$wgExtensionFunctions[] = 'WikiAdmin::loadModules';

$wgMessagesDirs['WikiAdmin'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['WikiAdmin'] = __DIR__ . '/languages/WikiAdmin.i18n.php';

// Specialpage and messages
$wgAutoloadClasses['SpecialWikiAdmin'] = __DIR__ . '/includes/specials/SpecialWikiAdmin.class.php';
$wgSpecialPageGroups['WikiAdmin'] = 'bluespice';
$wgExtensionMessagesFiles['WikiAdminAlias'] = __DIR__ . '/includes/specials/SpecialWikiAdmin.alias.php';
$wgSpecialPages['WikiAdmin'] = 'SpecialWikiAdmin';

$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'WikiAdmin::onSkinTemplateOutputPageBeforeExec';