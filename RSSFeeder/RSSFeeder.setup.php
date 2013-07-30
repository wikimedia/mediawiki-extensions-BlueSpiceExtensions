<?php

BsExtensionManager::registerExtension('RSSFeeder',                       BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$dir = dirname( __FILE__ );
$wgExtensionMessagesFiles['RSSFeeder']      = $dir . '/RSSFeeder.i18n.php';
$wgExtensionMessagesFiles['RSSFeederAlias'] = $dir . '/SpecialRSSFeeder.alias.php';

$wgAutoloadClasses['SpecialRSSFeeder'] = $dir . '/SpecialRSSFeeder.class.php';

$wgSpecialPageGroups['RSSFeeder'] = 'bluespice';

$wgSpecialPages['RSSFeeder'] = 'SpecialRSSFeeder';