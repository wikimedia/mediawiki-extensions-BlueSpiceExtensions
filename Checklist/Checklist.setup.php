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
		'bs-checklist-button-checkbox-title',
		'bs-checklist-menu-insert-list-title',
		'bs-checklist-menu-insert-checkbox',
		'bs-checklist-dlg-insert-list-title',
		'bs-checklist-dlg-insert-list-value-list',
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.checklist.styles'] = array(
	'styles' => 'bluespice.checklist.css',
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAjaxExportList[] = 'Checklist::doChangeCheckItem';
$wgAjaxExportList[] = 'Checklist::getOptionsList';
#$wgAutoloadClasses['ViewChecklistCheck'] = __DIR__ . '/views/view.ChecklistCheck.php';