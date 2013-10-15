<?php

BsExtensionManager::registerExtension('SecureFileStore', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['SecureFileStore'] = __DIR__ . '/SecureFileStore.i18n.php';

$wgAjaxExportList[] = 'SecureFileStore::getFile';
