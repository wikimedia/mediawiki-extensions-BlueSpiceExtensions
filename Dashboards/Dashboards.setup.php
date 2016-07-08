<?php

BsExtensionManager::registerExtension('Dashboards', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['Dashboards'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['DashboardsAlias'] = __DIR__.'/languages/Dashboards.alias.php';

$GLOBALS['wgAutoloadClasses']['Dashboards'] = __DIR__ . '/Dashboards.class.php';
$wgAutoloadClasses['Dashboards'] = __DIR__ . '/Dashboards.class.php';
$wgAutoloadClasses['SpecialAdminDashboard'] = __DIR__.'/includes/specials/SpecialAdminDashboard.php';
$wgAutoloadClasses['SpecialUserDashboard'] = __DIR__.'/includes/specials/SpecialUserDashboard.php';
$GLOBALS['wgAutoloadClasses']['BSApiDashboardTasks'] = __DIR__ . '/includes/api/BSApiDashboardTasks.php';
$GLOBALS['wgAutoloadClasses']['BSApiDashboardStore'] = __DIR__ . '/includes/api/BSApiDashboardStore.php';
$GLOBALS['wgAutoloadClasses']['BSApiDashboardWidgetsTasks'] = __DIR__ . '/includes/api/BSApiDashboardWidgetsTasks.php';

$wgSpecialPages['AdminDashboard'] = 'SpecialAdminDashboard';
$wgSpecialPages['UserDashboard']  = 'SpecialUserDashboard';

$wgAPIModules['bs-dashboards-tasks'] = 'BSApiDashboardTasks';
$wgAPIModules['bs-dashboards-store'] = 'BSApiDashboardStore';
$wgAPIModules['bs-dashboards-widgets-tasks'] = 'BSApiDashboardWidgetsTasks';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'Dashboards::getSchemaUpdates';

$aResourceModuleTemplate = array(
	'localBasePath' => $IP.'/extensions/BlueSpiceExtensions/Dashboards/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/Dashboards/resources',
);

$wgResourceModules['ext.bluespice.dashboards'] = array(
	'scripts' => array(
		'bluespice.dashboards.main.js',
	),
	'messages' => array(
		'tooltip-p-logo'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.dashboards.portletCatalog'] = array(
	'scripts' => array(
		'bluespice.dashboards.portletcatalog.js'
	),
	'styles' => array(
		'bluespice.dashboards.css'
	),
	'dependencies' => array(
		'ext.bluespice.extjs.BS.portal'
	),
	'messages' => array(
		'bs-dashboards-addportlet',
		'bs-dashboards-portlets',
		'bs-extjs-rssfeeder-rss-title',
		'bs-dashboard-userportlet-wikipage-wiki-article'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.dashboards.userDashboard'] = array(
	'scripts' => array(
		'bluespice.dashboards.userDashboard.js',
	),
	'styles' => array(
		//'extensions/BlueSpiceExtensions/Dashboards/resources/bluespice.dashboards.css',
	),
	'dependencies' => array(
		'ext.bluespice.dashboards.portletCatalog'
	),
	'messages' => array(
		//Default portlets user
		'bs-dashboard-userportlet-calendar-title',
		'bs-dashboard-userportlet-calendar-description',
		'bs-dashboard-userportlet-article-title',
		'bs-dashboard-userportlet-article-description',
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.dashboards.adminDashboard'] = array(
	'scripts' => array(
		'bluespice.dashboards.adminDashboard.js',
	),
	'styles' => array(
		//'extensions/BlueSpiceExtensions/Dashboards/resources/bluespice.dashboards.css',
	),
	'dependencies' => array(
		'ext.bluespice.dashboards.portletCatalog'
	),
	'messages' => array(
	)
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );
