<?php

BsExtensionManager::registerExtension('Authors',                         BsRUNLEVEL::FULL);

$wgExtensionMessagesFiles['Authors'] = dirname( __FILE__ ) . '/languages/Authors.i18n.php';

$wgResourceModules['ext.bluespice.authors'] = array(
	'styles' => 'extensions/BlueSpiceExtensions/Authors/resources/bluespice.authors.css',
	'position' => 'top',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);