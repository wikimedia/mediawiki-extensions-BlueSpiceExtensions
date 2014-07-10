<?php

BsExtensionManager::registerExtension( 'Emoticons', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$wgMessagesDirs['Emoticons'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['Emoticons'] = __DIR__ . '/languages/Emoticons.i18n.php';
