<?php

BsExtensionManager::registerExtension('MailChanges',                     BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['MailChanges'] = dirname( __FILE__ ) . '/MailChanges.i18n.php';