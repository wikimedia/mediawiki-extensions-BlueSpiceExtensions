<?php

BsExtensionManager::registerExtension( 'PageTemplates', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$wgMessagesDirs['PageTemplates'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['PageTemplates'] = __DIR__ . '/languages/PageTemplates.i18n.php';

$GLOBALS['wgAutoloadClasses']['PageTemplates'] = __DIR__ . '/PageTemplates.class.php';

$wgResourceModules['ext.bluespice.pageTemplates'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/PageTemplates/resources/bluespice.pageTemplates.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-pagetemplates-headerlabel',
		'bs-pagetemplates-headertargetnamespace',
		'bs-pagetemplates-tipeditdetails',
		'bs-pagetemplates-tipdeletetemplate',
		'bs-pagetemplates-tipaddtemplate',
		'bs-pagetemplates-label-tpl',
		'bs-pagetemplates-label-desc',
		'bs-pagetemplates-label-targetns',
		'bs-pagetemplates-label-tplns',
		'bs-pagetemplates-label-article',
		'bs-pagetemplates-confirm-deletetpl',
		'bs-pagetemplates-remove-message-unknown',
		'bs-pagetemplates-remove-message-success',
		'bs-pagetemplates-remove-message-failure'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAutoloadClasses['PageTemplatesAdmin'] = __DIR__ . '/PageTemplatesAdmin.class.php';

$wgAjaxExportList[] = 'PageTemplatesAdmin::getTemplates';
$wgAjaxExportList[] = 'PageTemplatesAdmin::getNamespaces';
$wgAjaxExportList[] = 'PageTemplatesAdmin::doEditTemplate';
$wgAjaxExportList[] = 'PageTemplatesAdmin::doDeleteTemplate';
$wgAjaxExportList[] = 'PageTemplatesAdmin::doDeleteTemplates';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'PageTemplates::getSchemaUpdates';