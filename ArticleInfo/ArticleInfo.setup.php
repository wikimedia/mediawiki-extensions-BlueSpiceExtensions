<?php

BsExtensionManager::registerExtension('ArticleInfo',                     BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['ArticleInfo'] = __DIR__ . '/languages/ArticleInfo.i18n.php';

$wgResourceModules['ext.bluespice.articleinfo'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/ArticleInfo/resources/bluespice.articleInfo.js',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['ViewStateBarTopElementCategoryShortList'] = __DIR__ . '/views/view.StateBarTopElementCategoryShortList.php';
