<?php
BsExtensionManager::registerExtension('FormattingHelp', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgAutoloadClasses['FormattingHelp'] = __DIR__ . '/FormattingHelp.class.php';

$wgMessagesDirs['FormattingHelp'] = __DIR__ . '/i18n';

$wgAutoloadClasses['BSApiTasksFormattingHelp'] = __DIR__ . '/includes/api/BSApiTasksFormattingHelp.php';
$wgAPIModules['bs-formattinghelp'] = 'BSApiTasksFormattingHelp';

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
	'position' => 'top'
) + $aResourceModuleTemplate;

unset($aResourceModuleTemplate);
unset( $sDir );