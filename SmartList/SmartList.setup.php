<?php

BsExtensionManager::registerExtension('SmartList', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['SmartList'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['SmartList'] = __DIR__ . '/languages/SmartList.i18n.php';

$GLOBALS['wgAutoloadClasses']['SmartList'] = __DIR__ . '/SmartList.class.php';

$GLOBALS['wgHooks']['LoadExtensionSchemaUpdates'][] = 'SmartList::getSchemaUpdates';

$wgAjaxExportList[] = 'SmartList::getMostVisitedPages';
$wgAjaxExportList[] = 'SmartList::getMostEditedPages';
$wgAjaxExportList[] = 'SmartList::getMostActivePortlet';
$wgAjaxExportList[] = 'SmartList::getYourEditsPortlet';