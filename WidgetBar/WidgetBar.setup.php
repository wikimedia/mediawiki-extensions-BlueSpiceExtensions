<?php

BsExtensionManager::registerExtension('WidgetBar', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['WidgetBar'] = __DIR__ . '/WidgetBar.class.php';

$wgMessagesDirs['WidgetBar'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['WidgetBar'] = __DIR__ . '/languages/WidgetBar.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'BlueSpiceExtensions/WidgetBar'
);

$wgResourceModules['ext.bluespice.widgetbar.style'] = array(
	'styles'  => 'resources/bluespice.widgetBar.css',
	'position' => 'top'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.widgetbar'] = array(
	'scripts' => 'resources/bluespice.widgetBar.js',
	'dependencies' => array ( 'jquery.cookie' ),
	'position' => 'bottom'
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAutoloadClasses['ViewWidgetError'] = __DIR__ . '/views/view.WidgetError.php';
$wgAutoloadClasses['ViewWidgetErrorList'] = __DIR__ . '/views/view.WidgetErrorList.php';
$wgAutoloadClasses['ViewWidgetList'] = __DIR__ . '/views/view.WidgetList.php';
