<?php

BsExtensionManager::registerExtension('WidgetBar', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['WidgetBar'] = __DIR__ . '/WidgetBar.class.php';

$wgMessagesDirs['WidgetBar'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['WidgetBar'] = __DIR__ . '/languages/WidgetBar.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => 'extensions/BlueSpiceExtensions/WidgetBar/resources/',
	'remoteExtPath' => 'BlueSpiceExtensions/WidgetBar/resources'
);

$wgResourceModules['ext.bluespice.widgetbar.style'] = array(
	'styles'  => 'bluespice.widgetBar.css'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.widgetbar'] = array(
	'scripts' => 'bluespice.widgetBar.js',
	'position' => 'bottom'
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAutoloadClasses['ViewWidgetError'] = __DIR__ . '/views/view.WidgetError.php';
$wgAutoloadClasses['ViewWidgetErrorList'] = __DIR__ . '/views/view.WidgetErrorList.php';
$wgAutoloadClasses['ViewWidgetList'] = __DIR__ . '/views/view.WidgetList.php';
