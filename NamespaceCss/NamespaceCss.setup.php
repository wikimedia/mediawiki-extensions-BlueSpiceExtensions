<?php

BsExtensionManager::registerExtension( 'NamespaceCss', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$wgMessagesDirs['NamespaceCss'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['NamespaceCss'] = __DIR__ . '/languages/NamespaceCss.i18n.php';