<?php

BsExtensionManager::registerExtension('BlueSpiceProjectFeedbackHelper',  BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['BlueSpiceProjectFeedbackHelper'] = dirname( __FILE__ ) . '/languages/BlueSpiceProjectFeedbackHelper.i18n.php';

$wgResourceModules['ext.bluespice.blueSpiceprojectfeedbackhelper'] = array(
	'styles' => 'extensions/BlueSpiceExtensions/BlueSpiceProjectFeedbackHelper/resources/bluespice.blueSpiceProjectFeedbackHelper.css',
	'scripts' => 'extensions/BlueSpiceExtensions/BlueSpiceProjectFeedbackHelper/resources/bluespice.blueSpiceProjectFeedbackHelper.js',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);