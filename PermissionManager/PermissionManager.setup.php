<?php

BsExtensionManager::registerExtension('PermissionManager',               BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['PermissionManager'] = dirname( __FILE__ ) . '/PermissionManager.i18n.php';