<?php

BsExtensionManager::registerExtension('CountThings',                     BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['CountThings'] = __DIR__ . '/languages/CountThings.i18n.php';

$wgAutoloadClasses['ViewCountCharacters'] = __DIR__ . '/views/view.CountCharacters.php';