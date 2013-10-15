<?php
BsExtensionManager::registerExtension('Notifications', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);
// Hooks
$wgHooks['BSBlueSpiceSkinUserBarBeforeLogout'][] = 'Notifications::onBSBlueSpiceSkinUserBarBeforeLogout';

// MessageFiles
$wgExtensionMessagesFiles['Notifications'] = __DIR__ . '/Notifications.i18n.php';

// Autoloader
$wgAutoloadClasses['BsNotificationsFormatter'] = __DIR__.'/includes/BsNotificationsFormatter.class.php';