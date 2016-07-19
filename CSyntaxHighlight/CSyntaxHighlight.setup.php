<?php

BsExtensionManager::registerExtension('CSyntaxHighlight', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgAutoloadClasses['CSyntaxHighlight'] = __DIR__ . '/CSyntaxHighlight.class.php';

$wgMessagesDirs['CSyntaxHighlight'] = __DIR__ . '/i18n';