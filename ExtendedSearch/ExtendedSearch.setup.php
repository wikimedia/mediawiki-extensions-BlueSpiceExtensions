<?php
wfLoadExtension( 'BlueSpiceExtensions/ExtendedSearch' );

$GLOBALS["wgAjaxExportList"][] = "ExtendedSearch::getRequestJson";
$GLOBALS["wgAjaxExportList"][] = "ExtendedSearchBase::getAutocompleteData";
$GLOBALS["wgAjaxExportList"][] = "ExtendedSearchBase::getRecentSearchTerms";
$GLOBALS["wgAjaxExportList"][] = "ExtendedSearchAdmin::getProgressBar";
