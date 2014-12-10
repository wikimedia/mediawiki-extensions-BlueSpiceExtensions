<?php

BsExtensionManager::registerExtension('CSyntaxHighlight', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['CSyntaxHighlight'] = __DIR__ . '/CSyntaxHighlight.class.php';

$wgMessagesDirs['CSyntaxHighlight'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['CSyntaxHighlight'] = __DIR__ . '/languages/CSyntaxHighlight.i18n.php';