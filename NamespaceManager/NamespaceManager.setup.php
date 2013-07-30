<?php

BsExtensionManager::registerExtension('NamespaceManager',                BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['NamespaceManager'] = dirname( __FILE__ ) . '/NamespaceManager.i18n.php';