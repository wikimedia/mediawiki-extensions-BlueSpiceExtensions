<?php

BsExtensionManager::registerExtension('SecureFileStore',                 BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['SecureFileStore'] = dirname( __FILE__ ) . '/SecureFileStore.i18n.php';