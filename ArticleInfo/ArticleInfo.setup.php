<?php

BsExtensionManager::registerExtension('ArticleInfo',                     BsRUNLEVEL::FULL);

$wgExtensionMessagesFiles['ArticleInfo'] = dirname( __FILE__ ) . '/languages/ArticleInfo.i18n.php';

$wgResourceModules['ext.bluespice.articleinfo'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/ArticleInfo/resources/bluespice.articleInfo.js',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);