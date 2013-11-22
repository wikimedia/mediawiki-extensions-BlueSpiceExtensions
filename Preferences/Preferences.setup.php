<?php

BsExtensionManager::registerExtension('Preferences',                     BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['Preferences'] = __DIR__ . '/Preferences.i18n.php';