<?php

BsExtensionManager::registerExtension('ExtendedSearch', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['ExtendedSearch'] = __DIR__ . '/languages/ExtendedSearch.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => 'extensions/BlueSpiceExtensions/ExtendedSearch/resources/',
	'remoteBasePath' => 'extensions/BlueSpiceExtensions/ExtendedSearch/resources'
);

$wgResourceModules['ext.bluespice.extendedsearch.autocomplete.style'] = array(
	'styles' => 'bluespice.extendedSearch.autocomplete.css'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.autocomplete'] = array(
	'scripts' => 'bluespice.extendedSearch.autocompleteWidget.js',
	'position' => 'bottom',
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.admin'] = array(
	'scripts' => 'bluespice.extendedSearch.admin.js'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.specialpage.style'] = array(
	'styles' => 'bluespice.extendedSearch.specialpage.css'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.specialpage'] = array(
	'scripts' => 'bluespice.extendedSearch.specialpage.js',
	'position' => 'bottom',
	'messages' => array(
		'bs-extendedsearch-more',
		'bs-extendedsearch-fewer'
	)
) + $aResourceModuleTemplate;

$wgAjaxExportList[] = 'ExtendedSearch::getRequestJson';
$wgAjaxExportList[] = 'ExtendedSearch::getAutocompleteData';
$wgAjaxExportList[] = 'ExtendedSearch::getRecentSearchTerms';

$wgAjaxExportList[] = 'ExtendedSearchAdmin::getProgressBar';

$sDir = __DIR__;

$wgAPIModules['feedextendedsearch'] = 'ApiFeedExtendedSearch';
$wgAutoloadClasses['ApiFeedExtendedSearch'] = $sDir . '/api/ApiFeedExtendedSearch.php';

$wgAutoloadClasses['Apache_Solr_Service'] = $sDir . '/includes/SolrPhpClient/Service.php';
$wgAutoloadClasses['Apache_Solr_Document'] = $sDir . '/includes/SolrPhpClient/Document.php';
$wgAutoloadClasses['Apache_Solr_Response'] = $sDir . '/includes/SolrPhpClient/Response.php';
$wgAutoloadClasses['SearchService'] = $sDir . '/includes/SearchService.class.php';
$wgAutoloadClasses['SolrServiceAdapter'] = $sDir . '/includes/SolrServiceAdapter.class.php';
$wgAutoloadClasses['AbstractBuildIndexAll'] = $sDir . '/includes/BuildIndex/AbstractBuildIndexAll.class.php';
$wgAutoloadClasses['AbstractBuildIndexFile'] = $sDir . '/includes/BuildIndex/AbstractBuildIndexFile.class.php';
$wgAutoloadClasses['AbstractBuildIndexLinked'] = $sDir . '/includes/BuildIndex/AbstractBuildIndexLinked.class.php';
$wgAutoloadClasses['BuildIndexMainControl'] = $sDir . '/includes/BuildIndex/BuildIndexMainControl.class.php';
$wgAutoloadClasses['BuildIndexMwArticles'] = $sDir . '/includes/BuildIndex/BuildIndexMwArticles.class.php';
$wgAutoloadClasses['BuildIndexMwSpecial'] = $sDir . '/includes/BuildIndex/BuildIndexMwSpecial.class.php';
$wgAutoloadClasses['BuildIndexMwRepository'] = $sDir . '/includes/BuildIndex/BuildIndexMwRepository.class.php';
$wgAutoloadClasses['BuildIndexMwExternalRepository'] = $sDir . '/includes/BuildIndex/BuildIndexMwExternalRepository.class.php';
$wgAutoloadClasses['BuildIndexMwSingleFile'] = $sDir . '/includes/BuildIndex/BuildIndexMwSingleFile.class.php';
$wgAutoloadClasses['BuildIndexMwSpecialLinked'] = $sDir . '/includes/BuildIndex/BuildIndexMwSpecialLinked.class.php';
$wgAutoloadClasses['BuildIndexMwLinked'] = $sDir . '/includes/BuildIndex/BuildIndexMwLinked.class.php';
$wgAutoloadClasses['ExtendedSearchAdmin'] = $sDir . '/includes/ExtendedSearchAdmin.class.php';
$wgAutoloadClasses['ExtendedSearchBase'] = $sDir . '/includes/ExtendedSearchBase.class.php';
$wgAutoloadClasses['SearchIndex'] = $sDir . '/includes/SearchIndex/SearchIndex.class.php';
$wgAutoloadClasses['SearchOptions'] = $sDir . '/includes/SearchIndex/SearchOptions.class.php';
$wgAutoloadClasses['SearchRequest'] = $sDir . '/includes/SearchIndex/SearchRequest.class.php';
$wgAutoloadClasses['SearchUriBuilder'] = $sDir . '/includes/SearchIndex/SearchUriBuilder.class.php';

$wgAutoloadClasses['ViewSearchExtendedOptionsForm'] = $sDir . '/views/view.SearchExtendedOptionsForm.php';
$wgAutoloadClasses['ViewSearchResult'] = $sDir . '/views/view.SearchResult.php';
$wgAutoloadClasses['ViewNoOfResultsFound'] = $sDir . '/views/view.NoOfResultsFound.php';
$wgAutoloadClasses['ViewExtendedSearchFormPage'] = $sDir . '/views/view.ExtendedSearchFormPage.php';
$wgAutoloadClasses['ViewSearchMultivalueField'] = $sDir . '/views/view.SearchMultivalueField.php';
$wgAutoloadClasses['ViewExtendedSearchFacetBox'] = $sDir . '/views/view.ExtendedSearchFacetBox.php';
$wgAutoloadClasses['ViewSearchSuggest'] = $sDir . '/views/view.SearchSuggest.php';
$wgAutoloadClasses['ViewSpell'] = $sDir . '/views/view.Spell.php';
$wgAutoloadClasses['ViewMoreLikeThis'] = $sDir . '/views/view.MoreLikeThis.php';
$wgAutoloadClasses['ViewExtendedSearchResultEntry'] = $sDir . '/views/view.ExtendedSearchResultEntry.php';

// Specialpage and messages
$wgAutoloadClasses['SpecialExtendedSearch'] = $sDir . '/includes/specials/SpecialExtendedSearch.class.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgSpecialPageGroups['SpecialExtendedSearch'] = 'bluespice';
$wgExtensionMessagesFiles['ExtendedSearchAlias'] = $sDir . '/includes/specials/SpecialExtendedSearch.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)
$wgSpecialPages['SpecialExtendedSearch'] = 'SpecialExtendedSearch'; # Tell MediaWiki about the new special page and its class name