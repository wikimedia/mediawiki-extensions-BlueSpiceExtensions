<?php

BsExtensionManager::registerExtension('SmartList', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['SmartList'] = __DIR__ . '/i18n';

$wgAutoloadClasses['SmartList'] = __DIR__ . '/SmartList.class.php';

$GLOBALS['wgHooks']['LoadExtensionSchemaUpdates'][] = 'SmartList::getSchemaUpdates';

$wgAjaxExportList[] = 'SmartList::getMostVisitedPages';
$wgAjaxExportList[] = 'SmartList::getMostEditedPages';
$wgAjaxExportList[] = 'SmartList::getMostActivePortlet';
$wgAjaxExportList[] = 'SmartList::getYourEditsPortlet';