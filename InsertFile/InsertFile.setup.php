<?php

BsExtensionManager::registerExtension('InsertFile',                      BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_ON_API);

$wgExtensionMessagesFiles['InsertFile'] = dirname( __FILE__ ) . '/InsertFile.i18n.php';