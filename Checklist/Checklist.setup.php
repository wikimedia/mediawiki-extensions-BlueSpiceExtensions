<?php

BsExtensionManager::registerExtension('Checklist', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['Checklist'] = __DIR__ . '/languages/Checklist.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__.'/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/Checklist/resources',
);

$wgResourceModules['ext.bluespice.checklist'] = array(
	'scripts' => 'bluespice.checklist.js',
	'messages' => array(
		'bs-checklist-button_checkbox_title',
		'bs-checklist-menu_insert_list_title',
		'bs-checklist-menu_insert_checkbox',
		'bs-checklist-dlg_insert_list_title',
		'bs-checklist-dlg_insert_list_value_list',
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.checklist.styles'] = array(
	'styles' => 'bluespice.checklist.css',
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAjaxExportList[] = 'Checklist::doChangeCheckItem';
$wgAjaxExportList[] = 'Checklist::getOptionsList';
#$wgAutoloadClasses['ViewChecklistCheck'] = __DIR__ . '/views/view.ChecklistCheck.php';