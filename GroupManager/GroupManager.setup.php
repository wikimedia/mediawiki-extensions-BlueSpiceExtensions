<?php

BsExtensionManager::registerExtension('GroupManager',                    BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['GroupManager'] = dirname( __FILE__ ) . '/GroupManager.i18n.php';