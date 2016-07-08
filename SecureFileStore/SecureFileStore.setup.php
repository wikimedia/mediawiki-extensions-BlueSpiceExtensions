<?php

BsExtensionManager::registerExtension('SecureFileStore', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['SecureFileStore'] = __DIR__ . '/SecureFileStore.class.php';

$wgMessagesDirs['SecureFileStore'] = __DIR__ . '/i18n';

$wgAjaxExportList[] = 'SecureFileStore::getFile';