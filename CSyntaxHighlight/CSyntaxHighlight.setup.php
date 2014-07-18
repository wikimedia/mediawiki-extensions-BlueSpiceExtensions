<?php

BsExtensionManager::registerExtension('CSyntaxHighlight', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['CSyntaxHighlight'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['CSyntaxHighlight'] = __DIR__ . '/languages/CSyntaxHighlight.i18n.php';