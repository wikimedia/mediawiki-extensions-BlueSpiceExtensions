<?php

BsExtensionManager::registerExtension('SecureFileStore', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['SecureFileStore'] = __DIR__ . '/languages/SecureFileStore.i18n.php';

$wgAjaxExportList[] = 'SecureFileStore::getFile';