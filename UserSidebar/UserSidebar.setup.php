<?php

BsExtensionManager::registerExtension('UserSidebar',                     BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['UserSidebar'] = __DIR__ . '/UserSidebar.i18n.php';

$wgAutoloadClasses['ApiSidebar'] = __DIR__ . '/api/ApiSidebar.php';
