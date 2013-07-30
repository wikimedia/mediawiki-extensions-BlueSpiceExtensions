<?php

BsExtensionManager::registerExtension('ShoutBox',                        BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['ShoutBox'] = dirname(__FILE__) . '/ShoutBox.i18n.php';
$wgExtensionMessagesFiles['ShoutBoxMagic'] = dirname(__FILE__) . '/ShoutBox.i18n.magic.php';

$wgResourceModules['ext.bluespice.shoutbox'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/ShoutBox/js/ShoutBox.js',
	'styles'  => 'extensions/BlueSpiceExtensions/ShoutBox/css/ShoutBox.css',
	'messages' => array(
		'bs-shoutbox-confirm_text',
		'bs-shoutbox-confirm_title',
		'bs-shoutbox-enterMessage'
	),
	'position' => 'top',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);