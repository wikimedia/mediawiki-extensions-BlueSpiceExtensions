<?php

BsExtensionManager::registerExtension('CountThings', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['CountThings'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['CountThings'] = __DIR__ . '/languages/CountThings.i18n.php';

$GLOBALS['wgAutoloadClasses']['CountThings'] = __DIR__ . '/CountThings.class.php';
$wgAutoloadClasses['ViewCountCharacters'] = __DIR__ . '/views/view.CountCharacters.php';