<?php

BsExtensionManager::registerExtension('ResponsibleEditors',              BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$dir = dirname(__FILE__);

$wgExtensionMessagesFiles['ResponsibleEditors']      = $dir . '/ResponsibleEditors.i18n.php';
$wgExtensionMessagesFiles['ResponsibleEditorsAlias'] = $dir . '/includes/specials/SpecialResponsibleEditors.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)

// Specialpage and messages
$wgAutoloadClasses['SpecialResponsibleEditors'] = $dir . '/includes/specials/SpecialResponsibleEditors.class.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgSpecialPageGroups['ResponsibleEditors'] = 'bluespice';
$wgSpecialPages['ResponsibleEditors'] = 'SpecialResponsibleEditors'; # Tell MediaWiki about the new special page and its class name

$wgResourceModules['ext.bluespice.responsibleEditors'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/ResponsibleEditors/resources/bluespice.responsibleEditors.js',
	),
	'dependencies' => 'ext.bluespice.responsibleEditors.specialAssignmentWindow',
	'styles' => 'extensions/BlueSpiceExtensions/ResponsibleEditors/resources/bluespice.responsibleEditors.css',
	'messages' => array(
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
		'bs-responsibleeditors-columnResponsibleEditorNotSet',
		'bs-responsibleeditors-pageSize',
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath'],
);

$wgResourceModules['ext.bluespice.responsibleEditors.assignmentPanel'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/ResponsibleEditors/resources/bluespice.responsibleEditors.lib.AssignmentPanel.js',		
	),
	'messages' => array(
		'bs-responsibleeditors-availableEditors',
		'bs-responsibleeditors-assignedEditors',
		'bs-responsibleeditors-save',
		'bs-responsibleeditors-cancel',
		'bs-responsibleeditors-title',
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath'],
);

$wgResourceModules['ext.bluespice.responsibleEditors.specialAssignmentDialog'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/ResponsibleEditors/resources/bluespice.responsibleEditors.SpecialPage.AssignmentDialog.js',		
	),
	'dependencies' => 'ext.bluespice.responsibleEditors.assignmentPanel',
	'messages' => array(
		'bs-responsibleeditors-dialogTitle',
		'bs-responsibleeditors-btnOK',
		'bs-responsibleeditors-btnCancel',
		'bs-responsibleeditors-pnlDescriptionText',
		'bs-responsibleeditors-pnlSucessText',
		'bs-responsibleeditors-pnlFailureText',
		'bs-responsibleeditors-cbLabelEditorList',
		'bs-responsibleeditors-cbEmptyText',
		'bs-responsibleeditors-loadMaskMessage',
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath'],
);

$wgResourceModules['ext.bluespice.responsibleEditors.specialAssignmentWindow'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/ResponsibleEditors/resources/bluespice.responsibleEditors.SpecialPage.AssignmentWindow.js',		
	),
	'dependencies' => 'ext.bluespice.responsibleEditors.assignmentPanel',
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath'],
);

$wgResourceModules['ext.bluespice.responsibleEditors.bookshelfPlugin'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/ResponsibleEditors/resources/bluespice.responsibleEditors.BookshelfPlugin.js',		
	),
	'dependencies' => 'ext.bluespice.responsibleEditors',
	'messages' => array(
		'bs-responsibleeditors-titleEditors',
		'bs-responsibleeditors-cmChangeRespEditors',
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath'],
);
