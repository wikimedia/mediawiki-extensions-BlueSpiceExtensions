<?php

BsExtensionManager::registerExtension( 'PagesVisited', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$wgAutoloadClasses['PagesVisited'] = __DIR__ . '/PagesVisited.class.php';

$wgMessagesDirs['PagesVisited'] = __DIR__ . '/i18n';
