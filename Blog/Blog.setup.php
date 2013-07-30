<?php

BsExtensionManager::registerExtension('Blog',                            BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['Blog'] = dirname( __FILE__ ) . '/languages/Blog.i18n.php';

$wgResourceModules['ext.bluespice.blog'] = array(
	'styles' => 'extensions/BlueSpiceExtensions/Blog/resources/bluespice.blog.css',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);