<?php

BsExtensionManager::registerExtension('SecureFileStore', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['SecureFileStore'] = __DIR__ . '/SecureFileStore.class.php';

$wgMessagesDirs['SecureFileStore'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['SecureFileStore'] = __DIR__ . '/languages/SecureFileStore.i18n.php';

$wgAjaxExportList[] = 'SecureFileStore::getFile';