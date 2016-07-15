<?php

BsExtensionManager::registerExtension('ArticleInfo', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['ArticleInfo'] = __DIR__ . '/ArticleInfo.class.php';

$wgMessagesDirs['ArticleInfo'] = __DIR__ . '/i18n';

$wgResourceModules['ext.bluespice.articleinfo'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/ArticleInfo/resources/bluespice.articleInfo.js',
	'position' => 'bottom',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['ViewStateBarTopElementCategoryShortList'] = __DIR__ . '/views/view.StateBarTopElementCategoryShortList.php';