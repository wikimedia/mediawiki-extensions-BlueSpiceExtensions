<?php

BsExtensionManager::registerExtension('ResponsibleEditors', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['ResponsibleEditors']      = __DIR__ . '/languages/ResponsibleEditors.i18n.php';
$wgExtensionMessagesFiles['ResponsibleEditorsAlias'] = __DIR__ . '/includes/specials/SpecialResponsibleEditors.alias.php';

// Specialpage and messages
$wgAutoloadClasses['BsResponsibleEditor']       = __DIR__ . '/includes/BsResponsibleEditor.php';
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
	'position' => 'top',
) + $aResourceModuleTemplate;;

$wgResourceModules['ext.bluespice.responsibleEditors'] = array(
	'scripts' => 'bluespice.responsibleEditors.js',
	'position' => 'bottom',
	'messages' => array(
		'bs-responsibleeditors-availableEditors',
		'bs-responsibleeditors-assignedEditors',
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
		'bs-responsibleeditors-pnlDescriptionText',
		'bs-responsibleeditors-pnlSucessText',
		'bs-responsibleeditors-pnlFailureText',
		'bs-responsibleeditors-cbLabelEditorList',
		'bs-responsibleeditors-cbEmptyText',
		'bs-responsibleeditors-loadMaskMessage',
		'bs-responsibleeditors-columnHeaderArticle',
		'bs-responsibleeditors-columnHeaderResponsibleEditor',
		'bs-responsibleeditors-columnHeaderNamespace',
		'bs-responsibleeditors-columnHeaderActions',
		'bs-responsibleeditors-tipEditAssignment',
		'bs-responsibleeditors-tipRemoveAssignement',
		'bs-responsibleeditors-btnDisplayModeText',
		'bs-responsibleeditors-rbDisplayModeOnlyAssignedText',
		'bs-responsibleeditors-rbDisplayModeOnlyNotAssigned',
		'bs-responsibleeditors-rbDisplayModeAll',
		'bs-responsibleeditors-ptbDisplayMsgText',
		'bs-responsibleeditors-ptbEmptyMsgText',
		'bs-responsibleeditors-ptbBeforePageText',
		'bs-responsibleeditors-ptbAfterPageText',
		'bs-responsibleeditors-cbNamespacesEmptyText',
		'bs-responsibleeditors-cbNamespacesLable',
		'bs-responsibleeditors-confirmNavigationTitle',
		'bs-responsibleeditors-confirmNavigationText',
		'bs-responsibleeditors-columnResponsibleEditorNotSet'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.responsibleEditors.bookshelfPlugin'] = array(
	'scripts' => array(
		'bluespice.responsibleEditors.BookshelfPlugin.js',		
	),
	'dependencies' => 'ext.bluespice.responsibleEditors',
	'messages' => array(
		'bs-responsibleeditors-titleEditors',
		'bs-responsibleeditors-cmChangeRespEditors',
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

unset( $aResourceModuleTemplate );