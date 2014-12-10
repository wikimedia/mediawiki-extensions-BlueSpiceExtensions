<?php

BsExtensionManager::registerExtension( 'PagesVisited', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$GLOBALS['wgAutoloadClasses']['PagesVisited'] = __DIR__ . '/PagesVisited.class.php';

$wgMessagesDirs['PagesVisited'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['PagesVisited'] = __DIR__ . '/languages/PagesVisited.i18n.php';