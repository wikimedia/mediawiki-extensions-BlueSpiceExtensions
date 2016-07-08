<?php

BsExtensionManager::registerExtension( 'RSSFeeder', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$GLOBALS['wgAutoloadClasses']['RSSFeeder'] = __DIR__ . '/RSSFeeder.class.php';

$wgMessagesDirs['RSSFeeder'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['RSSFeederAlias'] = __DIR__ . '/languages/SpecialRSSFeeder.alias.php';

$wgAutoloadClasses['SpecialRSSFeeder'] = __DIR__ . '/includes/specials/SpecialRSSFeeder.class.php';
$wgAutoloadClasses['RSSCreator'] = __DIR__ . '/includes/RSSCreator.class.php';
$wgAutoloadClasses['RSSItemCreator'] = __DIR__ . '/includes/RSSCreator.class.php';

$wgSpecialPages['RSSFeeder'] = 'SpecialRSSFeeder';

$wgAjaxExportList[] = 'RSSFeeder::getRSS';

$wgResourceModules['ext.bluespice.rssFeeder'] = array(
	'styles' => 'bluespice.rssFeeder.css',
	'messages' => array( 'bs-extjs-rssfeeder-rss-title' ),
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/RSSFeeder/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/RSSFeeder/resources',
);