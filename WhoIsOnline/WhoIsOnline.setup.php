<?php

BsExtensionManager::registerExtension('WhoIsOnline',                     BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['WhoIsOnline'] = dirname( __FILE__ ) . '/WhoIsOnline.i18n.php';

$wgResourceModules['ext.bluespice.whoisonline'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/WhoIsOnline/js/WhoIsOnline.js',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);