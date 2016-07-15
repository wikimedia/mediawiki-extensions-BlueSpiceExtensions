<?php

BsExtensionManager::registerExtension('ExtendedEditBar', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgAutoloadClasses['ExtendedEditBar'] = __DIR__ . '/ExtendedEditBar.class.php';

$wgMessagesDirs['ExtendedEditBar'] = __DIR__ . '/i18n';

$aResourceModuleTemplate = array(
	'localBasePath' => $IP.'/extensions/BlueSpiceExtensions/ExtendedEditBar/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/ExtendedEditBar/resources',
);

$wgResourceModules['ext.bluespice.extendeditbar'] = array(
	'scripts' => 'bluespice.extendedEditBar.js',
	'dependencies' => 'mediawiki.action.edit',
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.extendeditbar.styles'] = array(
	'styles' => 'bluespice.extendedEditBar.css',
	'position' => 'top'
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$bsgExtendedEditBarEnabledActions = array( 'edit', 'submit' );