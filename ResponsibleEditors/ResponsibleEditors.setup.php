<?php

BsExtensionManager::registerExtension('ResponsibleEditors', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgMessagesDirs['ResponsibleEditors'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['ResponsibleEditors'] = __DIR__ . '/languages/ResponsibleEditors.i18n.php';
$wgExtensionMessagesFiles['ResponsibleEditorsAlias'] = __DIR__ . '/languages/SpecialResponsibleEditors.alias.php';

// Specialpage and messages
$GLOBALS['wgAutoloadClasses']['ResponsibleEditors'] = __DIR__ . '/ResponsibleEditors.class.php';
$wgAutoloadClasses['BsResponsibleEditor'] = __DIR__ . '/includes/BsResponsibleEditor.php';
$wgAutoloadClasses['SpecialResponsibleEditors'] = __DIR__ . '/includes/specials/SpecialResponsibleEditors.class.php';

$wgSpecialPageGroups['ResponsibleEditors'] = 'bluespice';
$wgSpecialPages['ResponsibleEditors'] = 'SpecialResponsibleEditors';

$aResourceModuleTemplate = array(
	'dependencies' => 'ext.bluespice',
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/ResponsibleEditors/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/ResponsibleEditors/resources'
);

$wgResourceModules['ext.bluespice.responsibleEditors.styles'] = array(
	'styles' => 'bluespice.responsibleEditors.css',
) + $aResourceModuleTemplate;;

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
		'bs-responsibleeditors-columnnamespace',
		'bs-responsibleeditors-tipEditAssignment',
		'bs-responsibleeditors-tipRemoveAssignement',
		'bs-responsibleeditors-btnDisplayModeText',
		'bs-responsibleeditors-rbdisplaymodeonlyassignedtext',
		'bs-responsibleeditors-rbdisplaymodeonlynotassigned',
		'bs-responsibleeditors-rbdisplaymodeall',
		'bs-responsibleeditors-cbnamespacesemptytext',
		'bs-responsibleeditors-confirmNavigationTitle',
		'bs-responsibleeditors-confirmNavigationText',
		'bs-responsibleeditors-columneesponsibleeditornotset'
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

$wgAjaxExportList[] = 'SpecialResponsibleEditors::ajaxGetResponsibleEditors';
$wgAjaxExportList[] = 'SpecialResponsibleEditors::ajaxSetResponsibleEditors';
$wgAjaxExportList[] = 'SpecialResponsibleEditors::ajaxGetPossibleEditors';
$wgAjaxExportList[] = 'ResponsibleEditors::ajaxGetActivatedNamespacesForCombobox';
$wgAjaxExportList[] = 'ResponsibleEditors::ajaxGetResponsibleEditorsByArticleId';
$wgAjaxExportList[] = 'ResponsibleEditors::ajaxGetArticlesByNamespaceId';
$wgAjaxExportList[] = 'ResponsibleEditors::ajaxGetListOfResponsibleEditorsForArticle';
$wgAjaxExportList[] = 'ResponsibleEditors::ajaxDeleteResponsibleEditorsForArticle';
$wgAjaxExportList[] = 'ResponsibleEditors::getResponsibleEditorsPortletData';

$wgLogTypes[] = 'bs-responsibleeditors';
$wgFilterLogTypes['bs-responsibleeditors'] = true;

$wgHooks['LoadExtensionSchemaUpdates'][] = 'ResponsibleEditors::getSchemaUpdates';

unset( $aResourceModuleTemplate );