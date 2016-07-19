<?php

BsExtensionManager::registerExtension('ResponsibleEditors', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgMessagesDirs['ResponsibleEditors'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['ResponsibleEditorsAlias'] = __DIR__ . '/languages/SpecialResponsibleEditors.alias.php';

// Specialpage and messages
$wgAutoloadClasses['ResponsibleEditors'] = __DIR__ . '/ResponsibleEditors.class.php';
$wgAutoloadClasses['BsResponsibleEditor'] = __DIR__ . '/includes/BsResponsibleEditor.php';
$wgAutoloadClasses['BSApiResponsibleEditorsPagesStore'] = __DIR__ . '/includes/api/BSApiResponsibleEditorsPagesStore.php';
$wgAutoloadClasses['BSApiResponsibleEditorsActiveNamespacesStore'] = __DIR__ . '/includes/api/BSApiResponsibleEditorsActiveNamespacesStore.php';
$wgAutoloadClasses['BSApiResponsibleEditorsPossibleEditorsStore'] = __DIR__ . '/includes/api/BSApiResponsibleEditorsPossibleEditorsStore.php';
$wgAutoloadClasses['BSApiTasksResponsibleEditors'] = __DIR__ . '/includes/api/BSApiTasksResponsibleEditors.php';
$wgAutoloadClasses['SpecialResponsibleEditors'] = __DIR__ . '/includes/specials/SpecialResponsibleEditors.class.php';
$wgAutoloadClasses['ResponsibleEditorFormatter'] = __DIR__ . '/includes/ResponsibleEditorFormatter.class.php';

$wgSpecialPages['ResponsibleEditors'] = 'SpecialResponsibleEditors';

$aResourceModuleTemplate = array(
	'dependencies' => 'ext.bluespice',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/ResponsibleEditors/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/ResponsibleEditors/resources'
);

$wgResourceModules['ext.bluespice.responsibleEditors.styles'] = array(
	'styles' => 'bluespice.responsibleEditors.css',
	'position' => 'top'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.responsibleEditors'] = array(
	'scripts' => 'bluespice.responsibleEditors.js',
	'messages' => array(
		'bs-responsibleeditors-availableeditors',
		'bs-responsibleeditors-assignededitors',
		'bs-responsibleeditors-title',
	),
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.responsibleEditors.manager'] = array(
	'scripts' => 'bluespice.responsibleEditors.manager.js',
	'dependencies' => array(
		'ext.bluespice.responsibleEditors',
		'ext.bluespice.extjs',
	),
	'messages' => array(
		'bs-responsibleeditors-cbLabelEditorList',
		'bs-responsibleeditors-cbEmptyText',
		'bs-responsibleeditors-loadMaskMessage',
		'bs-responsibleeditors-columnpage',
		'bs-responsibleeditors-columnresponsibleeditor',
		'bs-responsibleeditors-tipEditAssignment',
		'bs-responsibleeditors-tipRemoveAssignement',
		'bs-responsibleeditors-btnDisplayModeText',
		'bs-responsibleeditors-cbnamespacesemptytext',
		'bs-responsibleeditors-confirmNavigationTitle',
		'bs-responsibleeditors-confirmNavigationText',
		'bs-responsibleeditors-columneesponsibleeditornotset',
		'bs-responsibleeditors-pagestorefilter-assigned',
		'bs-responsibleeditors-pagestorefilter-notassigned',
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.responsibleEditors.bookshelfPlugin'] = array(
	'scripts' => array(
		'bluespice.responsibleEditors.BookshelfPlugin.js',
	),
	'dependencies' => 'ext.bluespice.responsibleEditors',
	'messages' => array(
		'bs-responsibleeditors-titleeditors',
		'bs-responsibleeditors-cmchangerespeditors',
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.responsibleEditors.superList'] = array(
	'scripts' => array(
		'bluespice.responsibleEditors.SuperList.js',
	),
	'dependencies' => array(
		'ext.bluespice.responsibleEditors'
	)
) + $aResourceModuleTemplate;

$wgAPIModules['bs-responsibleeditorspages-store'] = 'BSApiResponsibleEditorsPagesStore';
$wgAPIModules['bs-responsibleeditorspossibleeditors-store'] = 'BSApiResponsibleEditorsPossibleEditorsStore';
$wgAPIModules['bs-responsibleeditorsactivenamespaces-store'] = 'BSApiResponsibleEditorsActiveNamespacesStore';
$wgAPIModules['bs-responsibleeditors-tasks'] = 'BSApiTasksResponsibleEditors';

//TODO: Revisit when rework dashboards. Find a gerneric portlet store solution
$wgAjaxExportList[] = 'ResponsibleEditors::getResponsibleEditorsPortletData';

$wgLogTypes[] = 'bs-responsible-editors';
$wgFilterLogTypes['bs-responsible-editors'] = true;
$wgLogActionsHandlers['bs-responsible-editors/*'] = 'LogFormatter';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'ResponsibleEditors::getSchemaUpdates';

$GLOBALS["bssDefinitions"]["_RESPEDITOR"] = array(
	"id" => "___RESPEDITOR",
	"type" => 9,
	"show" => false,
	"msgkey" => "prefs-responsibleeditors",
	"alias" => "Responsible editor",
	"mapping" => "ResponsibleEditors::addPropertyValues"
);

unset( $aResourceModuleTemplate );
