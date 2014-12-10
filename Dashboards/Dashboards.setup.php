<?php

BsExtensionManager::registerExtension('Dashboards', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['Dashboards'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['Dashboards'] = __DIR__ . '/languages/Dashboards.i18n.php';
$wgExtensionMessagesFiles['DashboardsAlias'] = __DIR__.'/languages/Dashboards.alias.php';

$GLOBALS['wgAutoloadClasses']['Dashboards'] = __DIR__ . '/Dashboards.class.php';
$wgAutoloadClasses['Dashboards'] = __DIR__ . '/Dashboards.class.php';
$wgAutoloadClasses['SpecialAdminDashboard'] = __DIR__.'/includes/specials/SpecialAdminDashboard.php';
$wgAutoloadClasses['SpecialUserDashboard'] = __DIR__.'/includes/specials/SpecialUserDashboard.php';
$wgAutoloadClasses['DashboardConfigRow'] = __DIR__.'/includes/DashboardConfigRow.php';
$wgAutoloadClasses['DashboardConfigTable'] = __DIR__.'/includes/DashboardConfigTable.php';

$wgSpecialPages['AdminDashboard'] = 'SpecialAdminDashboard';
$wgSpecialPages['UserDashboard']  = 'SpecialUserDashboard';

$wgSpecialPageGroups['AdminDashboard'] = 'bluespice';
$wgSpecialPageGroups['UserDashboard']  = 'bluespice';

$wgAjaxExportList[] = 'Dashboards::saveAdminDashboardConfig';
$wgAjaxExportList[] = 'Dashboards::saveUserDashboardConfig';
$wgAjaxExportList[] = 'Dashboards::saveTagDashboardConfig';
$wgAjaxExportList[] = 'Dashboards::getPortlets';
$wgAjaxExportList[] = 'Dashboards::getAdminDashboardConfig';
$wgAjaxExportList[] = 'Dashboards::getUserDashboardConfig';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'Dashboards::getSchemaUpdates';

$aResourceModuleTemplate = array(
	'localBasePath' => $IP.'/extensions/BlueSpiceExtensions/Dashboards/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/Dashboards/resources',
);

$wgResourceModules['ext.bluespice.dashboards'] = array(
	'scripts' => array(
		'bluespice.dashboards.js',
	),
	'styles' => array(
		'bluespice.dashboards.css'
	),
	'messages' => array(
		'tooltip-p-logo',
		'bs-dashboards-addportlet',
		'bs-dashboards-portlets',
		'bs-extjs-rssfeeder-rss-title'
	),
	'dependencies' => array(
		'ext.bluespice.extjs.BS.portal'
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
		'ext.bluespice.dashboards'
	),
	'messages' => array(
		//Default portlets user
		'bs-dashboard-userportlet-calendar-title',
		'bs-dashboard-userportlet-calendar-description',
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
		'ext.bluespice.dashboards'
	),
	'messages' => array(
	)
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );
