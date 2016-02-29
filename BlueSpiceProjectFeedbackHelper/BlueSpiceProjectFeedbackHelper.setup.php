<?php

BsExtensionManager::registerExtension('BlueSpiceProjectFeedbackHelper',  BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgMessagesDirs['BlueSpiceProjectFeedbackHelper'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['BlueSpiceProjectFeedbackHelper'] = __DIR__ . '/languages/BlueSpiceProjectFeedbackHelper.i18n.php';

$wgResourceModules['ext.bluespice.blueSpiceprojectfeedbackhelper'] = array(
	'styles' => 'extensions/BlueSpiceExtensions/BlueSpiceProjectFeedbackHelper/resources/bluespice.blueSpiceProjectFeedbackHelper.css',
	'scripts' => 'extensions/BlueSpiceExtensions/BlueSpiceProjectFeedbackHelper/resources/bluespice.blueSpiceProjectFeedbackHelper.js',
	'dependencies' => array ( 'jquery.cookie' ),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$GLOBALS['wgAutoloadClasses']['BlueSpiceProjectFeedbackHelper'] = __DIR__ . '/BlueSpiceProjectFeedbackHelper.class.php';
$wgAutoloadClasses['BSApiTasksBlueSpiceProjectFeedbackHelper'] = __DIR__ . '/includes/api/BSApiTasksBlueSpiceProjectFeedbackHelper.php';
$wgAutoloadClasses['ViewBlueSpiceProjectFeedbackHelperPanel'] = __DIR__ . '/views/view.BlueSpiceProjectFeedbackHelperPanel.php';

$wgAPIModules['bs-bluespiceprojectfeedbackhelper'] = 'BSApiTasksBlueSpiceProjectFeedbackHelper';