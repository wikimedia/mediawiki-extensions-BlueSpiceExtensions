<?php

BsExtensionManager::registerExtension('WatchList', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['WatchList'] = __DIR__ . '/WatchList.class.php';

$wgMessagesDirs['WatchList'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['WatchList'] = __DIR__ . '/languages/WatchList.i18n.php';