<?php

BsExtensionManager::registerExtension('Authors',                         BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['Authors'] = __DIR__ . '/languages/Authors.i18n.php';

$wgResourceModules['ext.bluespice.authors'] = array(
	'styles' => 'extensions/BlueSpiceExtensions/Authors/resources/bluespice.authors.css',
	'position' => 'top',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['ViewAuthors'] = __DIR__ . '/views/view.Authors.php';
$wgAutoloadClasses['ViewAuthorsUserPageProfileImageSetting'] = __DIR__ . '/views/view.AuthorsUserPageProfileImageSetting.php';
