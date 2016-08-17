<?php
wfLoadExtension( 'BlueSpiceExtensions/Statistics' );

$wgAjaxExportList[] = 'Statistics::ajaxGetAvalableDiagrams';
$wgAjaxExportList[] = 'Statistics::ajaxGetUserFilter';
$wgAjaxExportList[] = 'Statistics::ajaxGetNamespaceFilter';
$wgAjaxExportList[] = 'Statistics::ajaxGetCategoryFilter';
$wgAjaxExportList[] = 'Statistics::ajaxGetSearchscopeFilter';
$wgAjaxExportList[] = 'SpecialExtendedStatistics::ajaxSave';