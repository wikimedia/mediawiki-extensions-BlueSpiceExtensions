<?php

BsExtensionManager::registerExtension('Preferences',                     BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['Preferences'] = dirname( __FILE__ ) . '/Preferences.i18n.php';