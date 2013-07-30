<?php

BsExtensionManager::registerExtension('UserPreferences',                 BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE|BsACTION::LOAD_ON_API);

$wgExtensionMessagesFiles['UserPreferences'] = dirname( __FILE__ ) . '/UserPreferences.i18n.php';