<?php

BsExtensionManager::registerExtension('WantedArticle', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['WantedArticle'] = __DIR__ . '/WantedArticle.i18n.php';

$wgResourceModules['ext.bluespice.wantedarticle'] = array(
	'scripts' => 'bluespice.wantedArticle.js',
	'styles'  => 'bluespice.wantedArticle.css',
	'messages' => array(
		'bs-wantedarticle-info_dialog_title',
		'bs-wantedarticle-info_nothing_entered',
		'bs-wantedarticle-info_title_contains_invalid_characters'
	),
	'position' => 'top',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/WantedArticle/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/WantedArticle/resources'
);

$wgAjaxExportList[] = 'WantedArticle::ajaxAddWantedArticle';
$wgAjaxExportList[] = 'WantedArticle::ajaxGetWantedArticles';

$wgAutoloadClasses['ViewWantedArticleForm'] = __DIR__ . '/includes/ViewWantedArticleForm.php';
$wgAutoloadClasses['ViewWantedArticleTag']  = __DIR__ . '/includes/ViewWantedArticleTag.php';
