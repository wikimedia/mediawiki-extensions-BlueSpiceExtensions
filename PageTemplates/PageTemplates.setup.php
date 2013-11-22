<?php

BsExtensionManager::registerExtension('PageTemplates',                   BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['PageTemplates'] = __DIR__ . '/PageTemplates.i18n.php';

$wgResourceModules['ext.bluespice.pageTemplates'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/PageTemplates/resources/bluespice.pageTemplates.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-pagetemplates-headerLabel',
		'bs-pagetemplates-headerDescription',
		'bs-pagetemplates-headerTargetNamespace',
		'bs-pagetemplates-headerTemplate',
		'bs-pagetemplates-headerActions',
		'bs-pagetemplates-tipEditDetails',
		'bs-pagetemplates-tipDeleteTemplate',
		'bs-pagetemplates-tipAddTemplate',
		'bs-pagetemplates-btnOk',
		'bs-pagetemplates-btnCancel',
		'bs-pagetemplates-titleError',
		'bs-pagetemplates-unknownError',
		'bs-pagetemplates-titleAddTemplate',
		'bs-pagetemplates-titleEditDetails',
		'bs-pagetemplates-labelLabel',
		'bs-pagetemplates-labelDescription',
		'bs-pagetemplates-labelTargetNamespace',
		'bs-pagetemplates-labelTemplateNamespace',
		'bs-pagetemplates-labelArticle',
		'bs-pagetemplates-titleDeleteTemplate',
		'bs-pagetemplates-confirmDeleteTemplate',
		'bs-pagetemplates-showEntries',
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['PageTemplatesAdmin'] = __DIR__ . '/PageTemplatesAdmin.class.php';

$wgAjaxExportList[] = 'PageTemplatesAdmin::getTemplates';
$wgAjaxExportList[] = 'PageTemplatesAdmin::getNamespaces';
$wgAjaxExportList[] = 'PageTemplatesAdmin::doEditTemplate';
$wgAjaxExportList[] = 'PageTemplatesAdmin::doDeleteTemplate';