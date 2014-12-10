<?php
BsExtensionManager::registerExtension('FormattingHelp', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['FormattingHelp'] = __DIR__ . '/FormattingHelp.class.php';

$wgMessagesDirs['FormattingHelp'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['FormattingHelp'] = __DIR__ . '/languages/FormattingHelp.i18n.php';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/FormattingHelp/resources'
);

$wgResourceModules['ext.bluespice.formattinghelp'] = array(
	'scripts' => 'bluespice.formattinghelp.js',
	'messages' => array(
		'bs-formattinghelp-formatting',
		'bs-formattinghelp-help-text'
	),
	'dependencies' => 'mediawiki.action.edit',
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.formattinghelp.styles'] = array(
	'styles' => 'bluespice.formattinghelp.css',
) + $aResourceModuleTemplate;

unset($aResourceModuleTemplate);

$wgAjaxExportList[] = 'FormattingHelp::getFormattingHelp';