<?php

BsExtensionManager::registerExtension( 'Emoticons', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$GLOBALS['wgAutoloadClasses']['Emoticons'] = __DIR__ . '/Emoticons.class.php';

$wgMessagesDirs['Emoticons'] = __DIR__ . '/i18n';
