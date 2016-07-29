<?php

BsExtensionManager::registerExtension('SmartList', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['SmartList'] = __DIR__ . '/i18n';

$wgAutoloadClasses['SmartList'] = __DIR__ . '/SmartList.class.php';
$wgAutoloadClasses['BSApiTasksSmartList'] = __DIR__ . '/includes/api/BSApiTasksSmartList.php';

$GLOBALS['wgHooks']['LoadExtensionSchemaUpdates'][] = 'SmartList::getSchemaUpdates';

$wgAPIModules['bs-smartlist-tasks'] = 'BSApiTasksSmartList';
