<?php

BsExtensionManager::registerExtension( 'ExtendedSearch', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE );

$wgMessagesDirs['ExtendedSearch'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['ExtendedSearch'] = __DIR__ . '/languages/ExtendedSearch.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => 'extensions/BlueSpiceExtensions/ExtendedSearch/resources/',
	'remoteExtPath' => 'BlueSpiceExtensions/ExtendedSearch/resources'
);

$wgResourceModules['ext.bluespice.extendedsearch.focus'] = array(
	'scripts' => 'bluespice.extendedSearch.focus.js',
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.form'] = array(
	'scripts' => 'bluespice.extendedSearch.form.js',
	'styles' => 'bluespice.extendedSearch.form.css'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.autocomplete.style'] = array(
	'styles' => 'bluespice.extendedSearch.autocomplete.css'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.autocomplete'] = array(
	'scripts' => 'bluespice.extendedSearch.autocomplete.js',
	'dependencies' => array(
		'jquery.ui.autocomplete'
	),
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.admin'] = array(
	'scripts' => 'bluespice.extendedSearch.admin.js'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.specialpage.style'] = array(
	'styles' => 'bluespice.extendedSearch.specialpage.css'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendedsearch.specialpage'] = array(
	'scripts' => 'bluespice.extendedSearch.specialpage.js',
	'messages' => array(
		'bs-extendedsearch-more',
		'bs-extendedsearch-fewer'
	)
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAjaxExportList[] = 'ExtendedSearch::getRequestJson';
$wgAjaxExportList[] = 'ExtendedSearchBase::getAutocompleteData';
$wgAjaxExportList[] = 'ExtendedSearchBase::getRecentSearchTerms';

$wgAjaxExportList[] = 'ExtendedSearchAdmin::getProgressBar';

$wgAPIModules['feedextendedsearch'] = 'ApiFeedExtendedSearch';
$wgAutoloadClasses['ApiFeedExtendedSearch'] = __DIR__ . '/api/ApiFeedExtendedSearch.php';

$GLOBALS['wgAutoloadClasses']['ExtendedSearch'] = __DIR__ . '/ExtendedSearch.class.php';
$wgAutoloadClasses['Apache_Solr_Service'] = __DIR__ . '/includes/SolrPhpClient/Service.php';
$wgAutoloadClasses['Apache_Solr_Document'] = __DIR__ . '/includes/SolrPhpClient/Document.php';
$wgAutoloadClasses['Apache_Solr_Response'] = __DIR__ . '/includes/SolrPhpClient/Response.php';
$wgAutoloadClasses['SearchService'] = __DIR__ . '/includes/SearchService.class.php';
$wgAutoloadClasses['SolrServiceAdapter'] = __DIR__ . '/includes/SolrServiceAdapter.class.php';
$wgAutoloadClasses['AbstractBuildIndexAll'] = __DIR__ . '/includes/BuildIndex/AbstractBuildIndexAll.class.php';
$wgAutoloadClasses['AbstractBuildIndexFile'] = __DIR__ . '/includes/BuildIndex/AbstractBuildIndexFile.class.php';
$wgAutoloadClasses['AbstractBuildIndexLinked'] = __DIR__ . '/includes/BuildIndex/AbstractBuildIndexLinked.class.php';
$wgAutoloadClasses['BuildIndexMainControl'] = __DIR__ . '/includes/BuildIndex/BuildIndexMainControl.class.php';
$wgAutoloadClasses['BuildIndexMwArticles'] = __DIR__ . '/includes/BuildIndex/BuildIndexMwArticles.class.php';
$wgAutoloadClasses['BuildIndexMwSpecial'] = __DIR__ . '/includes/BuildIndex/BuildIndexMwSpecial.class.php';
$wgAutoloadClasses['BuildIndexMwRepository'] = __DIR__ . '/includes/BuildIndex/BuildIndexMwRepository.class.php';
$wgAutoloadClasses['BuildIndexMwExternalRepository'] = __DIR__ . '/includes/BuildIndex/BuildIndexMwExternalRepository.class.php';
$wgAutoloadClasses['BuildIndexMwSingleFile'] = __DIR__ . '/includes/BuildIndex/BuildIndexMwSingleFile.class.php';
$wgAutoloadClasses['BuildIndexMwSpecialLinked'] = __DIR__ . '/includes/BuildIndex/BuildIndexMwSpecialLinked.class.php';
$wgAutoloadClasses['BuildIndexMwLinked'] = __DIR__ . '/includes/BuildIndex/BuildIndexMwLinked.class.php';
$wgAutoloadClasses['ExtendedSearchAdmin'] = __DIR__ . '/includes/ExtendedSearchAdmin.class.php';
$wgAutoloadClasses['ExtendedSearchBase'] = __DIR__ . '/includes/ExtendedSearchBase.class.php';
$wgAutoloadClasses['SearchIndex'] = __DIR__ . '/includes/SearchIndex/SearchIndex.class.php';
$wgAutoloadClasses['SearchOptions'] = __DIR__ . '/includes/SearchIndex/SearchOptions.class.php';
$wgAutoloadClasses['SearchRequest'] = __DIR__ . '/includes/SearchIndex/SearchRequest.class.php';
$wgAutoloadClasses['SearchUriBuilder'] = __DIR__ . '/includes/SearchIndex/SearchUriBuilder.class.php';
$wgAutoloadClasses['BsSearchResult'] = __DIR__ . '/includes/SearchIndex/SearchResult.class.php';

$wgAutoloadClasses['ViewSearchExtendedOptionsForm'] = __DIR__ . '/views/view.SearchExtendedOptionsForm.php';
$wgAutoloadClasses['ViewSearchResult'] = __DIR__ . '/views/view.SearchResult.php';
$wgAutoloadClasses['ViewNoOfResultsFound'] = __DIR__ . '/views/view.NoOfResultsFound.php';
$wgAutoloadClasses['ViewExtendedSearchFormPage'] = __DIR__ . '/views/view.ExtendedSearchFormPage.php';
$wgAutoloadClasses['ViewSearchMultivalueField'] = __DIR__ . '/views/view.SearchMultivalueField.php';
$wgAutoloadClasses['ViewSearchFacet'] = __DIR__ . '/views/view.ExtendedSearchFacetBox.php';
$wgAutoloadClasses['ViewSearchSuggest'] = __DIR__ . '/views/view.SearchSuggest.php';
$wgAutoloadClasses['ViewSpell'] = __DIR__ . '/views/view.Spell.php';
$wgAutoloadClasses['ViewMoreLikeThis'] = __DIR__ . '/views/view.MoreLikeThis.php';
$wgAutoloadClasses['ViewExtendedSearchResultEntry'] = __DIR__ . '/views/view.ExtendedSearchResultEntry.php';

// Specialpage and messages
$wgAutoloadClasses['SpecialExtendedSearch'] = __DIR__ . '/includes/specials/SpecialExtendedSearch.class.php';
$wgSpecialPageGroups['SpecialExtendedSearch'] = 'bluespice';
$wgExtensionMessagesFiles['ExtendedSearchAlias'] = __DIR__ . '/languages/SpecialExtendedSearch.alias.php';
$wgSpecialPages['SpecialExtendedSearch'] = 'SpecialExtendedSearch';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'ExtendedSearch::getSchemaUpdates';
$GLOBALS['wgHooks']['OpenSearchUrls'][] = 'ExtendedSearch::onOpenSearchUrls';