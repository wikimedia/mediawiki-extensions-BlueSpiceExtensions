<?php

BsExtensionManager::registerExtension('ExtendedEditBar',                 BsRUNLEVEL::FULL);

$wgExtensionMessagesFiles['ExtendedEditBar'] = dirname( __FILE__ ) . '/languages/ExtendedEditBar.i18n.php';

$wgResourceModules['ext.bluespice.extendededitbar'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/ExtendedEditBar/resources/bluespice.extendedEditBar.js',
	'messages' => array(
		'bs-extendededitbar-redirect_tip',
		'bs-extendededitbar-redirect_sample',
		'bs-extendededitbar-strike_tip',
		'bs-extendededitbar-strike_sample',
		'bs-extendededitbar-enter_tip',
		'bs-extendededitbar-enter_sample',
		'bs-extendededitbar-upper_tip',
		'bs-extendededitbar-upper_sample',
		'bs-extendededitbar-lower_tip',
		'bs-extendededitbar-lower_sample',
		'bs-extendededitbar-small_tip',
		'bs-extendededitbar-small_sample',
		'bs-extendededitbar-comment_tip',
		'bs-extendededitbar-comment_sample',
		'bs-extendededitbar-gallery_tip',
		'bs-extendededitbar-gallery_sample',
		'bs-extendededitbar-quote_tip',
		'bs-extendededitbar-quote_sample',
		'bs-extendededitbar-table_tip',
		'bs-extendededitbar-table_sample'
	),
	'dependencies' => 'mediawiki.action.edit',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);