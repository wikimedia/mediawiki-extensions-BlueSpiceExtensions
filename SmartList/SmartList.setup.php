<?php

BsExtensionManager::registerExtension('SmartList', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['SmartList'] = __DIR__ . '/SmartList.i18n.php';

$wgAjaxExportList[] = 'SmartList::getMostVisitedPages';
$wgAjaxExportList[] = 'SmartList::getMostEditedPages';
$wgAjaxExportList[] = 'SmartList::getMostActivePortlet';
$wgAjaxExportList[] = 'SmartList::getYourEditsPortlet';