<?php

BsExtensionManager::registerExtension('InsertCategory', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['InsertCategory'] = __DIR__ . '/InsertCategory.class.php';

$wgMessagesDirs['InsertCategory'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['InsertCategory'] = __DIR__ . '/languages/InsertCategory.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/InsertCategory/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/InsertCategory/resources'
);

$wgResourceModules['ext.bluespice.insertcategory'] = array(
	'scripts' => 'bluespice.insertCategory.js',
	'messages' => array(
		'bs-insertcategory-title',
		'bs-insertcategory-cat-label',
		'bs-insertcategory-success',
		'bs-insertcategory-failure',
		'bs-insertcategory-hint',
		'bs-insertcategory-panel-title'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.insertcategory.styles'] = array(
	'styles' => 'bluespice.insertCategory.css'
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAjaxExportList[] = 'InsertCategory::addCategoriesToArticle';