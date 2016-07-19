<?php

BsExtensionManager::registerExtension('WatchList', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgAutoloadClasses['WatchList'] = __DIR__ . '/WatchList.class.php';

$wgMessagesDirs['BSWatchList'] = __DIR__ . '/i18n'; //TODO: Must not be WatchList, somehting's overriding here otherwise
