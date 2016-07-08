<?php

BsExtensionManager::registerExtension('WantedArticle', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['WantedArticle'] = __DIR__ . '/WantedArticle.class.php';

$wgAutoloadClasses['ViewWantedArticleForm'] = __DIR__ . '/includes/ViewWantedArticleForm.php';
$wgAutoloadClasses['ViewWantedArticleTag']  = __DIR__ . '/includes/ViewWantedArticleTag.php';
$wgAutoloadClasses['BSApiTasksWantedArticle'] = __DIR__ . '/includes/api/BSApiTasksWantedArticle.php';

$wgMessagesDirs['WantedArticle'] = __DIR__ . '/i18n';

$wgResourceModules['ext.bluespice.wantedarticle'] = array(
	'scripts' => 'bluespice.wantedArticle.js',
	'messages' => array(
		'bs-wantedarticle-info-nothing-entered',
		'bs-wantedarticle-title-invalid-chars'
	),
	'position' => 'top',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/WantedArticle/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/WantedArticle/resources'
);

$wgAPIModules['bs-wantedarticle'] = 'BSApiTasksWantedArticle';