<?php

BsExtensionManager::registerExtension('WantedArticle',                   BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['WantedArticle'] = dirname( __FILE__ ) . '/WantedArticle.i18n.php';

$wgResourceModules['ext.bluespice.wantedarticle'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/WantedArticle/js/WantedArticle.js',
	'styles'  => 'extensions/BlueSpiceExtensions/WantedArticle/css/WantedArticle.css',
	'messages' => array(
		'bs-wantedarticle-info_dialog_title',
		'bs-wantedarticle-info_nothing_entered',
		'bs-wantedarticle-info_title_contains_invalid_characters'
	),
	'position' => 'top',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);