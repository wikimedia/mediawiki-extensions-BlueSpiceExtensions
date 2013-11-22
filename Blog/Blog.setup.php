<?php

BsExtensionManager::registerExtension( 'Blog', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );
BsExtensionManager::registerNamespace( 'Blog', 2 );

$wgExtensionMessagesFiles['Blog']           = __DIR__ . '/languages/Blog.i18n.php';
$wgExtensionMessagesFiles['BlogNamespaces'] = __DIR__ . '/languages/Blog.namespaces.php';

$wgResourceModules['ext.bluespice.blog'] = array(
	'styles' => 'extensions/BlueSpiceExtensions/Blog/resources/bluespice.blog.css',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['ViewBlog'] = __DIR__ . '/views/view.Blog.php';
$wgAutoloadClasses['ViewBlogItem'] = __DIR__ . '/views/view.BlogItem.php';