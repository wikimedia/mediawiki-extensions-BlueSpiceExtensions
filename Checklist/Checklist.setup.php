<?php

BsExtensionManager::registerExtension('Checklist', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['Checklist'] = __DIR__ . '/Checklist.class.php';

$GLOBALS['wgAutoloadClasses']['BSApiChecklistAvailableOptionsStore'] = __DIR__ . '/includes/api/BSApiChecklistAvailableOptionsStore.php';
$GLOBALS['wgAutoloadClasses']['BSApiChecklistTemplateStore'] = __DIR__ . '/includes/api/BSApiChecklistTemplateStore.php';
$GLOBALS['wgAutoloadClasses']['BSApiChecklistTasks'] = __DIR__ . '/includes/api/BSApiChecklistTasks.php';

$wgAPIModules['bs-checklist-available-options-store'] = 'BSApiChecklistAvailableOptionsStore';
$wgAPIModules['bs-checklist-template-store'] = 'BSApiChecklistTemplateStore';
$wgAPIModules['bs-checklist-tasks'] = 'BSApiChecklistTasks';

$wgExtensionMessagesFiles['Checklist'] = __DIR__ . '/languages/Checklist.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__.'/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/Checklist/resources',
);

$wgResourceModules['ext.bluespice.checklist'] = array(
	'scripts' => array(
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
	),
	'dependencies' => array(
		'ext.bluespice.extjs'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.checklist.styles'] = array(
	'styles' => 'bluespice.checklist.css',
	'position' => 'top'
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgLogTypes[] = 'bs-checklist';
$wgFilterLogTypes['bs-checklist'] = true;
$wgLogActionsHandlers['bs-checklist/*'] = 'LogFormatter';
