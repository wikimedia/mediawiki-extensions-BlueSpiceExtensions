<?php

BsExtensionManager::registerExtension('StateBar', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['StateBar'] = __DIR__.'/languages/StateBar.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => 'extensions/BlueSpiceExtensions/StateBar/resources/',
	'remoteBasePath' => 'extensions/BlueSpiceExtensions/StateBar/resources'
);

$wgResourceModules['ext.bluespice.statebar.style'] = array(
	'styles'  => 'bluespice.StateBar.css'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.statebar'] = array(
	'scripts' => 'bluespice.StateBar.js',
	'position' => 'bottom'
) + $aResourceModuleTemplate;

$wgAutoloadClasses['ViewStateBar'] = __DIR__.'/views/view.StateBar.php';
$wgAutoloadClasses['ViewStateBarTopElement'] = __DIR__.'/views/view.StateBarTopElement.php';
$wgAutoloadClasses['ViewStateBarBodyElement'] = __DIR__.'/views/view.StateBarBodyElement.php';

$wgAjaxExportList[] = 'StateBar::ajaxCollectBodyViews';