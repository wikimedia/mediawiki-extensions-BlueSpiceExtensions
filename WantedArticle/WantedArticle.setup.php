<?php

BsExtensionManager::registerExtension('WantedArticle', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['WantedArticle'] = __DIR__ . '/WantedArticle.class.php';

$wgMessagesDirs['WantedArticle'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['WantedArticle'] = __DIR__ . '/languages/WantedArticle.i18n.php';

$wgResourceModules['ext.bluespice.wantedarticle'] = array(
	'scripts' => 'bluespice.wantedArticle.js',
	//'styles'  => 'bluespice.wantedArticle.css', 17.05.2014 13:43 STM: Not needed at the moment because wantedarticle from is not used anymore - not removed because maybe future use
	'messages' => array(
		'bs-wantedarticle-info-nothing-entered',
		'bs-wantedarticle-title-invalid-chars'
	),
	'position' => 'top',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/WantedArticle/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/WantedArticle/resources'
);

$wgAjaxExportList[] = 'WantedArticle::ajaxAddWantedArticle';
$wgAjaxExportList[] = 'WantedArticle::ajaxGetWantedArticles';

$wgAutoloadClasses['ViewWantedArticleForm'] = __DIR__ . '/includes/ViewWantedArticleForm.php';
$wgAutoloadClasses['ViewWantedArticleTag']  = __DIR__ . '/includes/ViewWantedArticleTag.php';