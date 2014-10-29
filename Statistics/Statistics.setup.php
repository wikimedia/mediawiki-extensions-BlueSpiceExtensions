<?php
BsExtensionManager::registerExtension('Statistics', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['Statistcs'] = __DIR__ . '/Statistcs.class.php';

$wgMessagesDirs['Statistcs'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['Statistcs'] = __DIR__ . '/languages/Statistics.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/Statistics/resources'
);

$wgResourceModules['ext.bluespice.statistics'] = array(
	'scripts' => array(
		'/bluespice.statistics.js',
	),
	'dependencies' => array(
		'ext.bluespice.extjs'
	),
	'messages' => array(
		'bs-statistics-filters',
		'bs-statistics-finish',
		'bs-statistics-diagram',
		'bs-statistics-from',
		'bs-statistics-to',
		'bs-statistics-filter-user',
		'bs-statistics-filter-searchscope',
		'bs-ns',
		'bs-statistics-filter-category',
		'bs-statistics-mode',
		'bs-statistics-absolute',
		'bs-statistics-aggregated',
		'bs-statistics-list',
		'bs-statistics-grain',
		'bs-statistics-year',
		'bs-statistics-month',
		'bs-statistics-week',
		'bs-statistics-day',
		'bs-statistics-label-count',
		'bs-statistics-label-time',
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.statisticsPortlets'] = array(
	'messages' => array(
		'bs-statistics-label-count',
		'bs-statistics-portlet-numberofusers',
		'bs-statistics-portlet-numberofedits',
		'bs-statistics-portlet-numberofpages',
		'bs-extjs-portal-timespan',
		'bs-statistics-portletconfig-periodday',
		'bs-statistics-week',
		'bs-statistics-month',
		'bs-statistics-label-time',
	)
) + $aResourceModuleTemplate;

$wgAutoloadClasses['BsStatisticsFilter'] = __DIR__ . '/includes/StatisticsFilter.class.php';
$wgAutoloadClasses['BsSelectFilter'] = __DIR__ . '/includes/SelectFilter.class.php';
$wgAutoloadClasses['BsMultiSelectFilter'] = __DIR__ . '/includes/MultiSelectFilter.class.php';
$wgAutoloadClasses['BsDiagram'] = __DIR__ . '/includes/Diagram.class.php';

$wgAutoloadClasses['BsFilterUsers'] = __DIR__ . '/includes/FilterUsers.class.php';
$wgAutoloadClasses['BsFilterNamespace'] = __DIR__ . '/includes/FilterNamespace.class.php';
$wgAutoloadClasses['BsFilterCategory'] = __DIR__ . '/includes/FilterCategory.class.php';
$wgAutoloadClasses['BsFilterSearchScope'] = __DIR__ . '/includes/FilterSearchScope.class.php';

$wgAutoloadClasses['BsDiagramNumberOfUsers'] = __DIR__ . '/includes/DiagramNumberOfUsers.class.php';
$wgAutoloadClasses['BsDiagramNumberOfPages'] = __DIR__ . '/includes/DiagramNumberOfPages.class.php';
$wgAutoloadClasses['BsDiagramNumberOfArticles'] = __DIR__ . '/includes/DiagramNumberOfArticles.class.php';
$wgAutoloadClasses['BsDiagramNumberOfEdits'] = __DIR__ . '/includes/DiagramNumberOfEdits.class.php';
$wgAutoloadClasses['BsDiagramEditsPerUser'] = __DIR__ . '/includes/DiagramEditsPerUser.class.php';
$wgAutoloadClasses['BsDiagramSearches'] = __DIR__ . '/includes/DiagramSearches.class.php';

$wgAutoloadClasses['MySQLDbReader'] = __DIR__ . '/includes/MySQLDbReader.class.php';
$wgAutoloadClasses['PostGreSQLDbReader'] = __DIR__ . '/includes/PostGreSQLDbReader.class.php';
$wgAutoloadClasses['OracleDbReader'] = __DIR__ . '/includes/OracleDbReader.class.php';
$wgAutoloadClasses['StatsDataProvider'] = __DIR__ . '/includes/StatsDataProvider.class.php';
$wgAutoloadClasses['Interval'] = __DIR__ . '/includes/Interval.class.php';
$wgAutoloadClasses['BsCharting'] = __DIR__ . '/includes/Charting.class.php';

$wgAutoloadClasses['SpecialExtendedStatistics'] = __DIR__ . '/includes/specials/SpecialExtendedStatistics.class.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgSpecialPageGroups['ExtendedStatistics'] = 'bluespice';
$wgExtensionMessagesFiles['ExtendedStatisticsAlias'] = __DIR__ . '/includes/specials/SpecialExtendedStatistics.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)
$wgSpecialPages['ExtendedStatistics'] = 'SpecialExtendedStatistics'; # Tell MediaWiki about the new special page and its class name

$wgAjaxExportList[] = 'Statistics::ajaxGetAvalableDiagrams';
$wgAjaxExportList[] = 'Statistics::ajaxGetUserFilter';
$wgAjaxExportList[] = 'Statistics::ajaxGetNamespaceFilter';
$wgAjaxExportList[] = 'Statistics::ajaxGetCategoryFilter';
$wgAjaxExportList[] = 'Statistics::ajaxGetSearchscopeFilter';
$wgAjaxExportList[] = 'SpecialExtendedStatistics::ajaxSave';