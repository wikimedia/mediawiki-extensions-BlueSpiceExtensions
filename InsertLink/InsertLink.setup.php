<?php

BsExtensionManager::registerExtension('InsertLink', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['InsertLink'] = __DIR__ . '/languages/InsertLink.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__.'/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/InsertLink/resources'
);

$wgResourceModules['ext.bluespice.insertlink'] = array(
	'scripts' => 'bluespice.insertLink.js',
	'messages' => array(
		'bs-insertlink-button_title',
		'bs-insertlink-dlg_title',
		'bs-insertlink-tab_wiki_page',
		'bs-insertlink-tab_external_link',
		'bs-insertlink-tab3_title',
		'bs-insertlink-tab6_title',
		'bs-insertlink-label_page',
		'bs-insertlink-select_a_page',
		'bs-insertlink-label_link',
		'bs-insertlink-label_mail',
		'bs-insertlink-label_namespace',
		'bs-insertlink-select_a_namespace',
		'bs-insertlink-label_description',
		'bs-insertlink-label_ok',
		'bs-insertlink-label_cancel',
		'bs-insertlink-label_file',
		'bs-insertlink-label_searchfile'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.insertlink.styles'] = array(
	'styles' => 'bluespice.insertLink.css',
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAjaxExportList[] = 'InsertLink::getNamespace';
$wgAjaxExportList[] = 'InsertLink::getPage';
