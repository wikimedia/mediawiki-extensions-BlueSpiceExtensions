<?php

BsExtensionManager::registerExtension( 'PagesVisited', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$wgResourceModules['ext.bluespice.pagesvisited.special'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/PagesVisited/resources/bluespice.pagesVisited.js',
	),
	'dependencies' => array(
		'ext.bluespice.extjs'
	),
	'messages' => array(
		'bs-readers-headerUsername',
		'bs-readers-headerReadersPath',
		'bs-readers-headerTs'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgExtensionMessagesFiles['PagesVisited'] = __DIR__ . '/languages/PagesVisited.i18n.php';
$wgExtensionMessagesFiles['PagesVisitedAlias'] = __DIR__.'/languages/SpecialPagesVisited.alias.php';

$wgAutoloadClasses['SpecialPagesVisited']  = __DIR__.'/includes/specials/SpecialPagesVisited.class.php';

$wgSpecialPages['PagesVisited'] = 'SpecialPagesVisited';

$wgSpecialPageGroups['PagesVisited'] = 'bluespice';

$wgAjaxExportList[] = 'PagesVisited::getData';
