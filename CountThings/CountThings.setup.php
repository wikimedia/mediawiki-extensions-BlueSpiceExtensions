<?php

BsExtensionManager::registerExtension('CountThings', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['CountThings'] = __DIR__ . '/i18n';

$GLOBALS['wgAutoloadClasses']['CountThings'] = __DIR__ . '/CountThings.class.php';
$wgAutoloadClasses['ViewCountCharacters'] = __DIR__ . '/views/view.CountCharacters.php';