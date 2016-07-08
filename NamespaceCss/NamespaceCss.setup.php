<?php

BsExtensionManager::registerExtension( 'NamespaceCss', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$GLOBALS['wgAutoloadClasses']['NamespaceCss'] = __DIR__ . '/NamespaceCss.class.php';

$wgMessagesDirs['NamespaceCss'] = __DIR__ . '/i18n';
