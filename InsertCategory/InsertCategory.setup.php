<?php

BsExtensionManager::registerExtension('InsertCategory', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['InsertCategory'] = __DIR__ . '/languages/InsertCategory.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/InsertCategory/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/InsertCategory/resources'
);

$wgResourceModules['ext.bluespice.insertcategory'] = array(
	'scripts' => 'bluespice.insertCategory.js',
	'messages' => array(
		'bs-insertcategory-button_title',
		'bs-insertcategory-title',
		'bs-insertcategory-cat_label',
		'bs-insertcategory-emptyText',
		'bs-insertcategory-cat_tag',
		'bs-insertcategory-ok',
		'bs-insertcategory-cancel',
		'bs-insertcategory-delete_cat',
		'bs-insertcategory-selected_cats',
		'bs-insertcategory-avail_cats',
		'bs-insertcategory-tb_1',
		'bs-insertcategory-new_category',
		'bs-insertcategory-new_category_btn',
		'bs-insertcategory-tb_2',
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