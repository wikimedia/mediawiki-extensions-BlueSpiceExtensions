<?php

BsExtensionManager::registerExtension('StateBar',                        BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['StateBar'] = __DIR__.'/languages/StateBar.i18n.php';

$wgResourceModules['ext.bluespice.statebar'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/StateBar/resources/bluespice.StateBar.js',
	'styles'  => 'extensions/BlueSpiceExtensions/StateBar/resources/bluespice.StateBar.css',
	'position' => 'top',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['ViewStateBar'] = __DIR__.'/views/view.StateBar.php';
$wgAutoloadClasses['ViewStateBarTopElement'] = __DIR__.'/views/view.StateBarTopElement.php';
$wgAutoloadClasses['ViewStateBarBodyElement'] = __DIR__.'/views/view.StateBarBodyElement.php';

$wgAjaxExportList[] = 'StateBar::ajaxCollectBodyViews';
