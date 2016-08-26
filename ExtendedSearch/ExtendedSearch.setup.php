<?php
wfLoadExtension( 'BlueSpiceExtensions/ExtendedSearch' );

$wgAjaxExportList[] = 'ExtendedSearch::getRequestJson';
$wgAjaxExportList[] = 'ExtendedSearchBase::getAutocompleteData';
$wgAjaxExportList[] = 'ExtendedSearchBase::getRecentSearchTerms';

$wgAjaxExportList[] = 'ExtendedSearchAdmin::getProgressBar';

