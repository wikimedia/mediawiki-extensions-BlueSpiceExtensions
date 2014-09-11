<?php

BsExtensionManager::registerExtension('Checklist', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['Checklist'] = __DIR__ . '/Checklist.class.php';

$wgExtensionMessagesFiles['Checklist'] = __DIR__ . '/languages/Checklist.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__.'/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/Checklist/resources',
);

$wgResourceModules['ext.bluespice.checklist'] = array(
	'scripts' => array(
		'BS.Checklist/Checklist.js',
		'BS.Checklist/ChecklistBoxSelect.js',
		'bluespice.checklist.js',
	),
	'messages' => array(
		'bs-checklist-button-checkbox-title',
		'bs-checklist-menu-insert-list-title',
		'bs-checklist-menu-insert-checkbox',
		'bs-checklist-dlg-insert-list-title',
		'bs-checklist-dlg-insert-list-value-list',
		'bs-checklist-dlg-new-list',
		'bs-checklist-dlg-save-list',
		'bs-checklist-dlg-items-label',
		'bs-checklist-dlg-items-emptytext',
		'bs-checklist-dlg-items-hint',
		'bs-checklist-dlg-panel-title',
		'bs-checklist-dlg-new-title',
		'bs-checklist-dlg-new-prompt',
		'bs-checklist-alert',
		'bs-checklist-confirm-dirty-title',
		'bs-checklist-confirm-dirty-text'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.checklist.styles'] = array(
	'styles' => 'bluespice.checklist.css',
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAjaxExportList[] = 'Checklist::doChangeCheckItem';
$wgAjaxExportList[] = 'Checklist::getOptionsList';
$wgAjaxExportList[] = 'Checklist::ajaxGetTemplateData';
$wgAjaxExportList[] = 'Checklist::ajaxSaveOptionsList';
$wgAjaxExportList[] = 'Checklist::ajaxGetItemStoreData';
$wgAjaxExportList[] = 'Checklist::getAvailableOptions';
#$wgAutoloadClasses['ViewChecklistCheck'] = __DIR__ . '/views/view.ChecklistCheck.php';