<?php

BsExtensionManager::registerExtension('InsertCategory',                  BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['InsertCategory'] = dirname( __FILE__ ) . '/InsertCategory.i18n.php';

$wgResourceModules['ext.bluespice.insertcategory'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/InsertCategory/InsertCategory.js',
	'styles'  => 'extensions/BlueSpiceExtensions/InsertCategory/InsertCategory.css',
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
		'bs-insertcategory-failure'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);